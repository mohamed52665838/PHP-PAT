<?php
require_once __DIR__ . '/../../config/config.php';

class PanierController {
    private $pdo;

    public function __construct() {
        $this->pdo = Config::getConnexion(); // Fixed: Capital C for Config class
    }
    
    public function getPanier(){
        try {
            // Check if session is started and user is set
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['user'])) {
                throw new Exception("User not logged in");
            }
            
            $user = $_SESSION['user'];
            $user_id = $user['id'];
            // Try to find existing active panier
            $stmt = $this->pdo->prepare("SELECT id as id_panier FROM panier WHERE user_id = ? AND active = 1");
            $stmt->execute([$user_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no active panier found, create a new one
            if (empty($result)) {
                $insertStmt = $this->pdo->prepare("INSERT INTO panier (prixFinaleDeProduit, active, user_id) VALUES (0.00, 1, ?)");
                $insertStmt->execute([$user_id]);
                
                // Get the newly created panier
                $newPanierId = $this->pdo->lastInsertId();
                return [['id_panier' => $newPanierId]];
            }
            
            return $result;
            
        } catch (Exception $e) {
            // Better error handling - you might want to log this instead of die()
            error_log('Error in getPanier: ' . $e->getMessage());
            throw $e; // Re-throw or handle appropriately
        }
    }

    public function getProduitsPanier(){
        try{
            $mon_panier = $this->getPanier();
            $id_paniner= $mon_panier[0]['id_panier'];
            
            $sql = $this->pdo->prepare("SELECT * FROM commande_produit WHERE panier_id = ? ");
            $sql->execute([$id_paniner]);
            $result = $sql->fetchAll(PDO::FETCH_ASSOC);
            var_dump($result);

             } catch (Exception $e) {
            // Better error handling - you might want to log this instead of die()
            error_log('Error in getPanier: ' . $e->getMessage());
            throw $e; // Re-throw or handle appropriately
        }
    }
}