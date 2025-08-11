<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Produit.php';
// require_once '../../config/config.php'; madhabia nzid nfhem les path

class ProduitsController {
 private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion(); // on récupère directement la connexion
    }


    public function getProduits()
    {
       
        try {
            $liste = $this->pdo->query("SELECT * FROM produits");
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function getProduitsTri($sort = null)
{

    
    try {
        if ($sort === 'asc') {
            $sql = "SELECT * FROM produits ORDER BY prix ASC";
        } elseif ($sort === 'desc') {
            $sql = "SELECT * FROM produits ORDER BY prix DESC";
        } 

        $liste = $this->pdo->query($sql);
        return $liste;
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }


}


        function add($produit)
    {
       
        try {
            
            $query = $this->pdo->prepare("
            INSERT INTO produits (nom, description, prix, image_url, stock)
            VALUES (:nom, :description, :prix, :image_url, :stock)
        ");

            $query->bindValue(':nom', $produit->getNom());
            $query->bindValue(':description', $produit->getDescription());
            $query->bindValue(':prix', $produit->getPrix());
            $query->bindValue(':image_url', $produit->getImageUrl());
            $query->bindValue(':stock', $produit->getStock());

            $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

       

     function update($produit, $id)
    {
        try {
            
            $query = $this->pdo->prepare('UPDATE produits SET 
                    `nom` = :nom, 
                    `description` = :description, 
                    `prix` = :prix, 
                    `image_url` = :image_url,
                    `stock` = :stock
                WHERE id = :id'
            );
            $query->bindValue(':nom', $produit->getNom());
            $query->bindValue(':description', $produit->getDescription());
            $query->bindValue(':prix', $produit->getPrix());
            $query->bindValue(':image_url', $produit->getImageUrl());
            $query->bindValue(':stock',$produit->getStock());
            $query->bindValue(':id',$id);


            $query->execute();

        } catch (PDOException $e) {
            $e->getMessage();
        }
    }

      function delete($id)
    {
       
        
        $req = $this->pdo->prepare("DELETE FROM produits WHERE id = :id");
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }



     function researcher($id)
    {
        $sql = "SELECT * from produits where id = $id";
  
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute();

            return $query->fetch();;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    function researchByName($nom)
    {
        $sql = "SELECT * FROM produits WHERE nom LIKE :nom";
        try {
            $query = $this->pdo->prepare($sql);
            $query->execute(['nom' => "%$nom%"]);
            return  $query->fetchAll(PDO::FETCH_ASSOC); 
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
