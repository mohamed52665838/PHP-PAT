<?php
require_once '../../../../src/controllers/produitsController.php';
require_once '../../../../src/models/Produit.php';

$controller = new ProduitsController();
$message = '';
$produitAModifier = null;

// === Add Product ===
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $imagePath = '';
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/' . $fileName; // stored relative path
        }
    }

    $produit = new Produit(
        $_POST['nom'] ?? '',
        $_POST['description'] ?? '',
        floatval($_POST['prix'] ?? 0),
        $imagePath,
        intval($_POST['stock'] ?? 0)
    );
    $controller->add($produit);
    header('Location: getProduits.php');
    exit;
}

// === Edit form load ===
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $produits = $controller->getProduits();
    foreach ($produits as $p) {
        if ($p['id'] === $id) {
            $produitAModifier = $p;
            break;
        }
    }
}

// === Update Product ===
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $imagePath = $_POST['existing_image'] ?? '';

    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/' . $fileName;
        }
    }

    $produit = new Produit(
        $_POST['nom'] ?? '',
        $_POST['description'] ?? '',
        floatval($_POST['prix'] ?? 0),
        $imagePath,
        intval($_POST['stock'] ?? 0)
    );
    $controller->update($produit, $id);
    header('Location: getProduits.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Produits</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6"><?= $produitAModifier ? 'Modifier un produit' : 'Ajouter un produit' ?></h1>

        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="<?= $produitAModifier ? 'update' : 'add' ?>">
            <?php if ($produitAModifier): ?>
                <input type="hidden" name="id" value="<?= $produitAModifier['id'] ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium">Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($produitAModifier['nom'] ?? '') ?>" 
                       class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Description</label>
                <input type="text" name="description" value="<?= htmlspecialchars($produitAModifier['description'] ?? '') ?>" 
                       class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Prix</label>
                <input type="number" step="0.01" name="prix" value="<?= htmlspecialchars($produitAModifier['prix'] ?? '') ?>" 
                       class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium">Image</label>
                <input type="file" name="image" class="mt-1 block w-full border rounded px-3 py-2">
                <?php if (!empty($produitAModifier['image_url'])): ?>
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($produitAModifier['image_url']) ?>">
                    <img src="<?= htmlspecialchars($produitAModifier['image_url']) ?>" alt="Aperçu" class="h-20 mt-2 rounded">
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium">Stock</label>
                <input type="number" name="stock" value="<?= htmlspecialchars($produitAModifier['stock'] ?? '') ?>" 
                       class="mt-1 block w-full border rounded px-3 py-2" required>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <?= $produitAModifier ? 'Mettre à jour' : 'Ajouter' ?>
                </button>
                <a href="getProduits.php" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>
