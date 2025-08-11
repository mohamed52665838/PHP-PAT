<?php
session_start();

// Si déjà connecté, rediriger
if (isset($_SESSION['client'])) {
    header("Location: ../home/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Créer un compte — Pâtisserie</title>
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
  .link-marquee {
    position: relative; display: inline-block; overflow: hidden;
    height: 1.25em; max-width: 12ch; vertical-align: middle;
  }
  .link-marquee .track {
    display: inline-block; white-space: nowrap; padding-left: 100%;
    animation: linkMarquee 7s linear infinite;
  }
  @keyframes linkMarquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
  }
  @media (prefers-reduced-motion: reduce) {
    .link-marquee .track { animation: none; padding-left: 0; }
  }

  .heading-marquee{
    position: relative; display: inline-block; overflow: hidden;
    height: 1.2em; max-width: 18ch; vertical-align: middle;
  }
  .heading-marquee .track{
    display: inline-block; white-space: nowrap; padding-left: 100%;
    animation: headingMarquee 9s linear infinite;
  }
  @keyframes headingMarquee{
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
  }
  @media (prefers-reduced-motion: reduce){
    .heading-marquee .track{ animation: none; padding-left: 0; }
  }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-pink-50 via-rose-50 to-pink-100 text-gray-800">

  <!-- Bandeau haut -->
  <header class="bg-gradient-to-r from-pink-600 via-rose-500 to-fuchsia-500 text-white shadow">
    <div class="max-w-5xl mx-auto px-6 py-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 text-xl font-bold">P</span>
        <div>
          <h1 class="text-lg font-semibold tracking-tight">Pâtisserie Douceur</h1>
          <p class="text-xs text-white/90">Créer un compte</p>
        </div>
      </div>
      <a href="../home/index.php" class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm hover:bg-white/20 transition">
        ← Retour à l’accueil
      </a>
    </div>
  </header>

  <!-- Contenu -->
  <main class="relative">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
      <div class="absolute -top-16 -left-10 h-40 w-40 rounded-full bg-pink-200/40 blur-3xl"></div>
      <div class="absolute -bottom-16 -right-10 h-56 w-56 rounded-full bg-rose-200/50 blur-3xl"></div>
    </div>

    <div class="relative max-w-md mx-auto px-6 py-10">
      <div class="rounded-2xl border border-white/70 bg-white/90 backdrop-blur shadow-xl p-7">

        <!-- Titre -->
        <div class="mb-6 text-center">
          <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-pink-100 text-pink-700 text-xl">🍰</span>
          <h2 class="mt-3 text-2xl font-extrabold text-pink-700">
            <span class="heading-marquee">
              <span class="track">Créer votre compte&nbsp;&nbsp;Créer votre compte&nbsp;&nbsp;</span>
            </span>
          </h2>
          <p class="text-sm text-pink-600">Rejoignez-nous pour commander nos douceurs artisanales.</p>
        </div>

        <!-- Alertes venant du serveur -->
        <?php if (isset($_GET['error'])): ?>
          <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <?= htmlspecialchars($_GET['error']) ?>
          </div>
        <?php endif; ?>

        <!-- Message d’erreur global JS -->
        <div id="formErr" class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 hidden"></div>

        <!-- Formulaire -->
        <form method="POST" action="../../../controllers/registerController.php" class="space-y-4" id="registerForm" novalidate>

          <!-- Prénom -->
          <div>
            <label for="prenom" class="block text-sm text-pink-700 mb-1">Prénom</label>
            <div id="prenomErr" class="text-red-600 text-sm mb-1 hidden"></div>
            <input type="text" id="prenom" name="prenom"
                   value="<?= isset($_GET['prenom']) ? htmlspecialchars($_GET['prenom']) : '' ?>"
                   class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 outline-none focus:ring-2 focus:ring-pink-200">
          </div>

          <!-- Nom -->
          <div>
            <label for="nom" class="block text-sm text-pink-700 mb-1">Nom</label>
            <div id="nomErr" class="text-red-600 text-sm mb-1 hidden"></div>
            <input type="text" id="nom" name="nom"
                   value="<?= isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : '' ?>"
                   class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 outline-none focus:ring-2 focus:ring-pink-200">
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm text-pink-700 mb-1">Email</label>
            <div id="emailErr" class="text-red-600 text-sm mb-1 hidden"></div>
            <input type="email" id="email" name="email"
                   value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>"
                   class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 outline-none focus:ring-2 focus:ring-pink-200">
          </div>

          <!-- Rôle -->
          <div>
            <label for="role" class="block text-sm text-pink-700 mb-1">Rôle</label>
            <select id="role" name="role"
                    class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 outline-none focus:ring-2 focus:ring-pink-200">
              <option value="client" <?= (isset($_GET['role']) && $_GET['role'] === 'client') ? 'selected' : '' ?>>client</option>
            </select>
            <p class="mt-1 text-xs text-pink-500">Le rôle “client” est attribué par défaut.</p>
          </div>

          <!-- Mot de passe -->
          <div>
            <label for="password" class="block text-sm text-pink-700 mb-1">Mot de passe</label>
            <div id="pwdErr" class="text-red-600 text-sm mb-1 hidden"></div>
            <div class="relative">
              <input type="password" id="password" name="password"
                     class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 pr-10 outline-none focus:ring-2 focus:ring-pink-200">
              <button type="button" id="togglePassword"
                      class="absolute right-3 top-2.5 text-pink-400 hover:text-pink-600">👁️</button>
            </div>
            <p class="text-xs text-pink-500 mt-1">Au moins 6 caractères.</p>
          </div>

          <!-- Confirmation -->
          <div>
            <label for="confirm_password" class="block text-sm text-pink-700 mb-1">Confirmer le mot de passe</label>
            <div id="matchErr" class="text-red-600 text-sm mb-1 hidden"></div>
            <input type="password" id="confirm_password" name="confirm_password"
                   class="w-full rounded-xl border border-pink-200 bg-white px-3 py-2.5 outline-none focus:ring-2 focus:ring-pink-200">
          </div>

          <!-- CGU -->
          <div class="flex items-start gap-3">
            <input type="checkbox" id="terms" name="terms"
                   class="mt-1 h-4 w-4 rounded border-pink-300 text-pink-600 focus:ring-pink-400">
            <label for="terms" class="text-sm text-gray-700">
              J’accepte les <a href="#" class="text-pink-600 hover:text-pink-700">Conditions d’utilisation</a>.
            </label>
          </div>

          <!-- Submit -->
          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-white font-medium shadow hover:bg-pink-700 active:bg-pink-800 transition">
            Créer mon compte
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Vous avez déjà un compte ?
            <a href="../login/index.php" class="text-pink-600 hover:text-pink-700 font-medium">
              <span class="link-marquee"><span class="track">Se connecter&nbsp;&nbsp;Se connecter&nbsp;&nbsp;</span></span>
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
    // --- Récup des éléments
    const form   = document.getElementById('registerForm');
    const prenom = document.getElementById('prenom');
    const nom    = document.getElementById('nom');
    const email  = document.getElementById('email');
    const pwd    = document.getElementById('password');
    const cpwd   = document.getElementById('confirm_password');
    const terms  = document.getElementById('terms');

    const formErr  = document.getElementById('formErr');
    const prenomErr= document.getElementById('prenomErr');
    const nomErr   = document.getElementById('nomErr');
    const emailErr = document.getElementById('emailErr');
    const pwdErr   = document.getElementById('pwdErr');
    const matchErr = document.getElementById('matchErr');

    // --- Helpers
    function setBorder(el, state) {
      el.classList.remove('border-red-500','border-green-500','focus:ring-red-200','focus:ring-green-200');
      if (state === 'red')   el.classList.add('border-red-500','focus:ring-red-200');
      if (state === 'green') el.classList.add('border-green-500','focus:ring-green-200');
    }
    function showErr(div, msg) {
      div.textContent = msg || '';
      div.classList.toggle('hidden', !msg);
    }

    // --- Règles
    function validatePrenom() {
      const ok = (prenom.value || '').trim() !== '';
      setBorder(prenom, ok ? 'green' : 'red');
      showErr(prenomErr, ok ? '' : 'Le prénom est obligatoire.');
      return ok;
    }
    function validateNom() {
      const ok = (nom.value || '').trim() !== '';
      setBorder(nom, ok ? 'green' : 'red');
      showErr(nomErr, ok ? '' : 'Le nom est obligatoire.');
      return ok;
    }
    function validateEmail() {
      const v = (email.value || '').trim();
      if (!v) { setBorder(email, 'red'); showErr(emailErr, 'L’email est obligatoire.'); return false; }
      const ok = v.includes('@'); // règle simple
      setBorder(email, ok ? 'green' : 'red');
      showErr(emailErr, ok ? '' : "L’email doit contenir '@'.");
      return ok;
      // (plus strict) /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)
    }
    function validatePwdLen() {
      const v = pwd.value || '';
      const ok = v.length >= 6;
      setBorder(pwd, ok ? 'green' : 'red');
      showErr(pwdErr, ok ? '' : 'Le mot de passe doit contenir au moins 6 caractères.');
      return ok;
    }
    function validateConfirm() {
      const ok = (cpwd.value || '') === (pwd.value || '');
      setBorder(cpwd, ok ? 'green' : 'red');
      showErr(matchErr, ok ? '' : 'Les mots de passe ne correspondent pas.');
      return ok;
    }

    // --- Live validation
    prenom.addEventListener('input', validatePrenom);
    nom.addEventListener('input',    validateNom);
    email.addEventListener('input',  validateEmail);
    pwd.addEventListener('input',    () => { validatePwdLen(); validateConfirm(); });
    cpwd.addEventListener('input',   validateConfirm);

    // --- Submit
    form.addEventListener('submit', (e) => {
      formErr.classList.add('hidden');

      const allEmpty =
        (prenom.value.trim()==='') &&
        (nom.value.trim()==='') &&
        (email.value.trim()==='') &&
        (pwd.value==='') &&
        (cpwd.value==='');

      const okPre  = validatePrenom();
      const okNomV = validateNom();
      const okMail = validateEmail();
      const okPwd  = validatePwdLen();
      const okCpwd = validateConfirm();
      const okTerms= terms.checked;

      if (allEmpty) {
        e.preventDefault();
        formErr.textContent = 'Veuillez remplir le formulaire.';
        formErr.classList.remove('hidden');
        return;
      }
      if (!okTerms) {
        e.preventDefault();
        formErr.textContent = "Veuillez accepter les Conditions d’utilisation.";
        formErr.classList.remove('hidden');
        return;
      }
      if (!okPre || !okNomV || !okMail || !okPwd || !okCpwd) {
        e.preventDefault();
        formErr.textContent = 'Corrigez les champs en rouge.';
        formErr.classList.remove('hidden');
      }
    });

    // Afficher / masquer le mot de passe
    document.getElementById('togglePassword')?.addEventListener('click', function () {
      const isHidden = pwd.type === 'password';
      pwd.type = isHidden ? 'text' : 'password';
      this.textContent = isHidden ? '🙈' : '👁️';
    });
  </script>
</body>
</html>
