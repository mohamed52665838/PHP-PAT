<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: ../home/index.php");

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen py-8">

    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Create Account</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="../../../controllers/registerController.php" class="space-y-4" id="registerForm">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" 
                           id="prenom"
                           name="prenom" 
                           
                           value="<?= isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : '' ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" 
                           id="nom"
                           name="nom" 
                           
                           value="<?= isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '' ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" 
                       id="email"
                       name="email" 
                       
                       value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="role" name="role" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="user" <?= (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : '' ?>>User</option>
                
                </select>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" 
                       id="password"
                       name="password" 
                       
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Must be at least 6 characters with uppercase, lowercase, and number</p>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" 
                       id="confirm_password"
                       name="confirm_password" 
                       
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <div id="password-match-error" class="text-red-500 text-xs mt-1 hidden">Passwords do not match</div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" 
                       id="terms" 
                       name="terms" 
                       
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-700">
                    I agree to the <a href="#" class="text-blue-500 hover:text-blue-700">Terms and Conditions</a>
                </label>
            </div>
            
            <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Create Account
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Already have an account? 
                <a href="../login/index.php" class="text-blue-500 hover:text-blue-700 font-medium">Login here</a>
            </p>
        </div>
    </div>

    <script>
        // Client-side password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const errorDiv = document.getElementById('password-match-error');
            
            if (confirmPassword && password !== confirmPassword) {
                errorDiv.classList.remove('hidden');
                this.classList.add('border-red-500');
            } else {
                errorDiv.classList.add('hidden');
                this.classList.remove('border-red-500');
            }
        });
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
        
            const minLength = password.length >= 6;
            
            if (password && ( !minLength)) {
                this.classList.add('border-yellow-400');
                this.classList.remove('border-green-500', 'border-gray-300');
            } else if (password && minLength) {
                this.classList.add('border-green-500');
                this.classList.remove('border-yellow-400', 'border-gray-300');
            } else {
                this.classList.remove('border-yellow-400', 'border-green-500');
                this.classList.add('border-gray-300');
            }
        });
    </script>

</body>
</html>