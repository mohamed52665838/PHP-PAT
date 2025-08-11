<?php
require_once '../../../../src/controllers/userController.php';
require_once '../../../../src/models/User.php';

$controller = new UserController();

$message = $_GET['message'] ?? '';

// --- Suppression ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $controller->delete($id);
    header('Location: getUsers.php?message=' . urlencode('Utilisateur supprimé.'));
    exit;
}

// --- Lecture des filtres & tri depuis la requête ---
$searchId    = trim($_GET['search_id'] ?? '');
$searchName  = trim($_GET['search_name'] ?? '');
$searchEmail = trim($_GET['search_email'] ?? '');
$searchRole  = trim($_GET['search_role'] ?? '');
$sortParam   = $_GET['sort'] ?? ''; // ex: id_asc, name_desc, email_asc, role_desc ...
[$sortKey, $sortDir] = array_pad(explode('_', $sortParam), 2, null);

// --- Récupération brute et conversion en array ---
$stmt = $controller->getUsers(); // getUsers() renvoie un PDOStatement
$users = $stmt instanceof PDOStatement
    ? $stmt->fetchAll(PDO::FETCH_ASSOC)
    : (is_array($stmt) ? $stmt : []);

// --- Filtrage côté PHP ---
$users = array_filter($users, function ($u) use ($searchId, $searchName, $searchEmail, $searchRole) {
    if ($searchId !== '' && strval($u['id'] ?? '') !== $searchId) return false;

    if ($searchName !== '') {
        $full = trim(($u['nom'] ?? '') . ' ' . ($u['prenom'] ?? ''));
        if (stripos($full, $searchName) === false &&
            stripos(($u['nom'] ?? ''), $searchName) === false &&
            stripos(($u['prenom'] ?? ''), $searchName) === false) {
            return false;
        }
    }

    if ($searchEmail !== '' && stripos(($u['email'] ?? ''), $searchEmail) === false) return false;

    if ($searchRole !== '' && strcasecmp(($u['role'] ?? ''), $searchRole) !== 0) return false;

    return true;
});

// réindexer
$users = array_values($users);

// --- Tri côté PHP ---
$allowedKeys = ['id','name','email','role'];
$sortKey = in_array($sortKey, $allowedKeys, true) ? $sortKey : null;
$sortDir = ($sortDir === 'desc') ? 'desc' : 'asc';

if ($sortKey !== null) {
    usort($users, function ($a, $b) use ($sortKey, $sortDir) {
        if ($sortKey === 'name') {
            $va = trim(($a['nom'] ?? '') . ' ' . ($a['prenom'] ?? ''));
            $vb = trim(($b['nom'] ?? '') . ' ' . ($b['prenom'] ?? ''));
            $cmp = strcasecmp($va, $vb);
        } elseif ($sortKey === 'id') {
            $cmp = intval($a['id'] ?? 0) <=> intval($b['id'] ?? 0);
        } else {
            $va = (string)($a[$sortKey] ?? '');
            $vb = (string)($b[$sortKey] ?? '');
            $cmp = strcasecmp($va, $vb);
        }
        return $sortDir === 'desc' ? -$cmp : $cmp;
    });
}

// helper pour avatar (initiales)
function initials($nom, $prenom) {
    $n = mb_substr(trim((string)$nom), 0, 1, 'UTF-8');
    $p = mb_substr(trim((string)$prenom), 0, 1, 'UTF-8');
    $txt = strtoupper($n . $p);
    return $txt !== '' ? $txt : 'U';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Utilisateurs</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
  .marquee-container {
    position: relative;
    overflow: hidden;
    height: 1.5em; /* hauteur visible */
  }
  .marquee-track {
    display: inline-block;
    white-space: nowrap;
    padding-left: 100%;
    animation: marquee 10s linear infinite;
  }
  @keyframes marquee {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-100%); }
  }
  /* Pause au survol */
  .marquee-container:hover .marquee-track {
    animation-play-state: paused;
  }
</style>

</head>
<body class="min-h-screen bg-gradient-to-br from-pink-50 via-rose-50 to-pink-100 text-gray-800">
  <div class="max-w-6xl mx-auto px-6 py-8">

<!-- Header -->
<div class="mb-6 rounded-2xl border border-pink-200 bg-white shadow-sm p-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
 <div class="marquee-container text-pink-500 font-medium">
  <span class="marquee-track">
    Gérez les comptes : recherche, tri et actions.&nbsp;&nbsp;
    Gérez les comptes : recherche, tri et actions.&nbsp;&nbsp;
  </span>
</div>

    <a href="./indexUsers.php"
       class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-white font-medium shadow hover:bg-pink-700 transition">
      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V6h2v5h5v2h-5v5h-2v-5H6v-2h5z"/></svg>
      Ajouter
    </a>
  </div>
</div>

<!-- Flash -->
<?php if (!empty($message)): ?>
  <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
    <?= htmlspecialchars($message) ?>
  </div>
<?php endif; ?>

<!-- Search Card -->
<div class="mb-6 rounded-2xl border border-pink-200 bg-white shadow-sm">
  <form method="get" class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div>
        <label class="block text-sm text-pink-600 mb-1">ID</label>
        <div class="relative">
          <input type="number" name="search_id" value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>"
                 class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:border-pink-400 focus:ring-2 focus:ring-pink-200 outline-none" />
          <span class="pointer-events-none absolute right-3 top-2.5 text-pink-300">#</span>
        </div>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm text-pink-600 mb-1">Nom / Prénom</label>
        <div class="relative">
          <input type="text" name="search_name" placeholder="Tapez un nom…"
                 value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>"
                 class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:border-pink-400 focus:ring-2 focus:ring-pink-200 outline-none" />
          <span class="pointer-events-none absolute right-3 top-2.5 text-pink-300">🔎</span>
        </div>
      </div>

      <div>
        <label class="block text-sm text-pink-600 mb-1">Email</label>
        <input type="text" name="search_email" placeholder="ex: user@site.tn"
               value="<?= htmlspecialchars($_GET['search_email'] ?? '') ?>"
               class="w-full rounded-xl border border-pink-200 px-3 py-2.5 focus:border-pink-400 focus:ring-2 focus:ring-pink-200 outline-none" />
      </div>

      <div>
        <label class="block text-sm text-pink-600 mb-1">Rôle</label>
        <select name="search_role"
        class="w-full rounded-xl border border-pink-200 px-3 py-2.5 bg-white focus:border-pink-400 focus:ring-2 focus:ring-pink-200 outline-none">
  <option value="">Tous</option>
  <option value="admin"       <?= (($_GET['search_role'] ?? '')==='admin')?'selected':'' ?>>Admin</option>
  <option value="client"      <?= (($_GET['search_role'] ?? '')==='client')?'selected':'' ?>>Client</option>
  <option value="preparateur" <?= (($_GET['search_role'] ?? '')==='preparateur')?'selected':'' ?>>Préparateur</option>
</select>

      </div>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
      <button type="submit"
              class="inline-flex items-center gap-2 rounded-xl bg-pink-600 px-4 py-2.5 text-white hover:bg-pink-700 transition">
        🔍 Rechercher
      </button>
      <a href="getUsers.php"
         class="rounded-xl border border-pink-200 bg-white px-4 py-2.5 hover:bg-pink-50 transition">
        Afficher tous
      </a>

      <div class="ml-auto flex items-center gap-2">
        <label class="text-sm text-pink-600">Trier par</label>
        <select name="sort" onchange="this.form.submit()"
                class="rounded-xl border border-pink-200 px-3 py-2.5 bg-white focus:border-pink-400 focus:ring-2 focus:ring-pink-200 outline-none">
          <?php
          $currentSort = $_GET['sort'] ?? '';
          $opts = [
            '' => '—',
            'id_asc' => 'ID ↑', 'id_desc' => 'ID ↓',
            'name_asc' => 'Nom/Prénom ↑', 'name_desc' => 'Nom/Prénom ↓',
            'email_asc' => 'Email ↑', 'email_desc' => 'Email ↓',
            'role_asc' => 'Rôle ↑', 'role_desc' => 'Rôle ↓',
          ];
          foreach ($opts as $val => $label):
          ?>
            <option value="<?= $val ?>" <?= $currentSort===$val?'selected':'' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </form>
</div>

<!-- Table -->
<div class="rounded-2xl border border-pink-200 bg-white shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-pink-50 text-pink-700">
        <tr>
          <th class="px-4 py-3 text-left font-semibold">ID</th>
          <th class="px-4 py-3 text-left font-semibold">Utilisateur</th>
          <th class="px-4 py-3 text-left font-semibold">Email</th>
          <th class="px-4 py-3 text-left font-semibold">Rôle</th>
          <th class="px-4 py-3 text-left font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-pink-100">
        <?php if (!empty($users) && is_iterable($users)): ?>
          <?php foreach ($users as $u): ?>
            <?php
              $initials = initials($u['nom'] ?? '', $u['prenom'] ?? '');
              $roleClass = 'bg-gray-100 text-gray-700';
              switch (strtolower($u['role'] ?? '')) {
                case 'admin':       $roleClass = 'bg-pink-100 text-pink-700'; break;
                case 'manager':     $roleClass = 'bg-fuchsia-100 text-fuchsia-700'; break;
                case 'client':      $roleClass = 'bg-rose-100 text-rose-700'; break;
                case 'preparateur': $roleClass = 'bg-orange-100 text-orange-700'; break;
              }
            ?>
            <tr class="hover:bg-pink-50/50">
              <td class="px-4 py-3 font-medium text-pink-700"><?= htmlspecialchars($u['id']) ?></td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-9 w-9 rounded-full bg-pink-200 text-pink-800 flex items-center justify-center font-semibold">
                    <?= htmlspecialchars($initials) ?>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">
                      <?= htmlspecialchars(($u['nom'] ?? '').' '.($u['prenom'] ?? '')) ?>
                    </div>
                    <div class="text-xs text-pink-500">ID: <?= htmlspecialchars($u['id']) ?></div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($u['email']) ?></td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs <?= $roleClass ?>">
                  <?= htmlspecialchars($u['role']) ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-2">
                  <a href="indexUsers.php?id=<?= urlencode($u['id']) ?>"
                     class="inline-flex items-center gap-1 rounded-lg bg-pink-500 px-3 py-1.5 text-white hover:bg-pink-600 transition">
                    ✏️ <span class="hidden sm:inline">Modifier</span>
                  </a>
                  <a href="?delete=<?= urlencode($u['id']) ?>"
                     onclick="return confirm('Confirmer la suppression ?');"
                     class="inline-flex items-center gap-1 rounded-lg bg-rose-500 px-3 py-1.5 text-white hover:bg-rose-600 transition">
                    🗑 <span class="hidden sm:inline">Supprimer</span>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="px-6 py-10 text-center">
              <div class="mx-auto w-full max-w-sm">
                <div class="rounded-2xl border border-dashed border-pink-300 bg-white p-8">
                  <p class="text-pink-500">Aucun utilisateur trouvé.</p>
                  <a href="getUsers.php" class="mt-3 inline-block text-pink-600 hover:text-pink-700">Réinitialiser les filtres</a>
                </div>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
</body>

</html>
