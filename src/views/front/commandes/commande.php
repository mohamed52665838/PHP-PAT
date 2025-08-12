<?php
declare(strict_types=1);
session_start();

require_once '../../../../src/controllers/panierController.php';

if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['role'] ?? '')) !== 'preparateur') {
  http_response_code(403);
  die('Accès refusé');
}

$pc  = new PanierController();
$msg = '';

// Actions POST (sans CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action  = (string)($_POST['action'] ?? '');
  $orderId = (int)($_POST['order_id'] ?? 0);

  try {
    if ($action === 'set_status') {
      $newStatus = (string)($_POST['new_status'] ?? '');
      $pc->changerStatus($orderId, $newStatus);
      $msg = "Commande #{$orderId} mise à jour en « {$newStatus} ».";
    } elseif ($action === 'delete_pending') {
      $pc->supprimerCommandeSiEnAttente($orderId);
      $msg = "Commande #{$orderId} supprimée.";
    }
  } catch (Throwable $e) {
    $msg = "Erreur: " . $e->getMessage();
  }
}

// Récupérer toutes les commandes (paniers fermés)
$orders = $pc->getAllOrders();

// Status -> classes badge (PHP 7 friendly)
$statusClass = function(string $s): string {
  if ($s === 'en attente') return 'bg-amber-50 text-amber-700 border-amber-200';
  if ($s === 'en cours')   return 'bg-blue-50 text-blue-700 border-blue-200';
  if ($s === 'livrée')     return 'bg-emerald-50 text-emerald-700 border-emerald-200';
  if ($s === 'annulée')    return 'bg-rose-50 text-rose-700 border-rose-200';
  return 'bg-gray-50 text-gray-700 border-gray-200';
};

// Base des images produits (adapte si besoin)
$imgBase = '../produits/'; // ex: ../produits/monimage.jpg
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Commandes — Préparateur</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>.brand{font-weight:800;background:linear-gradient(90deg,#10b981,#34d399,#10b981);-webkit-background-clip:text;background-clip:text;color:transparent}</style>
</head>
<body class="min-h-screen bg-emerald-50 text-gray-800">

  <!-- Topbar -->
  <div class="sticky top-0 z-10 border-b border-emerald-100 bg-white/90 backdrop-blur">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="brand text-xl">Espace Préparateur</h1>
      <a href="../home/index.php" class="text-emerald-600 hover:text-emerald-700 text-sm font-semibold">← Retour</a>
    </div>
  </div>

  <main class="container mx-auto px-4 py-6 max-w-5xl">

    <?php if (!empty($msg)): ?>
      <div class="mb-4 rounded-lg <?= strpos($msg,'Erreur:')===0 ? 'bg-rose-50 border border-rose-200 text-rose-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700' ?> px-3 py-2 text-sm">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <header class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-extrabold text-emerald-700">Commandes clients</h2>
        <p class="text-sm text-gray-600">Modifier le statut ou annuler une commande lorsqu’elle est <em>en attente</em>.</p>
      </div>
    </header>

    <?php if (empty($orders)): ?>
      <div class="rounded-xl border border-emerald-100 bg-white p-8 text-center shadow-sm">
        <div class="text-4xl mb-2">📦</div>
        <p class="text-gray-600">Aucune commande à afficher.</p>
      </div>
    <?php else: ?>

      <div class="rounded-xl border border-emerald-100 bg-white shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-emerald-100 flex items-center justify-between">
          <h3 class="font-bold text-emerald-700">Toutes les commandes</h3>
          <span class="text-xs text-gray-500"><?= count($orders) ?> commande(s)</span>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-emerald-50 text-gray-700">
              <tr>
                <th class="text-left px-4 py-2">Commande</th>
                <th class="text-left px-4 py-2">Client</th>
                <th class="text-left px-4 py-2">Date</th>
                <th class="text-left px-4 py-2">Statut</th>
                <th class="text-right px-4 py-2">Total</th>
                <th class="px-4 py-2">Aperçu</th>
                <th class="text-right px-4 py-2">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php foreach ($orders as $o):
                $rowId = 'order-'.$o['id'];
                $badge = $statusClass($o['status']);

                // mini-aperçu (3 premières lignes)
                $lignes = $pc->getOrderLines((int)$o['id']);
                $thumbs = array_slice($lignes, 0, 3);
                $more   = max(0, count($lignes) - count($thumbs));
              ?>
              <tr class="hover:bg-emerald-50 cursor-pointer order-row" data-target="<?= $rowId ?>">
                <td class="px-4 py-3 font-medium">#<?= (int)$o['id'] ?></td>
                <td class="px-4 py-3">User #<?= (int)$o['user_id'] ?></td>
                <td class="px-4 py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[12px] font-medium <?= $badge ?>">
                    <?= htmlspecialchars($o['status']) ?>
                  </span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                  <?= number_format((float)$o['total'], 2) ?> DT
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1">
                    <?php foreach ($thumbs as $t):
                      $src = !empty($t['image_url']) ? $imgBase . ltrim($t['image_url'], '/') : null; ?>
                      <div class="h-10 w-10 rounded-md overflow-hidden border border-emerald-100 bg-emerald-50">
                        <?php if ($src): ?>
                          <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($t['nom']) ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                          <div class="h-full w-full grid place-items-center text-[10px] text-emerald-300">—</div>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                    <?php if ($more > 0): ?>
                      <div class="h-10 w-10 rounded-md border border-emerald-200 bg-white text-[11px] grid place-items-center text-emerald-700">+<?= $more ?></div>
                    <?php endif; ?>
                  </div>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <?php if ($o['status'] === 'en attente'): ?>
                      <!-- Passer en cours -->
                      <form method="post">
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <input type="hidden" name="new_status" value="en cours">
                        <button type="submit" class="rounded-lg bg-blue-600 px-3 py-1.5 text-white text-xs font-semibold hover:bg-blue-700">
                          Passer « en cours »
                        </button>
                      </form>
                      <!-- Annuler -->
                      <form method="post" onsubmit="return confirm('Supprimer la commande #<?= (int)$o['id'] ?> ?');">
                        <input type="hidden" name="action" value="delete_pending">
                        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-white text-xs font-semibold hover:bg-rose-700">
                          Annuler
                        </button>
                      </form>
                    <?php elseif ($o['status'] === 'en cours'): ?>
                      <!-- Marquer livrée -->
                      <form method="post">
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                        <input type="hidden" name="new_status" value="livrée">
                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-white text-xs font-semibold hover:bg-emerald-700">
                          Marquer « livrée »
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-gray-400 text-xs">—</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>

              <!-- Détails produits (toggle) -->
              <tr id="<?= $rowId ?>" class="hidden bg-white">
                <td colspan="7" class="px-4 py-4">
                  <?php if (empty($lignes)): ?>
                    <p class="text-sm text-gray-500">Aucun article.</p>
                  <?php else: ?>
                    <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                      <?php foreach ($lignes as $l):
                        $img = !empty($l['image_url']) ? $imgBase . ltrim($l['image_url'], '/') : null; ?>
                        <li class="rounded-lg border border-emerald-100 bg-emerald-50/40 p-3">
                          <div class="flex items-center gap-3">
                            <div class="h-14 w-14 rounded-md overflow-hidden bg-emerald-50 border border-emerald-100">
                              <?php if ($img): ?>
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($l['nom']) ?>" class="h-full w-full object-cover">
                              <?php else: ?>
                                <div class="h-full w-full grid place-items-center text-xs text-emerald-300">—</div>
                              <?php endif; ?>
                            </div>
                            <div class="min-w-0">
                              <div class="font-medium truncate"><?= htmlspecialchars($l['nom']) ?></div>
                              <div class="text-xs text-gray-500">
                                Qté: <strong><?= (int)$l['qte'] ?></strong>
                                <span class="mx-1">•</span>
                                PU: <strong><?= number_format((float)$l['prix'], 2) ?> DT</strong>
                              </div>
                              <div class="text-xs font-semibold text-emerald-700">
                                Total ligne: <?= number_format((float)$l['total_ligne'], 2) ?> DT
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
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

  <footer class="mt-10 border-t border-emerald-100 py-6">
    <p class="text-center text-xs text-gray-500">
      © <?= date('Y') ?> Pâtisserie Douceur — Préparateur
    </p>
  </footer>

  <script>
    // Toggle lignes de détails (comme l’historique)
    document.querySelectorAll('.order-row').forEach(function(row){
      row.addEventListener('click', function(e){
        // éviter de déclencher quand on clique sur un bouton
        if (e.target.closest('form')) return;
        const id = row.dataset.target;
        const details = document.getElementById(id);
        if (!details) return;
        const isHidden = details.classList.contains('hidden');
        document.querySelectorAll('tr[id^="order-"]').forEach(tr => tr.classList.add('hidden'));
        if (isHidden) details.classList.remove('hidden');
      });
    });
  </script>
</body>
</html>
