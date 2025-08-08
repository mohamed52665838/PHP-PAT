<?php
session_start(); // Start session to check authentication
require_once '../../../../src/controllers/produitsController.php';
require_once '../../../../src/models/Produit.php';

$produitsController = new ProduitsController();
$produits = $produitsController->getProduits()->fetchAll(PDO::FETCH_ASSOC);

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['user']);
$user = $isAuthenticated ? $_SESSION['user'] : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pâtisserie</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-pink-50 text-gray-800">

    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto flex justify-between items-center py-4 px-6">
            <h1 class="text-2xl font-bold text-pink-600">Pâtisserie</h1>
            <ul class="flex space-x-6 items-center">
                <li><a href="#" class="hover:text-pink-500">Home</a></li>
                <li><a href="#services" class="hover:text-pink-500">Service</a></li>
                <li><a href="#products" class="hover:text-pink-500">Products</a></li>
                
                <?php if ($isAuthenticated): ?>
                    <!-- Authenticated User Menu -->
                    <li class="relative">
                        <button id="userMenuButton" class="flex items-center space-x-2 hover:text-pink-500 focus:outline-none">
                            <span>Hello, <?= htmlspecialchars($user['nom']) ?></span>
                            <svg class="w-4 h-4 transform transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden opacity-0 transform scale-95 transition-all duration-200">
                            <a href="../../../../views/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Dashboard</a>
                            <a href="../../../../views/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Profile</a>
                            <?php if ($user['role'] === 'admin' || $user['role'] === 'manager'): ?>
                                <a href="../../../../views/admin/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Admin Panel</a>
                            <?php endif; ?>
                            <hr class="my-1">
                            <a href="../../../controllers/logoutController.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-red-600 transition-colors">Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <!-- Non-authenticated User Menu -->
                    <li><a href="../login/index.php" class="hover:text-pink-500">Login</a></li>
                    <li><a href="../register/index.php" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="text-center py-16 bg-gradient-to-r from-pink-100 to-pink-200">
        <h2 class="text-4xl font-bold mb-4">Bienvenue à notre pâtisserie</h2>
        <p class="text-lg text-gray-700 mb-6">Découvrez nos délicieux gâteaux et pâtisseries artisanales</p>
        <?php if ($isAuthenticated): ?>
            <p class="text-md text-pink-700 mb-4">Bienvenue <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>!</p>
        <?php endif; ?>
        <a href="#products" class="px-6 py-3 bg-pink-500 text-white rounded-full hover:bg-pink-600">
            Voir nos produits
        </a>
    </section>

    <!-- Products Section -->
    <section id="products" class="container mx-auto py-12 px-6">
        <h3 class="text-3xl font-bold text-center mb-10">Nos Produits</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($produits as $produit): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                    <?php if (!empty($produit['image_url'])): ?>
                        <img src="../produits/<?= htmlspecialchars($produit['image_url']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>" class="h-48 w-full object-cover">
                    <?php else: ?>
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400 italic">Aucune image</span>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <h4 class="text-xl font-semibold"><?= htmlspecialchars($produit['nom']) ?></h4>
                        <p class="text-gray-600 mt-2"><?= htmlspecialchars($produit['description']) ?></p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-pink-600 font-bold"><?= number_format($produit['prix'], 2) ?> DT</span>
                            
                            <?php if ($isAuthenticated): ?>
                                <!-- Authenticated users can order -->
                                <button onclick="orderProduct(<?= $produit['id'] ?>, '<?= htmlspecialchars($produit['nom']) ?>')" 
                                        class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 transition">
                                    Commander
                                </button>
                            <?php else: ?>
                                <!-- Non-authenticated users redirected to login -->
                                <a href="../login/index.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                                   class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                                    Se connecter pour commander
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if ($isAuthenticated): ?>
        <!-- Order Success Modal (Hidden by default) -->
        <div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md mx-4">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Commande ajoutée!</h3>
                    <p class="text-sm text-gray-500 mb-4" id="orderMessage"></p>
                    <div class="flex space-x-3">
                        <button onclick="closeOrderModal()" 
                                class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 transition">
                            Continuer
                        </button>
                        <a href="../../../../views/orders.php" 
                           class="flex-1 bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 transition text-center">
                            Voir mes commandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-white shadow-inner py-6 mt-12">
        <p class="text-center text-gray-500">© <?= date('Y') ?> Pâtisserie - Tous droits réservés</p>
    </footer>

    <script>
        <?php if ($isAuthenticated): ?>
        // Dropdown menu functionality
        const userMenuButton = document.getElementById('userMenuButton');
        const userDropdown = document.getElementById('userDropdown');
        const dropdownArrow = document.getElementById('dropdownArrow');
        let isDropdownOpen = false;

        function toggleDropdown() {
            isDropdownOpen = !isDropdownOpen;
            
            if (isDropdownOpen) {
                userDropdown.classList.remove('hidden', 'opacity-0', 'scale-95');
                userDropdown.classList.add('opacity-100', 'scale-100');
                dropdownArrow.classList.add('rotate-180');
            } else {
                userDropdown.classList.add('opacity-0', 'scale-95');
                userDropdown.classList.remove('opacity-100', 'scale-100');
                dropdownArrow.classList.remove('rotate-180');
                
                // Hide after animation completes
                setTimeout(() => {
                    if (!isDropdownOpen) {
                        userDropdown.classList.add('hidden');
                    }
                }, 200);
            }
        }

        // Toggle dropdown on button click
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (isDropdownOpen && !userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                toggleDropdown();
            }
        });

        // Close dropdown when pressing Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isDropdownOpen) {
                toggleDropdown();
            }
        });

        // Order functionality for authenticated users
        function orderProduct(productId, productName) {
            // Here you would typically send an AJAX request to add the product to cart/orders
            // For now, we'll just show a success message
            document.getElementById('orderMessage').textContent = `"${productName}" a été ajouté à vos commandes.`;
            document.getElementById('orderModal').classList.remove('hidden');
            document.getElementById('orderModal').classList.add('flex');
        }

        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
            document.getElementById('orderModal').classList.remove('flex');
        }

        // Close modal when clicking outside
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
        <?php endif; ?>
    </script>

</body>
</html>