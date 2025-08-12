<?php
declare(strict_types=1);
session_start();

require_once '../../../../src/controllers/panierController.php';

if (!isset($_SESSION['user'])) {
  header('Location: ../login/index.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

$pc = new PanierController();

try {
  // Si POST => on valide le panier actif
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $pc->finalizeActivePanier();
    $_SESSION['flash_success'] = "Commande #{$res['order_id']} validée (total " . number_format($res['total'], 2) . " DT).";
    // Après validation, on redirige en GET pour afficher l'historique
    header('Location: order.php');
    exit;
  }

  // GET => afficher l'historique
  $user_id = (int)$_SESSION['user']['id'];
  $orders  = $pc->getHistoriqueAvecLignes($user_id);

} catch (Throwable $e) {
  $_SESSION['flash_error'] = $e->getMessage();
  $orders = [];
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Historique des commandes — Pâtisserie Douceur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>.brand{font-weight:800;background:linear-gradient(90deg,#f472b6,#f59e0b,#f472b6);-webkit-background-clip:text;background-clip:text;color:transparent}</style>
</head>
<body class="min-h-screen bg-pink-50 text-gray-800">

  <div class="sticky top-0 z-10 border-b border-pink-100 bg-white/90 backdrop-blur">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="brand text-xl">Pâtisserie Douceur</h1>
      <div class="flex items-center gap-2">
        <a href="../home/index.php" class="text-pink-600 hover:text-pink-700 text-sm font-semibold">← Retourner aux produits</a>
      </div>
    </div>
  </div>

  <main class="container mx-auto px-4 py-6 max-w-5xl">

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

    <header class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-extrabold text-pink-700">Historique des commandes</h2>
        <p class="text-sm text-gray-600">Vos commandes validées (statut initial <em>“en attente”</em>).</p>
      </div>
      <!-- Bouton pour valider le panier directement depuis ici si besoin -->
      <form method="post" action="../historique/order.php">
        <button type="submit"
                class="inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-white text-sm font-semibold hover:bg-pink-700 shadow">
          Valider mon panier actuel
        </button>
      </form>
    </header>

    <?php if (empty($orders)): ?>
      <div class="rounded-xl border border-pink-100 bg-white p-8 text-center shadow-sm">
        <div class="text-4xl mb-2">🧁</div>
        <p class="text-gray-600">Aucune commande pour le moment.</p>
        <a href="../home/index.php"
           class="mt-4 inline-flex items-center rounded-lg bg-pink-600 px-4 py-2 text-white text-sm font-semibold hover:bg-pink-700 shadow">
          Voir les produits →
        </a>
      </div>
    <?php else: ?>

      <div class="rounded-xl border border-pink-100 bg-white shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-pink-100 flex items-center justify-between">
          <h3 class="font-bold text-pink-700">Vos commandes</h3>
          <span class="text-xs text-gray-500"><?= count($orders) ?> commande(s)</span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-pink-50 text-gray-700">
              <tr>
                <th class="text-left px-4 py-2">Commande</th>
                <th class="text-left px-4 py-2">Date</th>
                <th class="text-left px-4 py-2">Statut</th>
                <th class="text-right px-4 py-2">Total</th>
                <th class="text-right px-4 py-2">Articles</th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php
                // Petite fonction locale pour badge
                $statusClass = function(string $s): string {
                  return match($s) {
                    'en attente' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'en cours'   => 'bg-blue-50 text-blue-700 border-blue-200',
                    'livrée'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'annulée'    => 'bg-rose-50 text-rose-700 border-rose-200',
                    default      => 'bg-gray-50 text-gray-700 border-gray-200',
                  };
                };
              ?>
              <?php foreach ($orders as $order):
                $badge = $statusClass($order['status']);
                $rowId = 'order-'.$order['id'];
                $nbItems = count($order['lignes'] ?? []);
              ?>
              <tr class="hover:bg-pink-50 cursor-pointer order-row" data-target="<?= $rowId ?>">
                <td class="px-4 py-3 font-medium">#<?= (int)$order['id'] ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($order['created_at']))) ?></td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[12px] font-medium <?= $badge ?>">
                    <?= htmlspecialchars($order['status']) ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-pink-700">
                  <?= number_format((float)$order['total'], 2) ?> DT
                </td>
                <td class="px-4 py-3 text-right text-gray-600"><?= (int)$nbItems ?></td>
                <td class="px-4 py-3 text-right">
                  <svg class="chev inline h-4 w-4 text-gray-500 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                  </svg>
                </td>
              </tr>

              <tr id="<?= $rowId ?>" class="hidden bg-white">
                <td colspan="6" class="px-4 py-4">
                  <?php if ($nbItems): ?>
                    <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                      <?php foreach ($order['lignes'] as $l):
                        $img = !empty($l['image_url']) ? '../produits/' . ltrim($l['image_url'], '/') : null;
                      ?>
                        <li class="rounded-lg border border-pink-100 bg-pink-50/40 p-3">
                          <div class="flex items-center gap-3">
                            <div class="h-14 w-14 rounded-md overflow-hidden bg-pink-50 border border-pink-100">
                              <?php if ($img): ?>
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($l['nom']) ?>" class="h-full w-full object-cover">
                              <?php else: ?>
                                <div class="h-full w-full flex items-center justify-center text-xs text-pink-300">—</div>
                              <?php endif; ?>
                            </div>
                            <div class="min-w-0">
                              <div class="font-medium truncate"><?= htmlspecialchars($l['nom']) ?></div>
                              <div class="text-xs text-gray-500">
                                Qté: <strong><?= (int)$l['qte'] ?></strong>
                                <span class="mx-1">•</span>
                                PU: <strong><?= number_format((float)$l['prix'], 2) ?> DT</strong>
                              </div>
                              <div class="text-xs font-semibold text-pink-700">
                                Total ligne: <?= number_format((float)$l['total_ligne'], 2) ?> DT
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <p class="text-sm text-gray-500">Aucun article dans cette commande.</p>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <?php endif; ?>
  </main>

  <footer class="mt-10 border-t border-pink-100 py-6">
    <p class="text-center text-xs text-gray-500">
      © <?= date('Y') ?> Pâtisserie Douceur — Tous droits réservés
    </p>
  </footer>

  <script>
    document.querySelectorAll('.order-row').forEach(function(row){
      row.addEventListener('click', function(){
        const id = row.dataset.target;
        const details = document.getElementById(id);
        const chev = row.querySelector('.chev');
        if (!details) return;
        const isHidden = details.classList.contains('hidden');
        document.querySelectorAll('tr[id^="order-"]').forEach(tr => tr.classList.add('hidden'));
        document.querySelectorAll('.order-row .chev').forEach(c => c.style.transform = 'rotate(0deg)');
        if (isHidden) {
          details.classList.remove('hidden');
          if (chev) chev.style.transform = 'rotate(180deg)';
        }
      });
    });
  </script>
</body>
</html>
