<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Commande.php';

class CommandeController {
 private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion(); // on récupère directement la connexion
    }

  
       public function getCommande()
    {
       
        try {
            $liste = $this->pdo->query("SELECT * FROM commandes");
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    
    function add($commande)
    {
       
        try {
            
            $query = $this->pdo->prepare("
            INSERT INTO commandes (user_id, status, produits)
            VALUES (:user_id, :status, :produits)
        ");

            $query->bindValue(':user_id', $commande->getUserId());
            $query->bindValue(':status', $commande->getStatus());
            $query->bindValue(':password', $commande->getProduits());


            $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

     public function getCommandes()
    {
       
        try {
            $liste = $this->pdo->query("SELECT * FROM commandes");
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }


}



?>