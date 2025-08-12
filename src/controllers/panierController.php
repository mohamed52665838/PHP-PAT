<?php
require_once __DIR__ . '/../../config/config.php';

class PanierController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    /**
     * Récupère le panier actif de l'utilisateur (le crée s'il n'existe pas).
     * @return array [ ['id_panier' => int] ]
     * @throws Exception
     */
    public function getPanier(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) throw new Exception("User not logged in");

        $user_id = (int)$_SESSION['user']['id'];

        $stmt = $this->pdo->prepare("
            SELECT id AS id_panier
            FROM panier
            WHERE user_id = ? AND active = 1
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $ins = $this->pdo->prepare("
                INSERT INTO panier (prixFinaleDeProduit, active, user_id)
                VALUES (0.00, 1, ?)
            ");
            $ins->execute([$user_id]);
            return [['id_panier' => (int)$this->pdo->lastInsertId()]];
        }
        return [$row];
    }

    /**
     * Ajoute un produit au panier actif avec contrôle de stock.
     * Retourne un petit récap (combien ajouté vs demandé, etc.).
     */
    public function addProduitAuPanier(int $produit_id, int $qte = 1): array {
        if ($qte < 1) { $qte = 1; }

        // 1) Panier actif
        $panier = $this->getPanier();
        $panier_id = (int)$panier[0]['id_panier'];

        // 2) Produit + stock
        $stmtP = $this->pdo->prepare("SELECT id, nom, prix, stock FROM produits WHERE id = ?");
        $stmtP->execute([$produit_id]);
        $prod = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            throw new Exception("Produit introuvable.");
        }
        $stock = is_null($prod['stock']) ? PHP_INT_MAX : (int)$prod['stock']; // stock null => pas de limite

        // 3) Ligne existante ?
        $stmtL = $this->pdo->prepare("SELECT id, qte FROM commande_produit WHERE panier_id = ? AND produit_id = ?");
        $stmtL->execute([$panier_id, $produit_id]);
        $ligne = $stmtL->fetch(PDO::FETCH_ASSOC);

        $deja = $ligne ? (int)$ligne['qte'] : 0;
        $restant = max(0, $stock - $deja);

        if ($restant <= 0) {
            // Rien à ajouter, stock atteint
            return [
                'added'     => 0,
                'requested' => $qte,
                'available' => 0,
                'stock'     => $stock,
                'name'      => $prod['nom'],
            ];
        }

        // On limite ce qu'on ajoute au restant
        $a_ajouter = min($qte, $restant);

        if ($ligne) {
            $upd = $this->pdo->prepare("UPDATE commande_produit SET qte = qte + ? WHERE id = ?");
            $upd->execute([$a_ajouter, (int)$ligne['id']]);
        } else {
            $ins = $this->pdo->prepare("INSERT INTO commande_produit (panier_id, produit_id, qte) VALUES (?, ?, ?)");
            $ins->execute([$panier_id, $produit_id, $a_ajouter]);
        }

        return [
            'added'     => $a_ajouter,
            'requested' => $qte,
            'available' => $restant,
            'stock'     => $stock,
            'name'      => $prod['nom'],
        ];
    }

    /**
     * Lignes du panier actif (pour affichage).
     */
    public function getProduitsPanier(): array {
        $panier    = $this->getPanier();
        $id_panier = (int)$panier[0]['id_panier'];

        $sql = $this->pdo->prepare("
            SELECT 
                cp.id AS ligne_id,
                cp.produit_id,
                COALESCE(cp.qte,0) AS quantite,
                p.nom AS produit_nom,
                p.image_url,
                p.prix AS prix_unitaire,
                (COALESCE(cp.qte,0) * p.prix) AS total_ligne
            FROM commande_produit cp
            INNER JOIN produits p ON p.id = cp.produit_id
            WHERE cp.panier_id = ?
            ORDER BY cp.id DESC
        ");
        $sql->execute([$id_panier]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre d’articles (somme des quantités) du panier actif.
     */
    public function countItems(): int {
        $panier = $this->getPanier();
        $id_panier = (int)$panier[0]['id_panier'];

        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(COALESCE(qte,0)),0) AS n FROM commande_produit WHERE panier_id = ?");
        $stmt->execute([$id_panier]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['n'] ?? 0);
    }

    /**
     * Valide (clôture) le panier actif : contrôle stock, décrément, total, active=0, status=en attente,
     * puis crée un nouveau panier actif. Retourne l'ID de la commande (l'ancien panier).
     */
    public function checkout(): int {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user'])) throw new Exception("Veuillez vous connecter.");

        $user_id = (int)$_SESSION['user']['id'];

        // Panier actif + lignes
        $panier = $this->getPanier();
        $panier_id = (int)$panier[0]['id_panier'];

        $sqlLignes = $this->pdo->prepare("
          SELECT cp.produit_id, cp.qte, p.prix, p.stock, p.nom
          FROM commande_produit cp
          JOIN produits p ON p.id = cp.produit_id
          WHERE cp.panier_id = ?
        ");
        $sqlLignes->execute([$panier_id]);
        $lignes = $sqlLignes->fetchAll(PDO::FETCH_ASSOC);

        if (!$lignes) {
            throw new Exception("Votre panier est vide.");
        }

        // Vérif stock côté serveur
        foreach ($lignes as $row) {
            $stock = (int)$row['stock'];
            $qte   = (int)$row['qte'];
            if ($qte > $stock) {
                throw new Exception("Stock insuffisant pour « {$row['nom']} » (disponible: {$stock}).");
            }
        }

        // Total
        $total = 0.00;
        foreach ($lignes as $row) {
            $total += (float)$row['prix'] * (int)$row['qte'];
        }

        // Transaction
        $this->pdo->beginTransaction();
        try {
            // 1) Décrémenter le stock (anti-concurrence via WHERE stock>=qte)
            $dec = $this->pdo->prepare("UPDATE produits SET stock = stock - :qte WHERE id = :pid AND stock >= :qte");
            foreach ($lignes as $row) {
                $ok = $dec->execute([
                    ':qte' => (int)$row['qte'],
                    ':pid' => (int)$row['produit_id'],
                ]);
                if (!$ok || $dec->rowCount() === 0) {
                    throw new Exception("Stock modifié entre-temps pour « {$row['nom']} ». Réessayez.");
                }
            }

            // 2) Fermer le panier = devient une commande historique
            $updPanier = $this->pdo->prepare("
              UPDATE panier
              SET active = 0,
                  status = 'en attente',
                  prixFinaleDeProduit = :total,
                  updated_at = NOW()
              WHERE id = :pid
            ");
            $updPanier->execute([':total' => $total, ':pid' => $panier_id]);

            // 3) Créer un nouveau panier actif pour l’utilisateur
            $newPanier = $this->pdo->prepare("
              INSERT INTO panier (prixFinaleDeProduit, active, user_id, status)
              VALUES (0.00, 1, :uid, 'en attente')
            ");
            $newPanier->execute([':uid' => $user_id]);

            $this->pdo->commit();
            return $panier_id; // l'ID de la "commande" = l'ancien panier fermé

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Alias pratique : même action que checkout(), mais renvoie aussi le total.
     * Utile si ta page attend ['order_id'=>..., 'total'=>...].
     */
    public function finalizeActivePanier(): array {
        $order_id = $this->checkout();
        $stmt = $this->pdo->prepare("SELECT prixFinaleDeProduit FROM panier WHERE id = ?");
        $stmt->execute([$order_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = isset($row['prixFinaleDeProduit']) ? (float)$row['prixFinaleDeProduit'] : 0.0;
        return ['order_id' => $order_id, 'total' => $total];
    }

    /**
     * Liste les commandes (paniers fermés) d'un utilisateur (en-têtes).
     */
    public function listOrdersByUser(int $user_id): array {
        $sql = $this->pdo->prepare("
            SELECT id,
                   prixFinaleDeProduit AS total,
                   status,
                   created_at
            FROM panier
            WHERE user_id = ? AND active = 0
            ORDER BY id DESC
        ");
        $sql->execute([$user_id]);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les lignes d'une commande (panier) donnée.
     */
    public function listOrderLines(int $panier_id): array {
        $q = $this->pdo->prepare("
            SELECT
              cp.qte,
              p.nom,
              p.prix,
              p.image_url,
              (cp.qte * p.prix) AS total_ligne
            FROM commande_produit cp
            JOIN produits p ON p.id = cp.produit_id
            WHERE cp.panier_id = ?
            ORDER BY cp.id ASC
        ");
        $q->execute([$panier_id]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Historique complet : chaque commande avec un sous-tableau 'lignes'.
     */
    public function getHistoriqueAvecLignes(int $user_id): array {
        $orders = $this->listOrdersByUser($user_id);
        if (empty($orders)) return [];

        $ids = array_map(fn($o) => (int)$o['id'], $orders);
        $in  = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $this->pdo->prepare("
            SELECT
              cp.panier_id,
              cp.qte,
              p.nom,
              p.prix,
              p.image_url,
              (cp.qte * p.prix) AS total_ligne
            FROM commande_produit cp
            JOIN produits p ON p.id = cp.produit_id
            WHERE cp.panier_id IN ($in)
            ORDER BY cp.panier_id ASC, cp.id ASC
        ");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regroupe par panier_id
        $grouped = [];
        foreach ($rows as $r) {
            $pid = (int)$r['panier_id'];
            $grouped[$pid][] = $r;
        }

        // Attache les lignes à chaque commande
        foreach ($orders as &$o) {
            $pid = (int)$o['id'];
            $o['lignes'] = $grouped[$pid] ?? [];
        }
        unset($o);

        return $orders;
    }

    //pour preparateur  

    // 1) Toutes les commandes (paniers fermés)
public function getAllOrders(): array {
    $st = $this->pdo->query("
        SELECT id, user_id, prixFinaleDeProduit AS total, status, created_at
        FROM panier
        WHERE active = 0
        ORDER BY id DESC
    ");
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

// 2) Lignes d'une commande (réutilisable)
public function getOrderLines(int $panier_id): array {
    $st = $this->pdo->prepare("
        SELECT cp.qte, p.nom, p.prix, p.image_url, (cp.qte * p.prix) AS total_ligne
        FROM commande_produit cp
        JOIN produits p ON p.id = cp.produit_id
        WHERE cp.panier_id = ?
        ORDER BY cp.id ASC
    ");
    $st->execute([$panier_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

// 3) Changer le statut avec transitions autorisées
public function changerStatus(int $commande_id, string $nouveau): bool {
    $nouveau = trim($nouveau);
    $allowed = ['en attente','en cours','livrée','annulée']; // garde si tu utilises "annulée"
    if (!in_array($nouveau, $allowed, true)) {
        throw new Exception("Statut invalide.");
    }

    // NE toucher qu'aux commandes (active = 0)
    $st = $this->pdo->prepare("SELECT status FROM panier WHERE id = ? AND active = 0");
    $st->execute([$commande_id]);
    $actuel = $st->fetchColumn();
    if ($actuel === false) {
        throw new Exception("Commande introuvable.");
    }

    // Transitions autorisées : en attente -> en cours -> livrée
    $ok = ($actuel === 'en attente' && $nouveau === 'en cours')
       || ($actuel === 'en cours'   && $nouveau === 'livrée');

    if (!$ok) {
        throw new Exception("Transition non autorisée ($actuel → $nouveau).");
    }

    $up = $this->pdo->prepare("UPDATE panier SET status = :s, updated_at = NOW() WHERE id = :id AND active = 0");
    return $up->execute([':s' => $nouveau, ':id' => $commande_id]);
}

public function supprimerCommandeSiEnAttente(int $commandeId): bool {
    // commande historique uniquement (active=0) et statut en attente
    $st = $this->pdo->prepare("SELECT status FROM panier WHERE id = ? AND active = 0");
    $st->execute([$commandeId]);
    $status = $st->fetchColumn();

    if ($status === false) {
        throw new Exception("Commande introuvable.");
    }
    if ($status !== 'en attente') {
        throw new Exception("Suppression autorisée uniquement pour les commandes 'en attente'.");
    }

    // suppression => les lignes s’effacent via ON DELETE CASCADE
    $del = $this->pdo->prepare("DELETE FROM panier WHERE id = ?");
    return $del->execute([$commandeId]);
}



/* =========================
 *   OUTILS ADMIN SUPPLÉMENTAIRES
 * ========================= */

/** Paniers ACTIFS de tous les utilisateurs (admin) */
public function adminGetAllActiveCarts(): array {
    $sql = "
      SELECT 
        p.id AS panier_id,
        p.user_id,
        u.nom,
        u.prenom,
        u.email,
        u.role,
        p.created_at,
        COALESCE(SUM(cp.qte),0) AS total_qte,
        COALESCE(SUM(cp.qte * pr.prix),0) AS total_estime
      FROM panier p
      JOIN users u              ON u.id = p.user_id
      LEFT JOIN commande_produit cp ON cp.panier_id = p.id
      LEFT JOIN produits pr         ON pr.id       = cp.produit_id
      WHERE p.active = 1
        AND LOWER(TRIM(u.role)) = 'client'      -- <<< uniquement les clients
      GROUP BY p.id, p.user_id, u.nom, u.prenom, u.email, u.role, p.created_at
      ORDER BY p.id DESC
    ";
    return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}


/** Lignes d’un panier ACTIF pour admin (pas basé sur la session) */
public function adminGetActiveCartLines(int $panier_id): array {
    $st = $this->pdo->prepare("
      SELECT 
        cp.id        AS ligne_id,
        cp.produit_id,
        cp.qte,
        pr.nom,
        pr.prix,
        pr.stock,
        pr.image_url,
        (cp.qte * pr.prix) AS total_ligne
      FROM commande_produit cp
      JOIN produits pr ON pr.id = cp.produit_id
      WHERE cp.panier_id = ?
      ORDER BY cp.id ASC
    ");
    $st->execute([$panier_id]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/** Mettre à jour la quantité d’une ligne (vérifie le stock) */
public function adminUpdateCartLineQty(int $ligne_id, int $new_qte): bool {
    if ($new_qte < 1) $new_qte = 1;

    // Récup ligne + produit + panier
    $st = $this->pdo->prepare("
      SELECT cp.id, cp.panier_id, cp.produit_id, cp.qte, pr.stock
      FROM commande_produit cp
      JOIN produits pr ON pr.id = cp.produit_id
      WHERE cp.id = ?
    ");
    $st->execute([$ligne_id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception("Ligne introuvable");

    $stock = is_null($row['stock']) ? PHP_INT_MAX : (int)$row['stock'];
    if ($new_qte > $stock) throw new Exception("Stock insuffisant (max $stock).");

    $up = $this->pdo->prepare("UPDATE commande_produit SET qte = :q WHERE id = :id");
    return $up->execute([':q' => $new_qte, ':id' => $ligne_id]);
}

/** Supprimer une ligne du panier (admin) */
public function adminDeleteCartLine(int $ligne_id): bool {
    $del = $this->pdo->prepare("DELETE FROM commande_produit WHERE id = ?");
    return $del->execute([$ligne_id]);
}

/** Valider (checkout) un PANIER SPÉCIFIQUE (admin, pas celui de la session) */
public function adminCheckoutCart(int $panier_id): int {
    // Charger panier actif + user
    $p = $this->pdo->prepare("SELECT id, user_id, active FROM panier WHERE id = ? LIMIT 1");
    $p->execute([$panier_id]);
    $panier = $p->fetch(PDO::FETCH_ASSOC);
    if (!$panier || (int)$panier['active'] !== 1) {
        throw new Exception("Panier introuvable ou déjà clôturé.");
    }
    $user_id = (int)$panier['user_id'];

    // Lignes + contrôle stock
    $sqlLignes = $this->pdo->prepare("
      SELECT cp.produit_id, cp.qte, pr.prix, pr.stock, pr.nom
      FROM commande_produit cp
      JOIN produits pr ON pr.id = cp.produit_id
      WHERE cp.panier_id = ?
    ");
    $sqlLignes->execute([$panier_id]);
    $lignes = $sqlLignes->fetchAll(PDO::FETCH_ASSOC);
    if (!$lignes) throw new Exception("Panier vide.");

    foreach ($lignes as $row) {
        $stock = (int)$row['stock'];
        $qte   = (int)$row['qte'];
        if ($qte > $stock) {
            throw new Exception("Stock insuffisant pour « {$row['nom']} » (disponible: {$stock}).");
        }
    }

    // Total
    $total = 0.0;
    foreach ($lignes as $row) $total += (float)$row['prix'] * (int)$row['qte'];

    // Transaction
    $this->pdo->beginTransaction();
    try {
        // Décrément stocks
        $dec = $this->pdo->prepare("UPDATE produits SET stock = stock - :q WHERE id = :pid AND stock >= :q");
        foreach ($lignes as $row) {
            $ok = $dec->execute([':q' => (int)$row['qte'], ':pid' => (int)$row['produit_id']]);
            if (!$ok || $dec->rowCount() === 0) {
                throw new Exception("Stock modifié entre-temps pour « {$row['nom']} ».");
            }
        }

        // Clôturer
        $upd = $this->pdo->prepare("
          UPDATE panier
          SET active = 0, status = 'en attente', prixFinaleDeProduit = :t, updated_at = NOW()
          WHERE id = :id
        ");
        $upd->execute([':t' => $total, ':id' => $panier_id]);

        // Nouveau panier actif pour l’utilisateur
        $newP = $this->pdo->prepare("
          INSERT INTO panier (prixFinaleDeProduit, active, user_id, status)
          VALUES (0.00, 1, :uid, 'en attente')
        ");
        $newP->execute([':uid' => $user_id]);

        $this->pdo->commit();
        return $panier_id;
    } catch (\Throwable $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

}
