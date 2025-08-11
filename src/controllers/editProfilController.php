<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/userController.php';

class EditProfileController {
    
    private $userController;
    
    public function __construct() {
        $this->userController = new UserController();
    }
    
    public function updateProfile() {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ../views/front/login/index.php?error=" . urlencode("Please login to access this page"));
            exit();
        }
        
        $userId = $_SESSION['user']['id'];
        
        try {
            // Validate input
            if (!isset($_POST['nom'], $_POST['prenom'], $_POST['email'])) {
                $this->redirectWithError("All fields are required");
                return;
            }
            
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validation
            $errors = $this->validateInput($nom, $prenom, $email, $currentPassword, $newPassword, $confirmPassword, $userId);
            
            if (!empty($errors)) {
                $this->redirectWithError(implode('. ', $errors));
                return;
            }
            
            // Get current user data
            $currentUser = $this->getCurrentUser($userId);
            if (!$currentUser) {
                $this->redirectWithError("User not found");
                return;
            }
            
            // Determine password to use
            $passwordToUpdate = $currentUser['password']; // Keep current password by default
            
            // If user wants to change password
            if (!empty($newPassword)) {
                // Verify current password
                if (!password_verify($currentPassword, $currentUser['password'])) {
                    $this->redirectWithError("Current password is incorrect");
                    return;
                }
                $passwordToUpdate = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            
            // Create User object (keeping original role)
            $user = new User($nom, $prenom, $passwordToUpdate, $currentUser['role'], $email);
            
            // Update user
            $this->userController->update($user, $userId);
            
            // Update session data
            $_SESSION['user']['nom'] = $nom;
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['email'] = $email;
            
            // Redirect with success message
            header("Location: ../views/front/login/edit.php?success=" . urlencode("Profile updated successfully!"));
            exit();
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $this->redirectWithError("System error. Please try again later.");
        }
    }
    
    private function validateInput($nom, $prenom, $email, $currentPassword, $newPassword, $confirmPassword, $userId) {
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
        
        // Check if email is already taken by another user
        if ($this->isEmailTakenByOther($email, $userId)) {
            $errors[] = "Email is already taken by another user";
        }
        
        // Password validation (only if user wants to change password)
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors[] = "Current password is required to change password";
            }
            
            if (strlen($newPassword) < 6) {
                $errors[] = "New password must be at least 6 characters";
            }
            
            if ($newPassword !== $confirmPassword) {
                $errors[] = "New passwords do not match";
            }
            
           
        }
        
        return $errors;
    }
    
    
    
    private function getCurrentUser($userId) {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function isEmailTakenByOther($email, $userId) {
        try {
            $pdo = config::getConnexion();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function redirectWithError($message) {
        $params = http_build_query([
            'error' => $message,
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email'] ?? ''
        ]);
        header("Location: ../views/front/login/edit.php?" . $params);
        exit();
    }
}

// Handle the profile update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editProfileController = new EditProfileController();
    $editProfileController->updateProfile();
} else {
    header("Location: ../views/front/login/edit.php");
    exit();
}
?>