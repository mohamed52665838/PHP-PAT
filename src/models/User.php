<?php
require_once __DIR__ . '/../../config/config.php';

class User {
    private $nom = null;
    private $prenom = null;
        private $email = null;

    private $password = null;
    private $role = "client";
    

    public function __construct($nom, $prenom, $password, $role,$email) {
       
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->password = $password;
        $this->role = $role;
        $this->email = $email;
    }

   
    //getters
    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getPassword() {
        return $this->password;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getRole() {
        return $this->role;
    }

    // Setters
    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setRole($role) {
        $this->role = $role;
    }
    public function setEmail($email)
    {
        $this->email = $email;
    }

}

?>