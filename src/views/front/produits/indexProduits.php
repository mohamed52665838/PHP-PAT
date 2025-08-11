<?php
require_once '../../../../src/controllers/produitsController.php';
require_once '../../../../src/models/Produit.php';

$controller = new ProduitsController();

$fieldErrors = [];       // erreurs par champ
$produitAModifier = null; // données affichées dans le formulaire (edit ou post en erreur)

/* ---------- Utilitaires ---------- */
function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/**
 * Valide et nettoie les données. Retourne [ $errors, $data ]
 */
function validateProduit(array $post, array $files, bool $isUpdate = false): array {
    $errors = [];
    $data = [
        'id'           => isset($post['id']) ? (int)$post['id'] : null,
        'nom'          => trim($post['nom'] ?? ''),
        'description'  => trim($post['description'] ?? ''),
        'prix'         => $post['prix'] ?? '',
        'stock'        => $post['stock'] ?? '',
        'image_url'    => $post['existing_image'] ?? '', // conserver ancienne image si non remplacée
    ];

    // Nom
    if ($data['nom'] === '' || mb_strlen($data['nom']) < 2) {
        $errors['nom'] = "Le nom est obligatoire (min. 2 caractères).";
    }

    // Description
    if ($data['description'] === '' || mb_strlen($data['description']) < 3) {
        $errors['description'] = "La description est obligatoire (min. 3 caractères).";
    }

    // Prix
    if ($data['prix'] === '' || !is_numeric($data['prix']) || (float)$data['prix'] < 0) {
        $errors['prix'] = "Le prix doit être un nombre ≥ 0.";
    } else {
        $data['prix'] = (float)$data['prix'];
    }

    // Stock (entier >= 0)
    if ($data['stock'] === '' || !is_numeric($data['stock']) || (int)$data['stock'] < 0 || floor($data['stock']) != $data['stock']) {
        $errors['stock'] = "Le stock doit être un entier ≥ 0.";
    } else {
        $data['stock'] = (int)$data['stock'];
    }

    // Image (optionnelle)
    if (!empty($files['image']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $tmpPath = $files['image']['tmp_name'];

        // Sécuriser la détection MIME
        $mime = function_exists('finfo_open')
            ? (function() use ($tmpPath) {
                $f = finfo_open(FILEINFO_MIME_TYPE);
                $m = finfo_file($f, $tmpPath);
                finfo_close($f);
                return $m;
              })()
            : ($files['image']['type'] ?? '');

        if (!in_array($mime, $allowed, true)) {
            $errors['image'] = "Format d’image non supporté (jpeg, png, gif, webp).";
        } elseif ((int)$files['image']['size'] > 2 * 1024 * 1024) {
            $errors['image'] = "L’image ne doit pas dépasser 2 Mo.";
        } else {
            // Upload
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $safeName  = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($files['image']['name']));
            $fileName  = time().'_'.$safeName;
            $target    = $uploadDir.$fileName;

            if (move_uploaded_file($files['image']['tmp_name'], $target)) {
                $data['image_url'] = 'uploads/'.$fileName;
            } else {
                $errors['image'] = "Échec de l’upload de l’image.";
            }
        }
    }

    return [$errors, $data];
}

/* ---------- Pré-chargement si édition ---------- */
if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    foreach ($controller->getProduits() as $p) {
        if ((int)$p['id'] === $id) { $produitAModifier = $p; break; }
    }
}

/* ---------- Traitement POST (add / update) ---------- */
if (!empty($_POST['action'])) {
    $isUpdate = ($_POST['action'] === 'update');
    [$fieldErrors, $data] = validateProduit($_POST, $_FILES, $isUpdate);

    if (empty($fieldErrors)) {
        $produit = new Produit($data['nom'], $data['description'], $data['prix'], $data['image_url'], $data['stock']);
        if ($isUpdate) {
            $controller->update($produit, (int)$data['id']);
        } else {
            $controller->add($produit);
        }
        header('Location: getProduits.php');
        exit;
    } else {
        // Réinjecter les valeurs saisies dans le formulaire
        $produitAModifier = $data;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Gestion Produits</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-rose-100 p-8">
  <div class="max-w-3xl mx-auto rounded-2xl border border-white/70 bg-white/90 backdrop-blur shadow-xl p-8 relative overflow-hidden">
    <div class="absolute -top-16 -left-16 w-48 h-48 bg-pink-200/40 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-rose-200/50 rounded-full blur-3xl"></div>

    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-extrabold text-pink-700">
        <?= !empty($produitAModifier['id']) ? '✏️ Modifier un produit' : '➕ Ajouter un produit' ?>
      </h1>
      <a href="../../back/dashboard/index.php"
         class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-pink-700 font-medium shadow hover:bg-pink-50 transition">
        🏠 Retour Admin
      </a>
    </div>

    <!-- Affichage des erreurs globales par champ -->
    <?php if (!empty($fieldErrors)): ?>
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
        <ul class="list-disc pl-5">
          <?php foreach ($fieldErrors as $msg): ?>
            <li><?= h($msg) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-6" novalidate>
      <input type="hidden" name="action" value="<?= !empty($produitAModifier['id']) ? 'update' : 'add' ?>">
      <?php if (!empty($produitAModifier['id'])): ?>
        <input type="hidden" name="id" value="<?= (int)$produitAModifier['id'] ?>">
      <?php endif; ?>

      <!-- Nom -->
      <div>
        <label class="block text-sm font-medium text-pink-700">Nom</label>
        <input type="text" name="nom" value="<?= h($produitAModifier['nom'] ?? '') ?>"
               class="mt-1 block w-full rounded-xl border <?= isset($fieldErrors['nom']) ? 'border-rose-500' : 'border-pink-200' ?> px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-300">
        <?php if (isset($fieldErrors['nom'])): ?>
          <div class="text-xs text-rose-600 mt-1"><?= h($fieldErrors['nom']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div>
        <label class="block text-sm font-medium text-pink-700">Description</label>
        <input type="text" name="description" value="<?= h($produitAModifier['description'] ?? '') ?>"
               class="mt-1 block w-full rounded-xl border <?= isset($fieldErrors['description']) ? 'border-rose-500' : 'border-pink-200' ?> px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-300">
        <?php if (isset($fieldErrors['description'])): ?>
          <div class="text-xs text-rose-600 mt-1"><?= h($fieldErrors['description']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Prix -->
      <div>
        <label class="block text-sm font-medium text-pink-700">Prix</label>
        <input type="text" name="prix" placeholder="Ex: 5.50" value="<?= h($produitAModifier['prix'] ?? '') ?>"
               class="mt-1 block w-full rounded-xl border <?= isset($fieldErrors['prix']) ? 'border-rose-500' : 'border-pink-200' ?> px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-300">
        <?php if (isset($fieldErrors['prix'])): ?>
          <div class="text-xs text-rose-600 mt-1"><?= h($fieldErrors['prix']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Image -->
      <div>
        <label class="block text-sm font-medium text-pink-700">Image (jpeg/png/gif/webp, max 2Mo)</label>
        <input type="file" name="image"
               class="mt-1 block w-full rounded-xl border <?= isset($fieldErrors['image']) ? 'border-rose-500' : 'border-pink-200' ?> px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-300">
        <?php if (!empty($produitAModifier['image_url'])): ?>
          <input type="hidden" name="existing_image" value="<?= h($produitAModifier['image_url']) ?>">
          <img src="<?= h($produitAModifier['image_url']) ?>" alt="Aperçu" class="h-20 mt-3 rounded-xl border border-pink-200 shadow-sm">
        <?php endif; ?>
        <?php if (isset($fieldErrors['image'])): ?>
          <div class="text-xs text-rose-600 mt-1"><?= h($fieldErrors['image']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Stock -->
      <div>
        <label class="block text-sm font-medium text-pink-700">Stock</label>
        <input type="text" name="stock" placeholder="Ex: 10" value="<?= h($produitAModifier['stock'] ?? '') ?>"
               class="mt-1 block w-full rounded-xl border <?= isset($fieldErrors['stock']) ? 'border-rose-500' : 'border-pink-200' ?> px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-300">
        <?php if (isset($fieldErrors['stock'])): ?>
          <div class="text-xs text-rose-600 mt-1"><?= h($fieldErrors['stock']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Boutons -->
      <div class="flex flex-wrap gap-3">
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-pink-600 text-white px-5 py-2.5 shadow hover:bg-pink-700 transition">
          <?= !empty($produitAModifier['id']) ? '💾 Mettre à jour' : '➕ Ajouter' ?>
        </button>
        <a href="getProduits.php"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 hover:bg-gray-50 transition">
          ↩ Annuler
        </a>
      </div>
    </form>
  </div>
</body>
</html>
