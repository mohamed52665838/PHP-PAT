<?php
require_once __DIR__ . '/../../config/config.php';


class Produit {
    private $nom = null;
    private $description = null;
    private $prix = null;
    private $image_url = null;
    private $stock = null;

    public function __construct($nom, $description, $prix, $image_url, $stock) {
        $this->nom = $nom;
        $this->description = $description;
        $this->prix = $prix;
        $this->image_url = $image_url;
        $this->stock = $stock;
    }

    // Getters
    public function getNom() {
        return $this->nom;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getPrix() {
        return $this->prix;
    }

    public function getImageUrl() {
        return $this->image_url;
    }

    public function getStock() {
        return $this->stock;
    }

    // Setters
    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setPrix($prix) {
        $this->prix = $prix;
    }

    public function setImageUrl($image_url) {
        $this->image_url = $image_url;
    }

    public function setStock($stock) {
        $this->stock = $stock;
    }
}
?>
