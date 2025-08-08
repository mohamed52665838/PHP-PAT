<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';


class UserController {
 private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion(); // on récupère directement la connexion
    }

    public function getUser()
    {
       
        try {
            $liste = $this->pdo->query("SELECT * FROM users");
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

        function add($user)
    {
       
        try {
            
            $query = $this->pdo->prepare("
            INSERT INTO users (nom, prenom, password, role,email)
            VALUES (:nom, :prenom, :password, :role,:email)
        ");

            $query->bindValue(':nom', $user->getNom());
            $query->bindValue(':prenom', $user->getPrenom());
            $query->bindValue(':password', $user->getPassword());
            $query->bindValue(':role', $user->getRole());
            $query->bindValue(':email', $user->getEmail());


            $query->execute();
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

     function update($user, $id)
    {
        try {
            
            $query = $this->pdo->prepare('UPDATE users SET 
                    `nom` = :nom, 
                    `prenom` = :prenom, 
                    `password` = :password, 
                    `role` = :role,
                    `email` = :email
                WHERE id = :id'
            );
            $query->bindValue(':nom', $user->getNom());
            $query->bindValue(':prenom', $user->getPrenom());
            $query->bindValue(':password', $user->getPassword());
            $query->bindValue(':role', $user->getRole());
            $query->bindValue(':email', $user->getEmail());
            $query->bindValue(':id',$id);


            $query->execute();

        } catch (PDOException $e) {
            $e->getMessage();
        }
    }
     public function getUsers()
    {
       
        try {
            $liste = $this->pdo->query("SELECT * FROM users");
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

     function delete($id)
    {
       
        
        $req = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }


}


?>