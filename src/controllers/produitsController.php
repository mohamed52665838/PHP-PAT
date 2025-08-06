<?php
require_once 'config/config.php';

class ProduitsController {
 private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion(); // on récupère directement la connexion
    }
public function getProduits() {
    
    $listeProduits = $this->pdo->prepare('SELECT * FROM produits');
    $listeProduits->execute();
    return $listeProduits->fetchAll(PDO::FETCH_ASSOC);
}

}
