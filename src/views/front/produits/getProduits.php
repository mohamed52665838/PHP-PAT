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
        // tableau indexé : liste de produits
        $produits = $res;
    } else {
        // produit unique (assoc)
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
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen p-6">
  <div class="max-w-6xl mx-auto">

    <?php if (!empty($message)): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-800"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Search Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h1 class="text-2xl font-semibold mb-4">Recherche produits</h1>
      <form method="get" class="flex flex-wrap gap-4 items-end">
        <div>
          <label class="block text-sm font-medium mb-1">ID</label>
          <input type="number" name="search_id" value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>"
                 class="border rounded px-3 py-2 w-40 focus:outline-none focus:ring" />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Nom</label>
          <input type="text" name="search_name" placeholder="Tapez un nom..."
                 value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>"
                 oninput="this.form.submit()"
                 class="border rounded px-3 py-2 w-64 focus:outline-none focus:ring" />
        </div>

        <div class="flex gap-2">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🔍 Rechercher</button>
          <a href="getProduits.php" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Afficher tous</a>
        </div>
      </form>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-xl font-semibold">Liste des produits</h2>
      <a href="./indexProduits.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">➕ Ajouter</a>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left">ID</th>
            <th class="px-4 py-3 text-left">Nom</th>
            <th class="px-4 py-3 text-left">Description</th>
            <th class="px-4 py-3 text-left">Prix
              <a href="?sort=asc" class="ml-2">🔼</a>
              <a href="?sort=desc" class="ml-1">🔽</a>
            </th>
            <th class="px-4 py-3 text-left">Image</th>
            <th class="px-4 py-3 text-left">Stock</th>
            <th class="px-4 py-3 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($produits) && is_iterable($produits)): ?>
            <?php foreach ($produits as $p): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3"><?= htmlspecialchars($p['id']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($p['nom']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($p['description']) ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars($p['prix']) ?> DT</td>
                <td class="px-4 py-3">
                  <?php if (!empty($p['image_url'])): ?>
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="img" class="h-12 w-12 object-cover rounded">
                  <?php else: ?>
                    <span class="text-gray-400 italic">Aucune</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3"><?= htmlspecialchars($p['stock']) ?></td>
                <td class="px-4 py-3 flex gap-2">
                  <a href="indexProduits.php?id=<?= $p['id'] ?>" class="px-3 py-1 bg-yellow-400 rounded text-white hover:bg-yellow-500">✏️ Modifier</a>
                  <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Confirmer la suppression ?');" class="px-3 py-1 bg-red-600 rounded text-white hover:bg-red-700">🗑 Supprimer</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-gray-500">Aucun produit trouvé.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</body>
</html>
