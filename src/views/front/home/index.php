<?php
session_start();
require_once '../../../../src/controllers/produitsController.php';
require_once '../../../../src/models/Produit.php';

$produitsController = new ProduitsController();
$produits = $produitsController->getProduits()->fetchAll(PDO::FETCH_ASSOC);

$isAuthenticated = isset($_SESSION['user']) && is_array($_SESSION['user']);
$user  = $isAuthenticated ? $_SESSION['user'] : null;
$role  = $user['role'] ?? null;
$isAdmin = $isAuthenticated && (strtolower($role) === 'admin');



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pâtisserie</title>
    <script src="https://cdn.tailwindcss.com"></script>
  <style>
  .marquee-container {
    position: relative;
    overflow: hidden;
    height: 1.5em; /* hauteur du texte */
    width: 150px;  /* largeur visible, ajuste selon ton design */
  }

  .marquee-track {
    display: inline-block;
    white-space: nowrap;
    padding-left: 100%; /* commence hors écran à droite */
    animation: marquee 8s linear infinite;
  }

  @keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-100%); }
  }
</style>

<style>
  /* Marquee du nom client (déjà demandé) */
  .marquee-container{position:relative;overflow:hidden;height:1.5em;width:150px}
  .marquee-track{display:inline-block;white-space:nowrap;padding-left:100%;animation:marquee 8s linear infinite}
  @keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}

  /* Brand: “Pâtisserie Douceur” — effet shine + léger flottement */
  .brand-animated{
    font-weight:800;
    background: linear-gradient(90deg,#ec4899,#f59e0b,#ec4899);
    -webkit-background-clip:text;
    background-clip:text;
    color: transparent;
    background-size: 200% auto;
    animation: brand-shine 4s linear infinite, brand-float 6s ease-in-out infinite;
    letter-spacing: .5px;
  }
  @keyframes brand-shine{to{background-position:-200% center}}
  @keyframes brand-float{0%,100%{transform:translateY(0)}50%{transform:translateY(-2px)}}

  /* Soulignement animé (optionnel) */
  .brand-underline{
    position: relative;
  }
  .brand-underline::after{
    content:"";
    position:absolute;left:0;bottom:-4px;height:3px;width:0;
    background:linear-gradient(90deg,#ec4899,#fb7185);
    border-radius:9999px;
    animation: underline-grow 2.2s ease-in-out infinite;
  }
  @keyframes underline-grow{
    0%{width:0}
    50%{width:100%}
    100%{width:0}
  }
</style>
<style>
  /* Bienvenue */
.welcome-animated {
  font-weight: 700;
  background: linear-gradient(90deg, #ec4899, #8b5cf6, #facc15); /* rose → violet → or */
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  background-size: 300% auto;
  animation: welcome-shine 5s linear infinite, welcome-bounce 4s ease-in-out infinite;
  letter-spacing: 1px;
}

@keyframes welcome-shine {
  0%   { background-position: 0% center; }
  100% { background-position: -300% center; }
}

@keyframes welcome-bounce {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(-4px); }
}

/* Pâtisserie Douceur */
.brand-animated {
  font-weight: 800;
  background: linear-gradient(90deg, #facc15, #f472b6, #8b5cf6); /* or → rose → violet */
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  background-size: 200% auto;
  animation: brand-shine 4s linear infinite, brand-float 6s ease-in-out infinite;
  letter-spacing: .5px;
}

@keyframes brand-shine {
  to { background-position: -200% center; }
}

@keyframes brand-float {
  0%,100% { transform: translateY(0); }
  50%     { transform: translateY(-2px); }
}
/* Animation description pro */
/* Animation description avec couleur changeante */
/* Animation description avec couleurs intenses */
.desc-animated {
  opacity: 0;
  transform: translateY(10px);
  animation: descFadeSlide 1.8s ease-out forwards,
             descColorShift 5s linear infinite;
  background: linear-gradient(
    90deg, 
    #ff0057,   /* rose intense */
    #ffae00,   /* orange/or vif */
    #8a00ff,   /* violet électrique */
    #00d4ff,   /* bleu turquoise vif */
    #ff0057    /* retour au rose */
  );
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  background-size: 400% auto;
}

@keyframes descFadeSlide {
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes descColorShift {
  0%   { background-position: 0% center; }
  100% { background-position: -400% center; }
}



@keyframes descShine {
  0%   { background-position: 200% center; }
  100% { background-position: -200% center; }
}


/* Animation liens et infos Hero */
.hero-link-animated {
  display: inline-block;
  background: linear-gradient(90deg, #ff0057, #ffae00, #8a00ff, #00d4ff, #ff0057);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  background-size: 400% auto;
  animation: heroLinkFade 1.2s ease-out forwards, heroLinkColorShift 6s linear infinite;
  opacity: 0;
  transform: translateY(8px);
}

@keyframes heroLinkFade {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes heroLinkColorShift {
  0%   { background-position: 0% center; }
  100% { background-position: -400% center; }
}

/* Animation retardée pour apparaître en séquence */
.hero-link-delay-1 { animation-delay: 0.3s, 0.3s; }
.hero-link-delay-2 { animation-delay: 0.6s, 0.6s; }
.hero-link-delay-3 { animation-delay: 0.9s, 0.9s; }
.hero-link-delay-4 { animation-delay: 1.2s, 1.2s; }
.hero-link-delay-5 { animation-delay: 1.5s, 1.5s; }

</style>


</head>
<body class="bg-pink-50 text-gray-800">

    <!-- Navbar -->
 <nav class="bg-white shadow-md">
  <div class="container mx-auto flex justify-between items-center py-4 px-6">
<h1 class="text-2xl md:text-3xl brand-animated brand-underline">Pâtisserie Douceur</h1>

    <ul class="flex space-x-6 items-center">
      <li><a href="#" class="hover:text-pink-500">Home</a></li>
      <li><a href="#services" class="hover:text-pink-500">Service</a></li>
      <li><a href="#products" class="hover:text-pink-500">Products</a></li>

      <?php if ($isAuthenticated): ?>

        <!-- Bouton à droite selon le rôle -->
        <?php if ($isAdmin): ?>
          <!-- ADMIN : Icône Dashboard (pas de panier) -->
          <li>
            <a href="../../back/dashboard/index.php"
               class="group relative inline-flex items-center justify-center rounded-full border border-pink-200 bg-pink-50 p-2 hover:bg-pink-100 transition"
               title="Dashboard">
              <!-- Icône dashboard -->
              <svg class="h-5 w-5 text-pink-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
              </svg>
              <span class="absolute -bottom-7 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2 py-1 text-xs text-white opacity-0 group-hover:opacity-100 transition">
                Dashboard
              </span>
            </a>
          </li>
        <?php else: ?>
          <!-- CLIENT : Icône Panier (visible pour les non-admin) -->
          <li>
            <a href="../panier/index.php"  
               class="group relative inline-flex items-center justify-center rounded-full border border-pink-200 bg-pink-50 p-2 hover:bg-pink-100 transition"
               title="Mon panier">
              <!-- Icône panier -->
              <svg class="h-5 w-5 text-pink-600" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M7 4h-2l-1 2h2l3.6 7.59-1.35 2.44A2 2 0 009 18h9v-2H9.42a.25.25 0 01-.22-.37L10 14h6.55a2 2 0 001.85-1.23l2.58-6.02A1 1 0 0020 5h-13z"/>
                <circle cx="10.5" cy="20.5" r="1.5"></circle>
                <circle cx="17.5" cy="20.5" r="1.5"></circle>
              </svg>
              <span class="absolute -bottom-7 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2 py-1 text-xs text-white opacity-0 group-hover:opacity-100 transition">
                Mon panier
              </span>
            </a>
          </li>
        <?php endif; ?>

        <!-- Menu utilisateur -->
        <li class="relative">
         <button id="userMenuButton" class="flex items-center space-x-2 hover:text-pink-500 focus:outline-none">
  <?php if ($isAdmin): ?>
<span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 to-rose-500 text-white px-3 py-1 shadow">
    <span class="text-xs uppercase tracking-wide/relaxed opacity-90">Welcome our Admin</span>
    <span class="font-semibold border-l border-white/30 pl-2 marquee-container">
      <span class="marquee-track"><?= htmlspecialchars($user['nom']) ?></span>
    </span>
  </span>

  <?php else: ?>
    <!-- Badge "Welcome our client" + nom animé -->
   <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-pink-500 to-rose-500 text-white px-3 py-1 shadow">
    <span class="text-xs uppercase tracking-wide/relaxed opacity-90">Welcome our client</span>
    <span class="font-semibold border-l border-white/30 pl-2 marquee-container">
      <span class="marquee-track"><?= htmlspecialchars($user['nom']) ?></span>
    </span>
  </span>
  <?php endif; ?>

  <!-- flèche du dropdown (inchangée) -->
  <svg class="w-4 h-4 transform transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
  </svg>
</button>


          <!-- Dropdown -->
          <div id="userDropdown"
               class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-50 hidden opacity-0 transform scale-95 transition-all duration-200">
            <?php if ($isAdmin): ?>
              <!-- Admin : Dashboard/Admin Panel -->
              <a href="../../back/dashboard/index.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Admin Panel</a>
            <?php else: ?>
              <!-- Client : Historique (remplace Dashboard) -->
              <a href="../../../../views/orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Historique</a>
            <?php endif; ?>
            <a href="../login/edit.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">Profile</a>
            <hr class="my-1">
            <a href="../../../controllers/logoutController.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-red-600 transition-colors">Logout</a>
          </div>
        </li>

      <?php else: ?>
        <!-- Non-authenticated -->
        <li><a href="../login/index.php" class="hover:text-pink-500">Login</a></li>
        <li><a href="../register/index.php" class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600">Register</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>



    <!-- Hero Section -->
<!-- Hero -->
<section id="hero" class="relative overflow-hidden">
  <!-- Fond rose uni -->
<div class="absolute inset-0 bg-gradient-to-r from-pink-500 via-pink-400 to-pink-300">
  <div class="absolute inset-0 bg-white/40"></div> <!-- voile blanc -->
</div>

  <!-- Contenu -->
  <div class="relative container mx-auto px-6 py-24 md:py-32">
    <div class="mx-auto max-w-3xl text-center text-white">
      <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-4 py-1 text-sm backdrop-blur">
        🍰 Fait maison chaque matin
      </span>

      <h2 class="mt-5 text-4xl md:text-5xl font-extrabold leading-tight drop-shadow-sm welcome-animated">
  Bienvenue à notre pâtisserie
</h2>
<h3 class="mt-2 text-3xl md:text-4xl leading-tight drop-shadow-sm brand-animated brand-underline">
  Pâtisserie Douceur
</h3>


  <p class="mt-4 text-lg md:text-xl desc-animated">
  Découvrez nos gâteaux, macarons et viennoiseries artisanales — fraîcheur garantie.
</p>



     <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
  <a href="#products"
     class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 font-semibold hero-link-animated hero-link-delay-1 hover:bg-rose-50 transition shadow">
    Voir nos produits
  </a>
  <a href="#contact"
     class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl border border-white/60 px-6 py-3 font-semibold hero-link-animated hero-link-delay-2 hover:bg-white/10 transition">
    Nous contacter
  </a>
</div>

<div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm">
  <span class="inline-flex items-center gap-2 hero-link-animated hero-link-delay-3">📞 Commandes : +216 20 123 456</span>
  <span class="inline-flex items-center gap-2 hero-link-animated hero-link-delay-4">📍 Rue des Fleurs, Tunis</span>
  <span class="inline-flex items-center gap-2 hero-link-animated hero-link-delay-5">🕒 08:00–19:00 (Dim 09:00–13:00)</span>
</div>

<!-- 
      <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm text-white/90">
        <span class="inline-flex items-center gap-2">📞 Commandes : +216 20 123 456</span>
        <span class="inline-flex items-center gap-2">📍 Rue des Fleurs, Tunis</span>
        <span class="inline-flex items-center gap-2">🕒 08:00–19:00 (Dim 09:00–13:00)</span>
      </div> -->
    </div>
  </div>
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
   class="bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600 transition">
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
   <!-- Footer -->
<!-- Footer -->
<footer class="relative mt-16 bg-gradient-to-br from-pink-500 via-pink-400 to-pink-300 text-white">
  <!-- décor -->
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="absolute -top-20 -right-20 h-56 w-56 rounded-full bg-pink-200/40 blur-3xl"></div>
    <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-rose-200/40 blur-3xl"></div>
  </div>

  <div class="relative container mx-auto px-6 py-14">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">

      <!-- Identité + Réseaux -->
      <div class="md:col-span-4">
        <div class="backdrop-blur supports-[backdrop-filter]:bg-white/60 bg-white/70 border border-white/60 shadow-sm rounded-2xl p-6">
          <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-pink-500 text-white text-xl font-bold">P</span>
<h2 class="mt-5 text-4xl md:text-5xl leading-tight drop-shadow-sm brand-animated brand-underline">
  Pâtisserie Douceur
</h2>
          </div>
          <p class="mt-4 text-gray-600">
            Des créations artisanales faites avec amour : gâteaux, macarons et viennoiseries fraîches chaque jour.
          </p>

          <div class="mt-6 flex items-center gap-3">
            <!-- Instagram -->
            <a href="#" aria-label="Instagram" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white hover:border-pink-300 hover:shadow transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5H7zm0 2h10c1.7 0 3 1.3 3 3v10c0 1.7-1.3 3-3 3H7c-1.7 0-3-1.3-3-3V7c0-1.7 1.3-3 3-3zm11 1.8a1.2 1.2 0 100 2.4 1.2 1.2 0 000-2.4zM12 7a5 5 0 100 10 5 5 0 000-10zm0 2a3 3 0 110 6 3 3 0 010-6z"/></svg>
            </a>
            <!-- Facebook -->
            <a href="#" aria-label="Facebook" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white hover:border-pink-300 hover:shadow transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-8h3l1-4h-4V7.5c0-1.1.3-1.9 2-1.9h2V2.2C16.6 2 15.3 2 14 2c-3 0-5 1.8-5 5v3H6v4h3v8h4z"/></svg>
            </a>
            <!-- WhatsApp -->
            <a href="https://wa.me/21620123456" target="_blank" aria-label="WhatsApp" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white hover:border-pink-300 hover:shadow transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 3.5A11 11 0 003.2 18.8L2 22l3.3-1.1A11 11 0 1020.5 3.5zM12 20a8 8 0 01-4.1-1.1l-.3-.2-2.4.8.8-2.3-.2-.3A8 8 0 1112 20zm4.7-5.7c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.1-.2.2-.8.9-.9 1.1-.2.2-.3.2-.6.1a6.6 6.6 0 01-2-1.2 7.4 7.4 0 01-1.4-1.7c-.2-.3 0-.4.1-.6.2-.2.3-.3.4-.5.1-.1.2-.3.2-.5 0-.2 0-.4-.1-.5-.1-.1-.7-1.6-.9-2-.2-.4-.4-.4-.6-.4H9c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5 0 1.4 1.1 2.8 1.3 3 .2.3 2.2 3.4 5.3 4.6.7.3 1.3.4 1.8.6.8.2 1.5.2 2.1.1.7-.1 1.7-.7 2-1.4.2-.7.2-1.3.1-1.4-.1-.1-.3-.2-.4-.2z"/></svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Coordonnées + Liens -->
      <div id="contact" class="md:col-span-4">
        <div class="backdrop-blur supports-[backdrop-filter]:bg-white/60 bg-white/70 border border-white/60 shadow-sm rounded-2xl p-6">
          <h4 class="text-lg font-semibold text-gray-800">Nous contacter</h4>
          <div class="mt-4 space-y-3 text-gray-700">
            <div class="flex items-start gap-3">
              <svg class="h-5 w-5 mt-0.5 text-pink-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.1 2 5 5.1 5 9c0 5.2 7 13 7 13s7-7.8 7-13c0-3.9-3.1-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
              <p>Rue des Fleurs, 1000 Tunis</p>
            </div>
            <div class="flex items-center gap-3">
              <svg class="h-5 w-5 text-pink-500" viewBox="0 0 24 24" fill="currentColor"><path d="M6.6 10.8c1.2 2.3 3.2 4.2 5.5 5.5l1.8-1.8c.3-.3.7-.4 1.1-.2 1.2.4 2.4.6 3.7.6.6 0 1 .4 1 .9V20c0 .5-.4 1-1 1C10.1 21 3 13.9 3 5c0-.5.5-1 1-1h3.2c.5 0 .9.4.9 1 0 1.3.2 2.5.6 3.7.1.4 0 .8-.3 1.1L6.6 10.8z"/></svg>
              <a href="tel:+21620123456" class="hover:text-pink-600">+216 20 123 456</a>
            </div>
            <div class="flex items-center gap-3">
              <svg class="h-5 w-5 text-pink-500" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
              <a href="mailto:contact@patisserie.tn" class="hover:text-pink-600">contact@patisserie.tn</a>
            </div>
            <p class="text-sm text-gray-500 pt-1">Lun–Sam 08:00–19:00 • Dim 09:00–13:00</p>

            <div class="mt-4">
              <h5 class="font-medium text-gray-800 mb-2">Liens rapides</h5>
              <div class="flex flex-wrap gap-2">
                <a href="#" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Accueil</a>
                <a href="#services" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Services</a>
                <a href="#products" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Produits</a>
                <?php if ($isAuthenticated): ?>
                  <a href="../../../../views/orders.php" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Mes commandes</a>
                  <a href="../../back/dashboard/index.php" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Dashboard</a>
                <?php else: ?>
                  <a href="../login/index.php" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Se connecter</a>
                  <a href="../register/index.php" class="text-sm px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:border-pink-300 transition">Créer un compte</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulaire -->
      <div class="md:col-span-4">
        <div class="backdrop-blur supports-[backdrop-filter]:bg-white/60 bg-white/70 border border-white/60 shadow-sm rounded-2xl p-6">
          <h4 class="text-lg font-semibold text-gray-800">Écrivez-nous</h4>
          <form id="contactForm" class="mt-4 space-y-4" method="post" action="../../../../src/controllers/contactController.php" novalidate>
            <div>
              <label for="c_name" class="block text-sm text-gray-600 mb-1">Nom</label>
              <input id="c_name" name="name" type="text" required
                     class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition">
            </div>
            <div>
              <label for="c_email" class="block text-sm text-gray-600 mb-1">Email</label>
              <input id="c_email" name="email" type="email" required
                     class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition">
            </div>
            <div>
              <label for="c_msg" class="block text-sm text-gray-600 mb-1">Message</label>
              <textarea id="c_msg" name="message" rows="4" required
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-200 transition"></textarea>
            </div>

            <div class="flex items-start gap-3">
              <input id="c_terms" type="checkbox" class="mt-1 rounded border-gray-300 text-pink-600 focus:ring-pink-400" required>
              <label for="c_terms" class="text-sm text-gray-600">J’accepte que mes informations soient utilisées pour traiter ma demande.</label>
            </div>

            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-pink-500 px-4 py-2.5 text-white font-medium hover:bg-pink-600 active:bg-pink-700 transition">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M2 21l21-9L2 3v7l15 2-15 2v7z"/></svg>
              Envoyer
            </button>
            <p id="contactFeedback" class="text-sm mt-1 hidden"></p>
          </form>
        </div>
      </div>

    </div>
  </div>

  <!-- Copyright bar -->
  <div class="relative border-t border-gray-200/70">
    <p class="text-center text-gray-500 py-5">
      © <?= date('Y') ?> Pâtisserie Douceur — Tous droits réservés
    </p>
  </div>
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

        </script> <!-- ← ton script existant -->

    <!-- Script du formulaire de contact -->
    <script>
      (function () {
        const form = document.getElementById('contactForm');
        if (!form) return;

        const feedback = document.getElementById('contactFeedback');

        form.addEventListener('submit', async function (e) {
          e.preventDefault(); // Empêche l'envoi classique
          try {
            const name = document.getElementById('c_name').value.trim();
            const email = document.getElementById('c_email').value.trim();
            const message = document.getElementById('c_msg').value.trim();

            if (!name || !email || !message) {
              feedback.textContent = 'Veuillez remplir tous les champs.';
              feedback.className = 'text-sm mt-2 text-red-600';
              feedback.classList.remove('hidden');
              return;
            }

            // Simulation de succès
            feedback.textContent = 'Merci ! Votre message a bien été envoyé.';
            feedback.className = 'text-sm mt-2 text-green-600';
            feedback.classList.remove('hidden');
            form.reset();
          } catch (err) {
            feedback.textContent = "Désolé, une erreur est survenue. Réessayez.";
            feedback.className = 'text-sm mt-2 text-red-600';
            feedback.classList.remove('hidden');
          }
        });
      })();
    </script>
</body>
</html>


</body>
</html>