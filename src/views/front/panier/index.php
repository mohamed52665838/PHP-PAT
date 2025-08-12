<?php
declare(strict_types=1);
session_start();

require_once '../../../../src/controllers/panierController.php';

try {
  if (!isset($_SESSION['user'])) {
    header('Location: ../login/index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
  }

  $panierController = new PanierController();
  $panier    = $panierController->getPanier();
  $id_panier = (int)$panier[0]['id_panier'];
  $user_id   = (int)$_SESSION['user']['id'];
  $items     = $panierController->getProduitsPanier();
} catch (Throwable $e) {
  http_response_code(500);
  echo "Erreur: " . htmlspecialchars($e->getMessage());
  exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Mon panier — Pâtisserie Douceur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .brand{font-weight:800;background:linear-gradient(90deg,#f472b6,#f59e0b,#f472b6);-webkit-background-clip:text;background-clip:text;color:transparent}
  </style>
</head>
<body class="min-h-screen bg-pink-50 text-gray-800">

  <!-- Top bar -->
  <div class="sticky top-0 z-10 border-b border-pink-100 bg-white/90 backdrop-blur">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="brand text-xl">Pâtisserie Douceur</h1>
      <a href="../home/index.php" class="text-pink-600 hover:text-pink-700 text-sm font-semibold">← Retourner aux produits</a>
    </div>
  </div>

  <main class="container mx-auto px-4 py-6 max-w-4xl">
    <!-- Flash -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-700 px-3 py-2 text-sm">
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
      </div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
      </div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Header bloc -->
    <section class="mb-5 flex flex-wrap items-center gap-3 text-sm">
      <span class="inline-flex items-center gap-2 rounded-full bg-white border border-pink-100 px-3 py-1">👤 User: <strong><?= $user_id ?></strong></span>
      <span class="inline-flex items-center gap-2 rounded-full bg-white border border-pink-100 px-3 py-1">🧺 Panier #<strong><?= $id_panier ?></strong></span>
    </section>

    <!-- Liste compacte -->
    <section class="rounded-xl border border-pink-100 bg-white shadow-sm">
      <div class="px-4 py-3 border-b border-pink-100 flex items-center justify-between">
        <h2 class="font-bold text-pink-700">Mon panier</h2>
      </div>

      <?php
        $total_panier = 0.0;
        if (!empty($items)):
      ?>
      <ul class="divide-y divide-gray-100">
        <?php foreach ($items as $it):
          $nom = $it['produit_nom'] ?? ('Produit #' . (int)$it['produit_id']);
          $qte = (int)($it['quantite'] ?? 0);
          $pu  = (float)($it['prix_unitaire'] ?? 0);
          $tot = (float)($it['total_ligne'] ?? ($qte * $pu));
          $total_panier += $tot;
          $img = !empty($it['image_url']) ? "../produits/".ltrim($it['image_url'],'/') : null;
        ?>
        <li class="px-4 py-3">
          <div class="grid grid-cols-[64px_1fr_auto] gap-3 items-center">
            <!-- mini vignette -->
            <div class="h-16 w-16 rounded-lg bg-pink-50 overflow-hidden border border-pink-100">
              <?php if ($img): ?>
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($nom) ?>" class="h-full w-full object-cover">
              <?php else: ?>
                <div class="h-full w-full flex items-center justify-center text-xs text-pink-300">—</div>
              <?php endif; ?>
            </div>

            <!-- infos -->
            <div class="min-w-0">
              <div class="font-medium truncate"><?= htmlspecialchars($nom) ?></div>
              <div class="mt-0.5 text-xs text-gray-500">
                Qté: <strong><?= $qte ?></strong>
                <span class="mx-2">•</span>
                PU: <strong><?= number_format($pu, 2) ?> DT</strong>
              </div>
            </div>

            <!-- total ligne -->
            <div class="text-right">
              <div class="text-sm font-semibold text-pink-700"><?= number_format($tot, 2) ?> DT</div>
            </div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>

      <!-- Total + actions -->
      <div class="px-4 py-4 border-t border-pink-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <p class="text-lg font-extrabold text-pink-700">
          Total : <?= number_format($total_panier, 2) ?> DT
        </p>
      <div class="flex flex-wrap items-center gap-2">
  <a href="../produits/index.php"
     class="inline-flex items-center rounded-lg border border-pink-200 bg-white px-4 py-2 text-pink-600 text-sm font-semibold hover:bg-pink-50">
    ← Continuer mes achats
  </a>

  <!-- Bouton Historique -->
  <a href="../historique/order.php"
     class="inline-flex items-center rounded-lg border border-pink-200 bg-white px-4 py-2 text-pink-600 text-sm font-semibold hover:bg-pink-50">
    🧾 Voir mon historique
  </a>

<form method="post" action="../historique/order.php" class="inline-block">
  <button type="submit"
          class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-white text-sm font-semibold hover:bg-pink-700 shadow">
    Valider ma commande
  </button>
</form>

</div>

      </div>

      <?php else: ?>
        <!-- Vide -->
        <div class="px-4 py-12 text-center">
          <div class="text-3xl mb-2">🧁</div>
          <p class="text-gray-600">Votre panier est vide.</p>
          <a href="../historique/order.php"
             class="mt-4 inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-white text-sm font-semibold hover:bg-pink-700 shadow">
            voir mon historique →
          </a>
        </div>
      <?php endif; ?>
    </section>

    <!-- Lien bas de page -->
    <div class="mt-6 text-center">
      <a href="../home/index.php" class="text-pink-600 hover:text-pink-700 text-sm font-semibold">← Retourner aux produits</a>
    </div>
  </main>

  <footer class="mt-10 border-t border-pink-100 py-6">
    <p class="text-center text-xs text-gray-500">
      © <?= date('Y') ?> Pâtisserie Douceur — Tous droits réservés
    </p>
  </footer>
</body>
</html>
