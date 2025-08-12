<?php
require_once '../../../../src/controllers/userController.php';
require_once '../../../../src/models/User.php';

$controller = new UserController();

$message = '';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    $user = new User($nom, $prenom, $password, $role, $email);
    $controller->add($user);

    header('Location: getUsers.php?message=' . urlencode('Utilisateur ajouté.'));
    exit;
}

$userAModifier = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $users = $controller->getUsers();
    foreach ($users as $p) {
        if ((int)$p['id'] === $id) {
            $userAModifier = $p;
            break;
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $id = intval($_POST['id']);

    $user = new User($nom, $prenom, $password, $role, $email);
    $controller->update($user, $id);

    header('Location: getUsers.php?message=' . urlencode('Utilisateur modifié.'));
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Gestion Utilisateurs</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
  .marquee-wrap{position:relative;display:inline-block;overflow:hidden;height:1.4em;max-width:22ch;vertical-align:middle}
  .marquee-track{display:inline-block;white-space:nowrap;padding-left:100%;animation:marquee 9s linear infinite}
  @keyframes marquee{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}
  .marquee-wrap:hover .marquee-track{animation-play-state:paused}
  .btn-marquee{position:relative;display:inline-block;overflow:hidden;height:1.2em;max-width:14ch;vertical-align:middle}
  .btn-marquee .track{display:inline-block;white-space:nowrap;padding-left:100%;animation:marqueeBtn 7s linear infinite}
  @keyframes marqueeBtn{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}
  @media (prefers-reduced-motion:reduce){
    .marquee-track,.btn-marquee .track{animation:none;padding-left:0}
  }
  .marquee-top{position:relative;display:inline-block;overflow:hidden;white-space:nowrap;animation:slideText 8s linear infinite;font-weight:bold}
  @keyframes slideText{0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
  @keyframes slide{0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
  .animate-slide{animation:slide 8s linear infinite}
  </style>
</head>
<body class="min-h-screen bg-pink-100 from-red-50 via-rose-300 to-red-200 text-gray-800">
<?php if (isset($_GET['status'])): ?>
  <div id="flash" class="mx-auto max-w-3xl my-4">
    <?php if ($_GET['status'] === 'updated'): ?>
      <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow">
        ✅ Utilisateur modifié avec succès.
      </div>
    <?php elseif ($_GET['status'] === 'added'): ?>
      <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 shadow">
        ✅ Utilisateur ajouté avec succès.
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

  <div class="max-w-3xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-pink-600 via-rose-500 to-fuchsia-500 p-6 mb-8 text-white shadow">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-white/10 blur-2xl"></div>
      <div class="absolute -left-12 -bottom-12 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>

      <div class="absolute top-4 right-4">
        <a href="../../back/dashboard/index.php" 
           class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-pink-700 font-medium shadow hover:bg-pink-50 transition">
          🏠 Retour Admin
        </a>
      </div>

      <div class="relative flex items-start gap-3">
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur text-xl">👤</span>
        <div>
          <div class="bg-pink-600 text-white py-3 shadow overflow-hidden">
            <div class="whitespace-nowrap animate-slide inline-block">
              <?= $userAModifier ? '✏️ Modifier un utilisateur' : '➕ Ajouter un utilisateur' ?>
            </div>
          </div>
          <p class="text-white/90">
            <?= $userAModifier ? 'Mettez à jour les informations de ce compte.' : 'Créez un nouveau compte utilisateur.' ?>
          </p>
        </div>
      </div>
    </div>

    <?php if (!empty($message)): ?>
      <div class="mb-6 rounded-xl border border-pink-200 bg-pink-50 px-4 py-3 text-pink-800">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <!-- Formulaire -->
    <div class="text-gray-800 rounded-2xl border border-red-200 bg-pink-300 backdrop-blur shadow-sm p-6">
      <?php if ($userAModifier): ?>
        <!-- ====== UPDATE ====== -->
        <form method="post" class="space-y-5" id="formUpdate" novalidate>
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="id" value="<?= (int)$userAModifier['id']; ?>">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white mb-1">Nom</label>
              <input type="text" name="nom"
                     value="<?= htmlspecialchars($userAModifier['nom']); ?>"
                     class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:outline-none" />
            </div>
            <div>
              <label class="block text-sm text-white mb-1">Prénom</label>
              <input type="text" name="prenom"
                     value="<?= htmlspecialchars($userAModifier['prenom']); ?>"
                     class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white mb-1">Rôle</label>
              <select name="role"
                      class="w-full text-gray-800 rounded-xl border border-pink-200 px-3 py-2.5 bg-white focus:outline-none">
                <option value="client"      <?= $userAModifier['role']==='client'?'selected':''; ?>>Client</option>
                <option value="preparateur" <?= $userAModifier['role']==='preparateur'?'selected':''; ?>>Préparateur</option>
                <option value="admin"       <?= $userAModifier['role']==='admin'?'selected':''; ?>>Admin</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-white mb-1">Email</label>
              <input type="text" name="email"
                     value="<?= htmlspecialchars($userAModifier['email']); ?>"
                     class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:outline-none" />
            </div>
          </div>

          <div class="relative">
            <label class="block text-sm text-white mb-1">Mot de passe</label>
            <input type="password" id="password" name="password"
                   value="<?= isset($userAModifier) ? htmlspecialchars($userAModifier['password']) : '' ?>"
                   class="w-full rounded-xl border border-pink-200 px-3 py-2.5 pr-10 focus:outline-none" />
            <button type="button" id="togglePassword"
                    class="absolute right-3 top-8 text-pink-400 hover:text-pink-600 focus:outline-none">
              👁️
            </button>
            <p class="mt-1 text-xs text-white">Astuce : utilisez au moins 8 caractères.</p>
          </div>

          <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-5 py-2.5 text-white font-medium shadow hover:bg-pink-700 transition">
              <span class="btn-marquee">
                <span class="track">💾 Enregistrer&nbsp;&nbsp;💾 Enregistrer&nbsp;&nbsp;</span>
              </span>
            </button>

            <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>"
               class="inline-flex items-center gap-2 rounded-xl border border-pink-200 bg-white px-5 py-2.5 text-pink-700 hover:bg-pink-50 transition">
              ✖️ Annuler
            </a>
            <a href="../../back/dashboard/index.php"
               class="ml-auto text-sm text-pink-600 hover:text-pink-800">← Retour à la liste</a>
          </div>
        </form>

      <?php else: ?>
        <!-- ====== ADD ====== -->
        <form method="post" class="space-y-5" id="formAdd" novalidate>
          <input type="hidden" name="action" value="add">

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white mb-1">Nom</label>
              <input type="text" name="nom"
                     class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:outline-none" />
            </div>
            <div>
              <label class="block text-sm text-white mb-1">Prénom</label>
              <input type="text" name="prenom"
                     class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm text-white mb-1">Rôle</label>
              <select name="role"
                      class="w-full rounded-xl border border-pink-200 px-3 py-2.5 bg-white focus:outline-none">
                <option value="">--Choisir un rôle--</option>
                <option value="client">Client</option>
                <option value="preparateur">Préparateur</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-white mb-1">Email</label>
              <input type="text" name="email"
                     class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:outline-none" />
            </div>
          </div>

          <div class="relative">
            <label class="block text-sm text-white mb-1">Mot de passe</label>
            <input type="password" id="passwordAdd" name="password"
                   class="w-full rounded-xl border border-pink-200 px-3 py-2.5 pr-10 focus:outline-none" />
            <button type="button" id="togglePasswordAdd"
                    class="absolute right-3 top-8 text-pink-400 hover:text-pink-600 focus:outline-none">
              👁️
            </button>
            <p class="mt-1 text-xs text-white">Astuce : utilisez au moins 8 caractères.</p>
          </div>

          <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-5 py-2.5 text-white font-medium shadow hover:bg-pink-700 transition">
              <span class="btn-marquee">
                <span class="track">➕ Ajouter&nbsp;&nbsp;➕ Ajouter&nbsp;&nbsp;</span>
              </span>
            </button>

            <a href="getUsers.php"
               class="inline-flex items-center gap-2 rounded-xl border border-pink-200 bg-white px-5 py-2.5 text-pink-700 hover:bg-pink-50 transition">
              ↩️ Retour à la liste
            </a>
          </div>
        </form>
      <?php endif; ?>
    </div>

    <p class="mt-6 text-center text-xs text-pink-500">© <?= date('Y') ?> — Gestion des utilisateurs</p>
  </div>
<script>
/* ===== Toggle voir/masquer mot de passe (update) ===== */
document.getElementById("togglePassword")?.addEventListener("click", function() {
  const pwdField = document.getElementById("password");
  const isHidden = pwdField.type === "password";
  pwdField.type = isHidden ? "text" : "password";
  this.textContent = isHidden ? "🙈" : "👁️";
});

/* ===== Toggle voir/masquer mot de passe (add) ===== */
document.getElementById("togglePasswordAdd")?.addEventListener("click", function() {
  const pwdField = document.getElementById("passwordAdd");
  if (!pwdField) return;
  const isHidden = pwdField.type === "password";
  pwdField.type = isHidden ? "text" : "password";
  this.textContent = isHidden ? "🙈" : "👁️";
});

/* ===== Validation JS (bordures rouge/verte) + message inline ===== */
function attachValidation(form) {
  if (!form) return;

  // Crée/attrape un conteneur d'erreur inline en haut du formulaire
  let errorBox = form.querySelector(".form-error");
  if (!errorBox) {
    errorBox = document.createElement("div");
    errorBox.className = "form-error mb-4 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700 hidden";
    form.prepend(errorBox);
  }

  const nom      = form.querySelector("[name='nom']");
  const prenom   = form.querySelector("[name='prenom']");
  const email    = form.querySelector("[name='email']");
  const password = form.querySelector("[name='password']");

  function setBorder(el, ok) {
    if (!el) return true;
    el.classList.remove("border-red-500","border-green-500");
    el.classList.add(ok ? "border-green-500" : "border-red-500");
    return ok;
  }

  function valNom()     { return setBorder(nom,    (nom?.value.trim().length ?? 0) >= 2); }
  function valPrenom()  { return setBorder(prenom, (prenom?.value.trim().length ?? 0) >= 2); }
  function valEmail()   {
    const v = (email?.value || '').trim();
    return setBorder(email, v.includes("@")); // vert si contient "@", sinon rouge
  }
  function valPassword(){
    const v = (password?.value || '');
    return setBorder(password, v.length >= 6);
  }

  // Live feedback
  function hideFormError() {
    if (!errorBox.classList.contains("hidden")) errorBox.classList.add("hidden");
  }
  nom?.addEventListener("input", () => { valNom(); hideFormError(); });
  prenom?.addEventListener("input", () => { valPrenom(); hideFormError(); });
  email?.addEventListener("input", () => { valEmail(); hideFormError(); });
  password?.addEventListener("input", () => { valPassword(); hideFormError(); });

  // Init couleurs au chargement
  valNom(); valPrenom(); valEmail(); valPassword();

  function showFormError(msg) {
    errorBox.textContent = msg;
    errorBox.classList.remove("hidden");
  }

  // Soumission : message inline + focus sur le 1er champ rouge
  form.addEventListener("submit", (e) => {
    const okNom = valNom();
    const okPre = valPrenom();
    const okEml = valEmail();
    const okPwd = valPassword();
    const ok = okNom && okPre && okEml && okPwd;

    if (!ok) {
      e.preventDefault();
      showFormError("⚠️ Corrigez les champs en rouge avant d'envoyer.");
      ( !okNom ? nom : !okPre ? prenom : !okEml ? email : password )?.focus();
    } else {
      hideFormError();
    }
  });
}

// Attacher sur les deux formulaires si présents
attachValidation(document.getElementById("formAdd"));
attachValidation(document.getElementById("formUpdate"));
</script>

</body>
</html>
