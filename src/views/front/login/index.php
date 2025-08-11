<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: ../home/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Connexion — Pâtisserie</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
  /* Titre qui défile (Se connecter) */
  .heading-marquee{
    position:relative;display:inline-block;overflow:hidden;
    height:1.2em;           /* ajuste selon la taille du titre */
    max-width:18ch;         /* largeur visible */
    vertical-align:middle;
  }
  .heading-marquee .track{
    display:inline-block;white-space:nowrap;
    padding-left:100%;      /* commence hors écran à droite */
    animation: headingMarquee 9s linear infinite;/* on va applique fct de annimation heading avec linear vitesse constant du debut a fin infinite bich text ytkteb minghir maye9ef  */
  }/*  duree de cycle 9s */
  /* @keyframes on va definir un css annimation  */
  @keyframes headingMarquee{
    0%{transform:translateX(0)}100%{transform:translateX(-100%)}
  }
/* 0% lowla bich kif youfa text i3wed yaffichi mra okhra 
*/


  /* Lien qui défile (Créer un compte) */
  .link-marquee{
    position:relative;display:inline-block;overflow:hidden;
    height:1.25em;max-width:14ch;vertical-align:middle;
  }
  .link-marquee .track{
    display:inline-block;white-space:nowrap;
    padding-left:100%;
    animation: linkMarquee 7s linear infinite;
  }
  @keyframes linkMarquee{
    0%{transform:translateX(0)}100%{transform:translateX(-100%)}
  }
  

  /* Accessibilité  sur pc */
  @media (prefers-reduced-motion: reduce){
    .heading-marquee .track,
    .link-marquee .track{ animation:none; padding-left:0; }
  }
</style>

</head>
<body class="min-h-screen bg-gradient-to-br from-pink-50 via-rose-50 to-pink-100 text-gray-800">

  <!-- Topbar -->
  <header class="bg-gradient-to-r from-pink-600 via-rose-500 to-fuchsia-500 text-white shadow">
    <div class="max-w-5xl mx-auto px-6 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-xl font-bold">P</span>
        <div>
          <h1 class="text-lg font-semibold tracking-tight">Pâtisserie Douceur</h1>
          <p class="text-xs text-white/90">Connexion</p>
        </div>
      </div>
      <a href="../home/index.php"
         class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm hover:bg-white/20 transition">
        ← Retour à l’accueil
      </a>
    </div>
  </header>

  <!-- Content -->
  <main class="relative">
    <!-- Décor bulles -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
      <div class="absolute -top-16 -left-10 h-40 w-40 rounded-full bg-pink-200/40 blur-3xl"></div>
      <div class="absolute -bottom-16 -right-10 h-56 w-56 rounded-full bg-rose-200/50 blur-3xl"></div>
    </div>

    <div class="relative max-w-md mx-auto px-6 py-10">
      <div class="rounded-2xl border border-white/70 bg-white/90 backdrop-blur shadow-xl p-7">
        <div class="mb-6 text-center">
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-pink-100 text-pink-700 text-xl">🔐</span>
<h2 class="mt-3 text-2xl font-extrabold text-pink-700">
  <span class="heading-marquee">
    <span class="track">Se connecter&nbsp;&nbsp;Se connecter&nbsp;&nbsp;</span>
  </span>
</h2>
          <p class="text-sm text-pink-600">Accédez à votre espace gourmand.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
          <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <?= htmlspecialchars($_GET['error']) ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
          <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            <?= htmlspecialchars($_GET['success']) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="../../../controllers/loginController.php" class="space-y-4" novalidate>
          <div>
            <label for="email" class="block text-sm text-pink-700 mb-1">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>"
                   class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-200">
          </div>

          <div class="relative">
            <label for="password" class="block text-sm text-pink-700 mb-1">Mot de passe</label>
            <input type="password" id="password" name="password"
                   class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 pr-10 outline-none focus:border-pink-400 focus:ring-2 focus:ring-pink-200">
            <button type="button" id="togglePassword"
                    class="absolute right-3 top-9 text-pink-400 hover:text-pink-600">👁️</button>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" name="remember" class="rounded border-pink-300 text-pink-600 focus:ring-pink-400">
              <span class="text-gray-700">Se souvenir de moi</span>
            </label>
            <a href="#" class="text-pink-600 hover:text-pink-700">Mot de passe oublié ?</a>
          </div>

          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-white font-medium shadow hover:bg-pink-700 active:bg-pink-800 transition">
            Connexion
          </button>
        </form>

        <div class="mt-6 text-center">
       <p class="text-sm text-gray-600">
  Pas de compte ?
  <a href="../register/index.php" class="text-pink-600 hover:text-pink-700 font-medium">
    <span class="link-marquee">
      <span class="track">Créer un compte&nbsp;&nbsp;Créer un compte&nbsp;&nbsp;</span>
    </span>
  </a>
</p>

        </div>
      </div>
    </div>
  </main>

  <footer class="border-t border-pink-200/60">
    <div class="max-w-5xl mx-auto px-6 py-6 text-xs text-pink-600">
      © <?= date('Y') ?> Pâtisserie Douceur — Tous droits réservés
    </div>
  </footer>

  <script>
    // Afficher / masquer le mot de passe
    document.getElementById('togglePassword')?.addEventListener('click', function () {
      const field = document.getElementById('password');
      const isHidden = field.type === 'password';
      field.type = isHidden ? 'text' : 'password';
      this.textContent = isHidden ? '🙈' : '👁️';
    });
  </script>
</body>
</html>  