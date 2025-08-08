<?php
require_once __DIR__ . '/../../config/config.php';

class Commande {
    private $id = null;
    private $user_id = null;
    private $status = 'en attente'; // valeur par défaut
    private $produits = []; // liste des produits (table commande_produit)
   
    
    public function __construct($user_id, $status = 'en attente', $produits = []) {
        $this->user_id = $user_id;
        $this->status = $status;
        $this->produits = $produits; // tableau de produits (avec quantité, prix final, etc.)
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getProduits() {
        return $this->produits;
    }

   

    // Setters
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setProduits($produits) {
        $this->produits = $produits;
    }


    
}

?>
