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

    /** Hash le mot de passe seulement si ce n'est pas déjà un hash (évite le double-hash). */
private function hashIfNeeded(?string $pwd): string {
    $pwd = (string)$pwd;
    $info = password_get_info($pwd);   // ['algo'] = 0 si ce n'est PAS un hash
    return !empty($info['algo']) ? $pwd : password_hash($pwd, PASSWORD_DEFAULT);
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
            $query->bindValue(':password', $this->hashIfNeeded($user->getPassword()));
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
        // 1) Préparer la bonne valeur de password
        $pwdInput = $user->getPassword();

        if ($pwdInput === null || $pwdInput === '') {
            // Champ vide => on conserve le hash existant
            $stmt = $this->pdo->prepare('SELECT password FROM users WHERE id = :id');
            $stmt->execute([':id' => $id]);
            $pwdHash = (string)$stmt->fetchColumn();
        } else {
            // Champ rempli => si déjà un hash, on garde, sinon on hash
            $info = password_get_info($pwdInput); // ['algo'] = 0 si ce n'est PAS un hash
            $pwdHash = !empty($info['algo']) ? $pwdInput : password_hash($pwdInput, PASSWORD_DEFAULT);
        }

        // 2) Update
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
        $query->bindValue(':password', $pwdHash);
        $query->bindValue(':role', $user->getRole());
        $query->bindValue(':email', $user->getEmail());
        $query->bindValue(':id', $id);

        $query->execute();

    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
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

// === AJOUTER DANS LA CLASSE UserController (sans modifier l'existant) ===

/** Renvoie tous les users en tableau associatif (wrapper autour de getUsers()) */
public function getUsersArray(): array {
    $stmt = $this->getUsers(); // ta méthode existante renvoie un PDOStatement
    return $stmt instanceof PDOStatement
        ? $stmt->fetchAll(PDO::FETCH_ASSOC)
        : (is_array($stmt) ? $stmt : []);
}

/**
 * Recherche + tri côté SQL (sans changer tes méthodes existantes)
 * $opts = [
 *   'id'    => int|null,
 *   'name'  => string,      // recherche dans nom/prenom/concat
 *   'email' => string,      // LIKE
 *   'role'  => string,      // égalité exacte
 *   'sort'  => 'id'|'name'|'email'|'role',
 *   'dir'   => 'asc'|'desc'
 * ]
 */
public function findUsers(array $opts = []): array {
    // sécuriser un minimum la connexion (au cas où)
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];

    if (!empty($opts['id'])) {
        $sql .= " AND id = :id";
        $params[':id'] = (int)$opts['id'];
    }
    if (!empty($opts['name'])) {
        $sql .= " AND (nom LIKE :name OR prenom LIKE :name OR CONCAT(nom,' ',prenom) LIKE :name)";
        $params[':name'] = '%' . $opts['name'] . '%';
    }
    if (!empty($opts['email'])) {
        $sql .= " AND email LIKE :email";
        $params[':email'] = '%' . $opts['email'] . '%';
    }
    if (!empty($opts['role'])) {
        $sql .= " AND role = :role";
        $params[':role'] = $opts['role'];
    }

    // tri (whitelist)
    $allowed = ['id','name','email','role'];
    $sort = strtolower($opts['sort'] ?? 'id');
    $dir  = strtolower($opts['dir']  ?? 'asc');
    $dir  = $dir === 'desc' ? 'DESC' : 'ASC';

    if (!in_array($sort, $allowed, true)) $sort = 'id';

    // mapping "name" vers 2 colonnes
    $orderBy = match ($sort) {
        'name'  => 'nom, prenom',
        'email' => 'email',
        'role'  => 'role',
        default => 'id',
    };

    $sql .= " ORDER BY $orderBy $dir";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(); // assoc
}

}


?>