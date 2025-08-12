<?php
declare(strict_types=1);
session_start();

require_once '../../../../src/controllers/panierController.php';

header('Content-Type: text/html; charset=utf-8');

try {
    if (!isset($_SESSION['user'])) {
        // Réponse JS vers la page parente (non connecté)
        echo '<script>if (parent && parent.alert) parent.alert("Veuillez vous connecter pour commander.");</script>';
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo '<script>if (parent && parent.alert) parent.alert("Requête invalide.");</script>';
        exit;
    }

    $produit_id = isset($_POST['produit_id']) ? (int)$_POST['produit_id'] : 0;
    $qte        = isset($_POST['qte']) ? (int)$_POST['qte'] : 1;

    if ($produit_id <= 0 || $qte <= 0) {
        echo '<script>if (parent && parent.alert) parent.alert("Données invalides.");</script>';
        exit;
    }

    $controller = new PanierController();
    $res = $controller->addProduitAuPanier($produit_id, $qte);

    // Récupérer le nouveau total d’articles
    $count = $controller->countItems();

    // Construire un petit message utilisateur
    if ($res['added'] === 0) {
        $msg = "Stock épuisé pour « {$res['name']} ». Stock disponible: 0.";
    } elseif ($res['added'] < $res['requested']) {
        $msg = "Seulement {$res['added']} ajouté(s) sur {$res['requested']} pour « {$res['name']} » (reste: {$res['available']}).";
    } else {
        $msg = "« {$res['name']} » ajouté au panier (x{$res['added']}).";
    }

    // Réponse JS : met à jour le compteur + optionnel: toast/alert
    ?>
<!doctype html>
<meta charset="utf-8">
<script>
try {
  if (window.parent && typeof window.parent.updateCartCount === 'function') {
    window.parent.updateCartCount(<?= (int)$count ?>);
  }
  // Si tu veux un feedback rapide côté client :
  if (window.parent && window.parent.console) {
    console.log(<?= json_encode($msg, JSON_UNESCAPED_UNICODE) ?>);
  }
  // Tu peux aussi déclencher une petite notif custom si tu as une fonction parent.showToast:
  // if (parent.showToast) parent.showToast(<?= json_encode($msg, JSON_UNESCAPED_UNICODE) ?>);
} catch(e) {}
</script>
<?php
    exit;

} catch (Throwable $e) {
    // En cas d’erreur serveur
    $msg = "Erreur: " . $e->getMessage();
    echo '<script>if (parent && parent.alert) parent.alert('.json_encode($msg, JSON_UNESCAPED_UNICODE).');</script>';
    exit;
}
