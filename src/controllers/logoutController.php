<?php
session_start();

class LogoutController {
    
    public function logout() {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location:  ../views/front/login/index.php?error=" . urlencode("You are not logged in"));
            exit();
        }
        
        // Get user info before destroying session (for logging purposes)
        $user = $_SESSION['user'];
        
        // Log the logout event (optional)
        error_log("User logout: " . $user['email'] . " (" . $user['id'] . ") at " . date('Y-m-d H:i:s'));
        
        // Destroy all session data
        $_SESSION = array();
        
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page with success message
        header("Location:  ../views/front/login/index.php?success=" . urlencode("You have been logged out successfully"));
        exit();
    }
}

// Handle logout request
$logoutController = new LogoutController();
$logoutController->logout();
?>