<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

class LoginController {
    
    public function login() {
        try {
            $db = new config();
            $pdo = $db->getConnexion();
            
            if (!isset($_POST['email'], $_POST['password'])) {
                header("Location: ../views/front/login/index.php?error=" . urlencode("Missing email or password"));
                exit();
            }
            
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: ../views/front/login/index.php?error=" . urlencode("Invalid email format"));
                exit();
            }
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id'     => $user['id'],
                    'nom'    => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email'  => $user['email'],
                    'role'   => $user['role']
                ];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                header("Location: ../views/front/home");
                exit();
            } else {
                header("Location: ../views/front/login/index.php?error=" . urlencode("Invalid credentials"));
                exit();
            }
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            header("Location: ../views/front/login/index.php?error=" . urlencode("System error. Please try again."));
            exit();
        }
    }
}

// Handle the login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginController = new LoginController();
    $loginController->login();
} else {
    header("Location:  ../views/front/login/index.php");
    exit();
}
?>