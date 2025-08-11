<?php
require_once '../../../../src/controllers/produitsController.php';
require_once '../../../../src/models/Produit.php';

$controller = new ProduitsController();
$produits = []; // <- initialisation sûre

$searchId = $_GET['search_id'] ?? '';
$searchName = $_GET['search_name'] ?? '';

// recherche par ID
if ($searchId !== '') {
    $produit = $controller->researcher(intval($searchId));
    $produits = $produit ? [$produit] : [];
}
// recherche par nom (normalize le retour: peut être un produit ou un tableau)
elseif ($searchName !== '') {
    $res = $controller->researchByName($searchName);
    if ($res === null) {
        $produits = [];
    } elseif (is_array($res) && array_keys($res) === range(0, count($res) - 1)) {
        $produits = $res;
    } else {
        $produits = [$res];
    }
} else {
    $produits = $controller->getProduits() ?? [];
}

// suppression
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $controller->delete($id);
    $message = "Produit supprimé.";
    $produits = $controller->getProduits() ?? [];
}

// tri
$sort = $_GET['sort'] ?? null;
if ($sort !== null) {
    $produits = $controller->getProduitsTri($sort) ?? [];
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Liste des produits</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
  .marquee-container {
    position: relative;
    overflow: hidden;
    height: 1.5em; /* hauteur du texte visible */
  }
  .marquee-track {
    display: inline-block;
    white-space: nowrap;
    padding-left: 100%; /* démarre hors écran à droite */
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
<body class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-rose-100 text-gray-800">
  <div class="max-w-6xl mx-auto p-6">

    <!-- Header -->
 <!-- Header -->
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
  <div class="marquee-container mt-1 text-pink-600 font-medium">
    <span class="marquee-track">
      Gérez les produits : recherche, tri et actions.&nbsp;&nbsp;Gérez les comptes : recherche, tri et actions.&nbsp;&nbsp;
    </span>
  </div>

  <div class="flex items-center gap-2">
    <a href="./indexProduits.php"
       class="inline-flex items-center gap-2 rounded-xl bg-pink-600 text-white px-4 py-2.5 shadow hover:bg-pink-700 transition">
      <span>➕</span> Ajouter un produit
    </a>

    <!-- Export PDF: transmet les filtres actuels -->
     <!-- methode get ya3ni les donnes bich ytba3tho bil url -->
<form action="exportpdf.php" method="get" target="_blank">
  <input type="hidden" name="search_id" value="<?= htmlspecialchars($searchId) ?>">
  <input type="hidden" name="search_name" value="<?= htmlspecialchars($searchName) ?>">
  <input type="hidden" name="sort" value="<?= htmlspecialchars($sort ?? '') ?>">
  <button type="submit"
          class="inline-flex items-center gap-2 rounded-xl border border-pink-200 bg-white px-4 py-2.5 hover:bg-pink-50 transition">
    📄 Export PDF
  </button>
</form>

  </div>
</div>


    <?php if (!empty($message)): ?>
      <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <!-- Search Card -->
    <div class="relative overflow-hidden rounded-2xl border border-white/70 bg-white/80 backdrop-blur shadow p-6 mb-6">
      <div class="absolute -right-16 -top-16 h-32 w-32 rounded-full bg-pink-200/30 blur-2xl"></div>
      <div class="absolute -left-10 -bottom-12 h-28 w-28 rounded-full bg-rose-200/40 blur-2xl"></div>

      <h2 class="relative text-lg font-semibold text-pink-700 mb-4">Recherche produits</h2>
      <form method="get" class="relative grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
          <label class="block text-sm text-gray-600 mb-1">ID</label>
          <div class="flex items-center rounded-xl border border-gray-200 bg-white focus-within:ring-2 focus-within:ring-pink-200">
            <span class="px-3 text-gray-400">#</span>
            <input type="number" name="search_id" value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>"
                   class="w-full rounded-r-xl px-2 py-2.5 outline-none" />
          </div>
        </div>

        <div>
          <label class="block text-sm text-gray-600 mb-1">Nom</label>
          <div class="flex items-center rounded-xl border border-gray-200 bg-white focus-within:ring-2 focus-within:ring-pink-200">
            <svg class="mx-3 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M10 4a6 6 0 014.47 9.95l4.29 4.3-1.42 1.4-4.3-4.28A6 6 0 1110 4zm0 2a4 4 0 100 8 4 4 0 000-8z"/></svg>
            <input type="text" name="search_name" placeholder="Tapez un nom…"
                   value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>"
                   class="w-full rounded-r-xl px-2 py-2.5 outline-none" />
          </div>
        </div>

        <div class="flex gap-2 md:justify-end">
          <button type="submit"
                  class="inline-flex items-center gap-2 rounded-xl bg-pink-600 text-white px-4 py-2.5 shadow hover:bg-pink-700 transition">
            🔍 Rechercher
          </button>
          <a href="getProduits.php"
             class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 hover:bg-gray-50 transition">
            ↺ Afficher tous
          </a>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl border border-white/70 bg-white/90 backdrop-blur shadow overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b bg-gradient-to-r from-pink-50 to-rose-50">
        <h3 class="font-semibold text-pink-800">Liste des produits</h3>
        <div class="text-sm">
          <span class="text-gray-500 mr-2">Trier par prix</span>
          <a href="?sort=asc" class="inline-flex items-center gap-1 rounded-full border border-pink-200 bg-pink-50 px-3 py-1 hover:bg-pink-100 transition">🔼</a>
          <a href="?sort=desc" class="inline-flex items-center gap-1 rounded-full border border-pink-200 bg-pink-50 px-3 py-1 hover:bg-pink-100 transition">🔽</a>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-rose-50/60 text-gray-700">
            <tr>
              <th class="px-4 py-3 text-left">ID</th>
              <th class="px-4 py-3 text-left">Nom</th>
              <th class="px-4 py-3 text-left">Description</th>
              <th class="px-4 py-3 text-left">Prix</th>
              <th class="px-4 py-3 text-left">Image</th>
              <th class="px-4 py-3 text-left">Stock</th>
              <th class="px-4 py-3 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($produits) && is_iterable($produits)): ?>
              <?php foreach ($produits as $p): ?>
                <tr class="border-t hover:bg-pink-50/30">
                  <td class="px-4 py-3"><?= htmlspecialchars($p['id']) ?></td>
                  <td class="px-4 py-3 font-medium text-gray-900"><?= htmlspecialchars($p['nom']) ?></td>
                  <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($p['description']) ?></td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full bg-pink-100 px-2.5 py-0.5 text-pink-700 font-semibold">
                      <?= htmlspecialchars($p['prix']) ?> DT
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <?php if (!empty($p['image_url'])): ?>
                      <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="img"
                           class="h-12 w-12 object-cover rounded-xl ring-1 ring-pink-200/60">
                    <?php else: ?>
                      <span class="text-gray-400 italic">Aucune</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3">
                    <?php $low = (int)($p['stock'] ?? 0) < 10; ?>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs
                                 <?= $low ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' ?>">
                      <?= htmlspecialchars($p['stock']) ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-2">
                      <a href="indexProduits.php?id=<?= $p['id'] ?>"
                         class="px-3 py-1.5 rounded-xl bg-amber-400 text-white hover:bg-amber-500 transition shadow-sm">✏️ Modifier</a>
                      <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Confirmer la suppression ?');"
                         class="px-3 py-1.5 rounded-xl bg-rose-600 text-white hover:bg-rose-700 transition shadow-sm">🗑 Supprimer</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucun produit trouvé.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Footer mini -->
    <p class="mt-6 text-center text-xs text-gray-500">© <?= date('Y') ?> — Gestion des produits</p>
  </div>
</body>
</html>
