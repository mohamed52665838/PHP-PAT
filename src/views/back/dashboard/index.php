<?php
// views/admin/index.php (refactor lisible, même UI)
session_start();

require_once __DIR__ . '/../../../controllers/produitsController.php';
require_once __DIR__ . '/../../../controllers/userController.php';

/* ------------------------- Auth: admin only ------------------------- */
$isAuthenticated = isset($_SESSION['user']) && is_array($_SESSION['user']);
$user  = $isAuthenticated ? $_SESSION['user'] : null;
$role  = strtolower($user['role'] ?? '');

if (!$isAuthenticated || $role !== 'admin') {
  header('Location: ../../front/home/index.php?error=forbidden');
  exit;
}

/* ------------------------- Helpers (affichage) ---------------------- */
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function badge(string $text, string $classes): string {
  return '<span class="inline-flex px-2 py-0.5 rounded text-xs '.$classes.'">'.e($text).'</span>';
}

function role_badge(string $role): string {
  $r = strtolower($role);
  return match ($r) {
    'client'      => badge($role, 'bg-pink-100 text-pink-700'),
    'preparateur' => badge($role, 'bg-rose-100 text-rose-700'),
    'admin'       => badge($role, 'bg-amber-100 text-amber-700'),
    default       => badge($role, 'bg-gray-100 text-gray-700'),
  };
}

function stock_badge(int $stock): string {
  $ok = $stock >= 10;
  return badge((string)$stock, $ok ? 'bg-blue-100 text-emerald-700' : 'bg-rose-100 text-rose-700');
}

/* ------------------------- Données ------------------------- */
$pc = new ProduitsController();/* pour gerer recuperation depuis base*/
$uc = new UserController();

$produits = [];//initilisation tab vides
$users    = [];
/* getProduits  retourne objet PDOStatement (résultat d’une requête SQL). 
fetchAll récupère toutes les lignes
*/
try { $produits = $pc->getProduits()->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
try { $users = $uc->getUsers()->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}



/* KPIs  */
/* le compteur qui vas compter initilisation tableau de 3 cle client prepa admin  */
$rolesCount = [
  'client'      => 0,
  'preparateur' => 0,
  'admin'       => 0,
];
/* naprcouri tableau 3ala kol user nchof role ta3o o n3mlolo miniscule  khtr fil base miniscule 
o ba3ed nimchi rolesCount nchof kn mwjod case fiha nom mta3 client oila admin oila prep o nzid fi
nzid fi role ili kima user heka  */
foreach ($users as $u) {
  $r = strtolower($u['role'] ?? '');
  if (isset($rolesCount[$r])) $rolesCount[$r]++;
}
/* kpis dans php ichabah l map howa tableau associative fih cle valeur  */
$kpis = [
  'produits' => count($produits),
  'users'    => count($users),
  'stockLow' => count(array_filter($produits, fn($p)=> (int)($p['stock'] ?? 0) < 10)),
  /* nhseb kain les roles ili stock fihom aal min 10  */
  'orders'   => 0, // TODO: branche table commandes si dispo
  'roles'    => $rolesCount,
];

/* Sous-listes */
/* bich na5o mathln awel 5 whad mkaydin o naffichihom array_slice t5alini ina7i min tableau 5 premier elemnet ili houma */
$lastProducts = array_slice($produits, 0, 5);
$lastUsers    = array_slice($users, 0, 5);

/* Données pour ApexCharts (simple et sûr) */

/* series bich ikoun fiha tableau des valeur mta clients prepa admins [2,4,5] array_values bich tnahilna
key min kpis o t5ali kain values
lables nhot fiha les asemi assosie l chaque case o colors coleurs haque case  */
$chartRoles = [
  'series' => array_values($kpis['roles']),
  'labels' => ['Clients', 'Préparateurs', 'Admins'],
  'colors' => ['#ec4899', '#f43f5e', '#f59e0b'],
];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Admin — Pâtisserie</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <style>
    html { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial; }
    @keyframes slide { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }
    .animate-slide { animation: slide 8s linear infinite; }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-rose-100 text-gray-800">

  <!-- Topbar -->
  <header class="sticky top-0 z-30 bg-gradient-to-r from-pink-600 to-rose-500 text-white shadow relative">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <!-- Branding -->
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-white/20 flex items-center justify-center font-bold">P</div>
        <div>
          <h1 class="text-xl font-semibold">Pâtisserie — Admin</h1>
          <p class="text-xs text-white/90">Tableau de bord</p>
        </div>
      </div>

      <!-- Message Admin -->
      <div class="hidden md:block overflow-hidden">
        <p class="whitespace-nowrap animate-slide">
          👋 Welcome Admin <span class="font-bold"><?= e($user['prenom'] ?? '') ?></span>
        </p>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-2">
        <button id="btnQuick"
                class="group inline-flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm hover:bg-white/20 transition"
                title="Menu rapide (Ctrl+K)">
          <svg class="h-5 w-5 opacity-90" viewBox="0 0 24 24" fill="currentColor"><path d="M4 7h16v2H4zM4 11h16v2H4zM4 15h16v2H4z"/></svg>
          <span class="hidden sm:inline">Menu rapide</span>
        </button>
<!-- il ism en majuscule ili bich ikoun fil dowira ta dashbord kain prenom null ?? nhot par defaut A  -->
        <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center font-semibold">
          <?= e(strtoupper(string:substr($user['prenom'] ?? 'A', 0,2 ))) ?>
        </div>
      </div>
    </div>

    <!-- Quick menu -->
    <nav id="quickMenu"
         class="hidden absolute right-6 mt-2 w-64 rounded-xl bg-white text-gray-800 shadow-lg ring-1 ring-black/5 overflow-hidden">
      <a href="../../front/home/index.php"              class="flex items-center gap-2 px-4 py-2.5 hover:bg-pink-50">🏠 Accueil (site)</a>
      <a href="../../front/produits/getProduits.php"    class="flex items-center gap-2 px-4 py-2.5 hover:bg-pink-50">🍰 Gérer les produits</a>
      <a href="../../front/users/getUsers.php"          class="flex items-center gap-2 px-4 py-2.5 hover:bg-pink-50">👤 Gérer les utilisateurs</a>
      <a href="../../../controllers/logoutController.php" class="flex items-center gap-2 px-4 py-2.5 hover:bg-rose-50">🚪 Déconnexion</a>
    </nav>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-8 space-y-8">

    <!-- Liens rapides -->
    <section class="flex flex-wrap gap-3">
      <a href="../../front/produits/getProduits.php"
         class="inline-flex items-center gap-2 rounded-xl bg-pink-600 text-white px-4 py-2 hover:bg-pink-700 shadow">🍰 Gérer les produits</a>
      <a href="../../front/users/getUsers.php"
         class="inline-flex items-center gap-2 rounded-xl bg-rose-500 text-white px-4 py-2 hover:bg-rose-600 shadow">👤 Gérer les utilisateurs</a>
    </section>

    <!-- KPIs -->
     <!-- partie win mahtouta les info kdch min commandes kadch min utilisateur  -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="rounded-2xl bg-white/80 backdrop-blur p-5 shadow border border-white">
        <p class="text-sm text-gray-500">Produits</p>
        <p class="mt-2 text-2xl font-semibold"><?= number_format($kpis['produits']) ?></p>
      </div>
      <div class="rounded-2xl bg-white/80 backdrop-blur p-5 shadow border border-white">
        <p class="text-sm text-gray-500">Utilisateurs</p>
        <p class="mt-2 text-2xl font-semibold"><?= number_format($kpis['users']) ?></p>
      </div>
      <div class="rounded-2xl bg-white/80 backdrop-blur p-5 shadow border border-white">
        <p class="text-sm text-gray-500">Stock bas (&lt; 10)</p>
        <p class="mt-2 text-2xl font-semibold text-rose-600"><?= number_format($kpis['stockLow']) ?></p>
      </div>
      <div class="rounded-2xl bg-white/80 backdrop-blur p-5 shadow border border-white">
        <p class="text-sm text-gray-500">Commande</p>
        <p class="mt-2 text-2xl font-semibold"><?= number_format($kpis['orders']) ?></p>
      </div>
    </section>

    <!-- Graphique + Raccourcis -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 rounded-2xl bg-white/80 backdrop-blur p-6 shadow border border-white">
        <div class="flex items-center justify-between">
          <h3 class="font-semibold">Répartition des utilisateurs par rôle</h3>
        </div>
        <div id="chartRoles" class="mt-4"></div>
        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
          <div class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded bg-pink-600"></span> Clients: <?= (int)$kpis['roles']['client'] ?></div>
          <div class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded bg-rose-500"></span> Préparateurs: <?= (int)$kpis['roles']['preparateur'] ?></div>
          <div class="flex items-center gap-2"><span class="inline-block h-3 w-3 rounded bg-amber-500"></span> Admins: <?= (int)$kpis['roles']['admin'] ?></div>
        </div>
      </div>

      <aside class="rounded-2xl bg-white/80 backdrop-blur p-6 shadow border border-white">
        <h3 class="font-semibold">Raccourcis</h3>
        <div class="mt-4 grid grid-cols-1 gap-2 text-sm">
          <a href="../../front/produits/indexProduits.php" class="rounded-lg border border-pink-200 bg-pink-50 px-3 py-2 hover:bg-pink-100">➕ Ajouter un produit</a>
          <a href="../../front/users/indexUsers.php"     class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 hover:bg-rose-100">👤 Créer un utilisateur</a>
          <a href="../../front/produits/getProduits.php" class="rounded-lg border bg-white px-3 py-2 hover:bg-gray-50">📦 Liste des produits</a>
          <a href="../../front/users/getUsers.php"       class="rounded-lg border bg-white px-3 py-2 hover:bg-gray-50">📋 Liste utilisateurs</a>
        </div>
      </aside>
    </section>

    <!-- Tables -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Produits -->
      <div class="rounded-2xl bg-white/80 backdrop-blur p-6 shadow border border-white overflow-hidden">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold">Derniers produits</h3>
          <a href="../../front/produits/getProduits.php" class="text-sm text-pink-600 hover:text-pink-700">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-pink-50 text-gray-700">
              <tr>
                <th class="px-3 py-2 text-left">#</th>
                <th class="px-3 py-2 text-left">Nom</th>
                <th class="px-3 py-2 text-left">Prix</th>
                <th class="px-3 py-2 text-left">Stock</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($lastProducts)): ?>
                <?php foreach ($lastProducts as $p): ?>
                  <tr class="border-t">
                    <td class="px-3 py-2"><?= e($p['id']) ?></td>
                    <td class="px-3 py-2"><?= e($p['nom']) ?></td>
                    <td class="px-3 py-2"><?= number_format((float)$p['prix'], 2, ',', ' ') ?> DT</td>
                    <td class="px-3 py-2"><?= stock_badge((int)($p['stock'] ?? 0)) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Aucun produit.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-4 flex gap-2">
          <a href="../../front/produits/indexProduits.php" class="inline-flex items-center gap-2 rounded-lg bg-pink-600 text-white px-3 py-2 hover:bg-pink-700">➕ Ajouter</a>
          <a href="../../front/produits/getProduits.php"   class="inline-flex items-center gap-2 rounded-lg border bg-white px-3 py-2 hover:bg-gray-50">Gérer</a>
        </div>
      </div>

      <!-- Utilisateurs -->
      <div class="rounded-2xl bg-white/80 backdrop-blur p-6 shadow border border-white overflow-hidden">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold">Derniers utilisateurs</h3>
          <a href="../../front/users/getUsers.php" class="text-sm text-pink-600 hover:text-pink-700">Voir tout</a>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-rose-50 text-gray-700">
              <tr>
                <th class="px-3 py-2 text-left">#</th>
                <th class="px-3 py-2 text-left">Nom</th>
                <th class="px-3 py-2 text-left">Email</th>
                <th class="px-3 py-2 text-left">Rôle</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($lastUsers)): ?>
                <?php foreach ($lastUsers as $u): ?>
                  <tr class="border-t">
                    <td class="px-3 py-2"><?= e($u['id']) ?></td>
                    <td class="px-3 py-2"><?= e(($u['prenom'] ?? '').' '.($u['nom'] ?? '')) ?></td>
                    <td class="px-3 py-2"><?= e($u['email'] ?? '') ?></td>
                    <td class="px-3 py-2"><?= role_badge($u['role'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">Aucun utilisateur.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-4 flex gap-2">
          <a href="../../front/users/indexUsers.php" class="inline-flex items-center gap-2 rounded-lg bg-rose-500 text-white px-3 py-2 hover:bg-rose-600">➕ Ajouter</a>
          <a href="../../front/users/getUsers.php"   class="inline-flex items-center gap-2 rounded-lg border bg-white px-3 py-2 hover:bg-gray-50">Gérer</a>
        </div>
      </div>
    </section>

  </main>

  <footer class="border-t bg-white/60">
    <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-gray-600">
      © <?= date('Y') ?> Pâtisserie — Admin
    </div>
  </footer>

  <script>
    /* --------- Menu rapide (simple et lisible) --------- */
    /*  hidden dans html se fait initilisation  avec tailwindcss si on met hiden elle met display:non
    done elle ne fait pas laffichage si on clic  */ 
    const btnQuick  = document.getElementById('btnQuick');
    const quickMenu = document.getElementById('quickMenu');

    btnQuick.addEventListener('click', (e) => {
      e.stopPropagation();
      quickMenu.classList.toggle('hidden');
    });
    // ninzel ay blasa mil document trja hidden o ywali m3ach yaffichi menu 
    document.addEventListener('click', () => quickMenu.classList.add('hidden'));

    /* --------- Donut des rôles (données passées depuis PHP) --------- */
    const rolesData = <?= json_encode($chartRoles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    //bich nkrw les valeurs mta tableau php par js 
    //walina 7awelna tableau php li json lisible par js
    const el = document.querySelector('#chartRoles');
    //jebna div masoula 3al affichage mta chart 
    if (el) {
      //kain jebna div ta chart n3mlo new object du chart o nhotolo el ili hia masoula al affichage graphiue 
      //el va recevoir graphique
      new ApexCharts(el, {
        //fama type pie
        //toolbar howa win njm ntelechargi chart graphique
        chart:   { type: 'donut', height: 330, toolbar: { show: false } },
        //chofna rolesdata fouk fih 3 case kol whda fiha valeur ex
        //rolesdata =[[2,4,5],["cleint","preparateur","admin"],["ec4899","",""]]
        series:  rolesData.series,//[2,4,5]
        labels:  rolesData.labels,//["cleint","preparateur","admin"]
        colors:  rolesData.colors,//["ec4899","",""]
        plotOptions: { pie: { donut: { size: '64%' } } },
          //3ordh dowira 64%
        legend:  { position: 'bottom' },
        //coleur ta kol haja 
        dataLabels: { enabled: true, formatter: (v) => `${v.toFixed(1)}%` }
        //bih nhot les pourcentage 3al dowira o valeur b virgule 
      }).render();
      //.render() nskro biha configuration o norsmo e1
    }
  </script>

</body>
</html>
