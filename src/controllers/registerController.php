<?php
session_start();
require_once __DIR__ . '/../../config/config.php';


class RegisterController {
    
    public function register() {
        try {
            $db = new config();
            $pdo = $db->getConnexion();
            
            // Check if all required fields are present
            if (!isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
                $this->redirectWithError("All fields are required");
                return;
            }
            
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // Default role
            
            // Validation
            $errors = $this->validateInput($nom, $prenom, $email, $password, $confirmPassword, $role);
            
            if (!empty($errors)) {
                $this->redirectWithError(implode('. ', $errors));
                return;
            }
            
            // Check if email already exists
            if ($this->emailExists($pdo, $email)) {
                $this->redirectWithError("Email already exists");
                return;
            }
            
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role, created_at) VALUES (:nom, :prenom, :email, :password, :role, NOW())");
            $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
            $stmt->bindValue(':prenom', $prenom, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindValue(':role', $role, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                header("Location: ../views/front/login/index.php?success=" . urlencode("Registration successful! Please login."));
                exit();
            } else {
                $this->redirectWithError("Registration failed. Please try again.");
            }
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->redirectWithError("System error. Please try again later.");
        }
    }
    
    private function validateInput($nom, $prenom, $email, $password, $confirmPassword, $role) {
        $errors = [];
        
        // Name validation
        if (empty($nom) || strlen($nom) < 2) {
            $errors[] = "Last name must be at least 2 characters";
        }
        
        if (empty($prenom) || strlen($prenom) < 2) {
            $errors[] = "First name must be at least 2 characters";
        }
        
        // Email validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }
        
        // Password validation
        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }
        
        // Password strength check

        
        // Role validation
        $allowedRoles = ['user'];
        if (!in_array($role, $allowedRoles)) {
            $errors[] = "Invalid role selected";
        }
        
        return $errors;
    }
    
    
    private function emailExists($pdo, $email) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }
    
    private function redirectWithError($message) {
        $params = http_build_query([
            'error' => $message,
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'user'
        ]);
        header("Location: ../views/front/register/index.php?" . $params);
        exit();
    }
}

// Handle the registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registerController = new RegisterController();
    $registerController->register();
} else {
    header("Location: ../views/front/register/index.php");
    exit();
}
?>