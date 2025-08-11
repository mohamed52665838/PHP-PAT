<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php?error=" . urlencode("Please login to access this page"));
    exit();
}

$user = $_SESSION['user'];

// Valeurs à afficher dans le formulaire (en cas de retour avec ?nom=... etc.)
$formNom    = $_GET['nom']    ?? ($user['nom']    ?? '');
$formPrenom = $_GET['prenom'] ?? ($user['prenom'] ?? '');
$formEmail  = $_GET['email']  ?? ($user['email']  ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Profile - Pâtisserie</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <style>
    @media (prefers-reduced-motion: reduce){ *{animation:none!important;transition:none!important;} }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-rose-100">
  <!-- Top bar -->
  <nav class="sticky top-0 z-40 bg-white/70 backdrop-blur border-b border-white/60">
    <div class="max-w-5xl mx-auto px-4">
      <div class="flex h-16 items-center justify-between">
        <a href="index.php" class="flex items-center gap-3">
          <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-pink-400 to-pink-600 text-white flex items-center justify-center shadow">
            <i class="fa-solid fa-cake-candles"></i>
          </div>
          <span class="text-lg font-extrabold tracking-tight text-pink-700">Pâtisserie</span>
        </a>
        <div class="hidden md:flex items-center gap-6">
          <a href="../../back/dashboard/index.php" class="text-sm text-gray-700 hover:text-pink-600 transition">
            <i class="fa-solid fa-house-chimney mr-2"></i>Dashboard
          </a>
          <a href="#" class="text-sm text-gray-700 hover:text-pink-600 transition">
            <i class="fa-solid fa-user-gear mr-2"></i>Profil
          </a>
          <a href="#" class="text-sm text-gray-700 hover:text-pink-600 transition">
            <i class="fa-solid fa-receipt mr-2"></i>Commandes
          </a>
        </div>
        <div class="hidden md:flex items-center gap-3">
          <span class="text-sm font-semibold text-pink-700">
            Bonjour, <?= htmlspecialchars(strtolower($user['nom'])) ?>
          </span>
          <a href="../../../controllers/logoutController.php" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm border hover:bg-gray-50">
            <i class="fa-solid fa-right-from-bracket"></i> Déconnexion
          </a>
        </div>
        <button id="navToggle" class="md:hidden h-9 w-9 rounded-lg ring-1 ring-gray-200 text-gray-700 hover:bg-gray-50">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>
    <div id="navMenu" class="md:hidden hidden border-t border-white/60 bg-white/80 backdrop-blur">
      <div class="px-4 py-3 space-y-2">
        <a href="index.php" class="flex items-center gap-2 text-gray-700 hover:text-pink-600">
          <i class="fa-solid fa-house-chimney"></i><span>Dashboard</span>
        </a>
        <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-pink-600">
          <i class="fa-solid fa-user-gear"></i><span>Profil</span>
        </a>
        <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-pink-600">
          <i class="fa-solid fa-receipt"></i><span>Commandes</span>
        </a>
        <a href="logout.php" class="flex items-center gap-2 text-pink-700 hover:text-pink-600">
          <i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span>
        </a>
      </div>
    </div>
  </nav>

  <main class="max-w-3xl mx-auto px-5 py-10">
    <section class="relative rounded-2xl shadow bg-white/80 backdrop-blur border border-white/60">
      <div class="p-8 md:p-10">
        <div class="text-center mb-8">
          <div class="mx-auto w-20 h-20 bg-pink-100 rounded-2xl flex items-center justify-center mb-4 ring-1 ring-pink-200">
            <i class="fas fa-user text-2xl text-pink-600"></i>
          </div>
          <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800 tracking-tight">Modifier mon profil</h2>
          <p class="text-gray-600 mt-2">Mettez à jour vos informations personnelles</p>
        </div>

        <!-- Messages serveur -->
        <?php if (isset($_GET['error'])): ?>
          <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <?= htmlspecialchars($_GET['error']) ?>
          </div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
          <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            <?= htmlspecialchars($_GET['success']) ?>
          </div>
        <?php endif; ?>

        <!-- Message d’erreur global JS -->
        <div id="formErr" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700 hidden"></div>

        <!-- Form -->
        <form method="POST" action="../../../controllers/editProfilController.php" id="editForm" class="space-y-8" novalidate>
          <!-- Infos personnelles -->
          <div class="rounded-xl border border-white/70 bg-white/70 p-5 md:p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-5 flex items-center">
              <i class="fas fa-user-edit mr-2 text-pink-600"></i>Informations personnelles
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                <div id="prenomErr" class="text-red-600 text-xs mb-1 hidden"></div>
                <input
                  type="text" id="prenom" name="prenom"
                  value="<?= htmlspecialchars($formPrenom) ?>"
                  class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white/80 outline-none focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition"
                />
              </div>

              <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <div id="nomErr" class="text-red-600 text-xs mb-1 hidden"></div>
                <input
                  type="text" id="nom" name="nom"
                  value="<?= htmlspecialchars($formNom) ?>"
                  class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white/80 outline-none focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition"
                />
              </div>
            </div>

            <div class="mt-4">
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <div id="emailErr" class="text-red-600 text-xs mb-1 hidden"></div>
              <!-- type="text" pour éviter la validation HTML native -->
              <input
                type="text" id="email" name="email"
                value="<?= htmlspecialchars($formEmail) ?>"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white/80 outline-none focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition"
              />
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
              <div class="px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-gray-600 flex items-center">
                <i class="fas fa-lock mr-2 text-gray-500"></i>
                <?= htmlspecialchars(ucfirst($user['role'])) ?> (non modifiable)
              </div>
            </div>
          </div>

          <!-- Mot de passe (optionnel) -->
          <div class="rounded-xl border border-white/70 bg-white/70 p-5 md:p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
              <i class="fas fa-key mr-2 text-pink-600"></i>Changer le mot de passe (optionnel)
            </h3>
            <p class="text-sm text-gray-600 mb-5">Laissez vide si vous ne voulez pas le modifier.</p>

            <div class="space-y-4">
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                <input
                  type="password" id="current_password" name="current_password"
                  class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white/80 outline-none focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition"
                />
              </div>

              <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                <div id="pwdErr" class="text-red-600 text-xs mb-1 hidden"></div>
                <input
                  type="password" id="new_password" name="new_password"
                  class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white/80 outline-none focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition"
                />
                <p class="text-xs text-gray-500 mt-1">Au moins 6 caractères si rempli.</p>
              </div>

              <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                <div id="matchErr" class="text-red-600 text-xs mb-1 hidden"></div>
                <input
                  type="password" id="confirm_password" name="confirm_password"
                  class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white/80 outline-none focus:ring-4 focus:ring-pink-200 focus:border-pink-400 transition"
                />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-2 flex flex-col sm:flex-row gap-3">
            <button
              type="submit"
              class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 bg-pink-600 text-white font-semibold shadow hover:bg-pink-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-pink-200"
            >
              <i class="fas fa-save"></i> Mettre à jour
            </button>

            <a href="index.php"
               class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 bg-white text-gray-700 font-semibold border border-gray-200 hover:bg-gray-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-gray-200">
              <i class="fas fa-times"></i> Annuler
            </a>
          </div>
        </form>
      </div>
    </section>
  </main>

  <footer class="py-8 text-center text-xs text-gray-500">
    © <?= date('Y') ?> Pâtisserie — Tous droits réservés.
  </footer>

<script>
  // Elements
  const form   = document.getElementById('editForm');
  const prenom = document.getElementById('prenom');
  const nom    = document.getElementById('nom');
  const email  = document.getElementById('email');

  const curPwd = document.getElementById('current_password');
  const newPwd = document.getElementById('new_password');
  const cPwd   = document.getElementById('confirm_password');

  const formErr  = document.getElementById('formErr');
  const prenomErr= document.getElementById('prenomErr');
  const nomErr   = document.getElementById('nomErr');
  const emailErr = document.getElementById('emailErr');
  const pwdErr   = document.getElementById('pwdErr');
  const matchErr = document.getElementById('matchErr');

  function setBorder(el, state) {
    el.classList.remove('border-red-500','border-green-500','focus:ring-red-200','focus:ring-green-200');
    if (state === 'red')   el.classList.add('border-red-500','focus:ring-red-200');
    if (state === 'green') el.classList.add('border-green-500','focus:ring-green-200');
  }
  function showErr(div, msg) {
    div.textContent = msg || '';
    div.classList.toggle('hidden', !msg);
  }

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
  }

  // Mot de passe (optionnel) :
  // - Si vide → pas d’erreur
  // - Si rempli → longueur >= 6 et confirmation identique si confirm rempli
  function validateNewPwd() {
    const v = newPwd.value || '';
    if (!v) { setBorder(newPwd, null); showErr(pwdErr, ''); return true; }
    const ok = v.length >= 6;
    setBorder(newPwd, ok ? 'green' : 'red');
    showErr(pwdErr, ok ? '' : 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
    return ok;
  }
  function validateConfirmPwd() {
    const v  = newPwd.value || '';
    const v2 = cPwd.value || '';
    if (!v2) { setBorder(cPwd, null); showErr(matchErr, ''); return true; }
    const ok = v !== '' && v === v2;
    setBorder(cPwd, ok ? 'green' : 'red');
    showErr(matchErr, ok ? '' : 'Les mots de passe ne correspondent pas.');
    return ok;
  }

  // Live events
  prenom.addEventListener('input', validatePrenom);
  nom.addEventListener('input',    validateNom);
  email.addEventListener('input',  validateEmail);
  newPwd.addEventListener('input', () => { validateNewPwd(); validateConfirmPwd(); });
  cPwd.addEventListener('input',   validateConfirmPwd);

  // Submit
  form.addEventListener('submit', (e) => {
    formErr.classList.add('hidden');

    const okPre  = validatePrenom();
    const okNomV = validateNom();
    const okMail = validateEmail();
    const okNPwd = validateNewPwd();
    const okCpwd = validateConfirmPwd();

    // Cas: si nouveau mdp vide mais confirm rempli → erreur
    if (!newPwd.value && cPwd.value) {
      e.preventDefault();
      setBorder(cPwd, 'red');
      showErr(matchErr, 'Veuillez saisir un nouveau mot de passe pour confirmer.');
      formErr.textContent = 'Corrigez les champs en rouge.';
      formErr.classList.remove('hidden');
      return;
    }

    if (!okPre || !okNomV || !okMail || !okNPwd || !okCpwd) {
      e.preventDefault();
      formErr.textContent = 'Corrigez les champs en rouge.';
      formErr.classList.remove('hidden');
    }
  });

  // Mobile menu
  document.getElementById('navToggle')?.addEventListener('click', ()=>{
    document.getElementById('navMenu')?.classList.toggle('hidden');
  });
</script>
</body>
</html>
