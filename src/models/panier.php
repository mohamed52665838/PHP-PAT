<?php
require_once __DIR__ . '/../../config/config.php';

class Panier {
    private ?int $id = null;
    private float $prixFinaleDeProduit = 0.0;
    private bool $active = false;
    private int $user_id;
    private string $status = 'en attente';

    public function __construct(float $prixFinaleDeProduit, int $user_id, bool $active = false, string $status = 'en attente') {
        $this->prixFinaleDeProduit = (float)$prixFinaleDeProduit;
        $this->user_id = (int)$user_id;
        $this->active = (bool)$active;
        $this->status = $status;
    }

    /* ========== Getters ========== */
    public function getId(): ?int { return $this->id; }
    public function getPrixFinaleDeProduit(): float { return $this->prixFinaleDeProduit; }
    public function getActive(): bool { return $this->active; }
    public function getUserId(): int { return $this->user_id; }
    public function getStatus(): string { return $this->status; }

    /* ========== Setters ========== */
    public function setId(?int $id): void { $this->id = $id; }
    public function setPrixFinaleDeProduit($prix): void { $this->prixFinaleDeProduit = (float)$prix; }
    public function setActive($active): void { $this->active = (bool)$active; }
    public function setUserId($user_id): void { $this->user_id = (int)$user_id; }
    public function setStatus(string $status): void { $this->status = $status; }

    /* ========== Validation simple ========== */
    private function validate(): void {
        $allowed = ['en attente','en cours','livrée','annulée'];
        if (!in_array($this->status, $allowed, true)) {
            throw new InvalidArgumentException("Statut invalide: {$this->status}");
        }
        if ($this->user_id <= 0) {
            throw new InvalidArgumentException("user_id invalide.");
        }
    }

    /**
     * Insert si $id === null, sinon Update.
     * Retourne true si OK. Met à jour $this->id après un insert.
     */
    public function save(PDO $pdo): bool {
        $this->validate();
        $a = $this->active ? 1 : 0;

        if ($this->id === null) {
            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO panier (prixFinaleDeProduit, active, user_id, status)
                VALUES (:prix, :active, :uid, :status)
            ");
            $ok = $stmt->execute([
                ':prix'   => $this->prixFinaleDeProduit,
                ':active' => $a,
                ':uid'    => $this->user_id,
                ':status' => $this->status,
            ]);
            if ($ok) {
                $this->id = (int)$pdo->lastInsertId();
            }
            return $ok;
        } else {
            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE panier
                SET prixFinaleDeProduit = :prix,
                    active = :active,
                    user_id = :uid,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            return $stmt->execute([
                ':prix'   => $this->prixFinaleDeProduit,
                ':active' => $a,
                ':uid'    => $this->user_id,
                ':status' => $this->status,
                ':id'     => $this->id,
            ]);
        }
    }

    /* Optionnel : fermer le panier (utile si tu veux déplacer la logique hors du contrôleur) */
    public function close(PDO $pdo, ?float $total = null, string $statusFinal = 'en attente'): bool {
        if ($this->id === null) throw new RuntimeException("Impossible de fermer : id null.");
        if ($total !== null) $this->prixFinaleDeProduit = (float)$total;
        $this->active = false;
        $this->status = $statusFinal;

        $stmt = $pdo->prepare("
            UPDATE panier
            SET active = 0,
                status = :status,
                prixFinaleDeProduit = :total,
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':status' => $this->status,
            ':total'  => $this->prixFinaleDeProduit,
            ':id'     => $this->id,
        ]);
    }
}
