<?php
declare(strict_types=1);
session_start();

require_once  '../../../../src/controllers/panierController.php';

$isAuth = isset($_SESSION['user']) && is_array($_SESSION['user']);
$role   = strtolower($_SESSION['user']['role'] ?? '');
if (!$isAuth || $role !== 'admin') {
  header('Location: ../..front/home/index.php?error=forbidden');
  exit;
}

$pc  = new PanierController();
$msg = '';

/* ====== Actions POST Admin ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');
  try {
    switch ($action) {
      case 'update_line_qty':
        $ligne_id = (int)($_POST['ligne_id'] ?? 0);
        $qte      = (int)($_POST['qte'] ?? 1);
        $pc->adminUpdateCartLineQty($ligne_id, $qte);
        $msg = "Quantité mise à jour.";
        break;

      case 'delete_line':
        $ligne_id = (int)($_POST['ligne_id'] ?? 0);
        $pc->adminDeleteCartLine($ligne_id);
        $msg = "Ligne supprimée.";
        break;

      case 'checkout_cart':
        $panier_id = (int)($_POST['panier_id'] ?? 0);
        $oid = $pc->adminCheckoutCart($panier_id);
        $msg = "Panier #$panier_id validé. (Commande créée)";
        break;

      case 'set_status':
        $order_id  = (int)($_POST['order_id'] ?? 0);
        $newStatus = (string)($_POST['new_status'] ?? '');
        $pc->changerStatus($order_id, $newStatus);
        $msg = "Commande #$order_id → « $newStatus »";
        break;

      case 'delete_pending':
        $order_id = (int)($_POST['order_id'] ?? 0);
        $pc->supprimerCommandeSiEnAttente($order_id);
        $msg = "Commande #$order_id supprimée.";
        break;
    }
  } catch (Throwable $e) {
    $msg = "Erreur: " . $e->getMessage();
  }
}

/* ====== Données ====== */
$activeCarts = $pc->adminGetAllActiveCarts();
$orders      = $pc->getAllOrders();

// Pour afficher les lignes d’un panier (accordion)
function linesOf(PanierController $pc, int $panier_id): array {
  try { return $pc->adminGetActiveCartLines($panier_id); } catch(Throwable $e) { return []; }
}
$statusClass = function(string $s): string {
  if ($s === 'en attente') return 'bg-amber-50 text-amber-700 border-amber-200';
  if ($s === 'en cours')   return 'bg-blue-50 text-blue-700 border-blue-200';
  if ($s === 'livrée')     return 'bg-emerald-50 text-emerald-700 border-emerald-200';
  if ($s === 'annulée')    return 'bg-rose-50 text-rose-700 border-rose-200';
  return 'bg-gray-50 text-gray-700 border-gray-200';
};
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Admin — Paniers & Commandes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-rose-50 text-gray-800">

  <div class="sticky top-0 z-10 border-b border-rose-100 bg-white/90 backdrop-blur">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
      <h1 class="text-xl font-extrabold text-pink-600">Administration — Paniers & Commandes</h1>
      <a href="../dashboard/index.php" class="text-pink-600 hover:text-pink-700 text-sm font-semibold">← Tableau de bord</a>
    </div>
  </div>

  <main class="container mx-auto px-4 py-6 max-w-6xl">
    <?php if (!empty($msg)): ?>
      <div class="mb-4 rounded-lg <?= strpos($msg,'Erreur:')===0 ? 'bg-rose-50 border border-rose-200 text-rose-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700' ?> px-3 py-2 text-sm">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <!-- Onglets -->
    <div class="mb-4 flex gap-2">
      <button data-tab="carts" class="tab px-4 py-2 rounded-lg bg-pink-600 text-white">Paniers actifs</button>
      <button data-tab="orders" class="tab px-4 py-2 rounded-lg bg-white border">Commandes</button>
    </div>

    <!-- PANIER ACTIFS -->
    <section id="tab-carts" class="rounded-xl border bg-white shadow-sm overflow-hidden">
      <div class="px-4 py-3 border-b bg-pink-50 flex items-center justify-between">
        <h3 class="font-bold text-pink-700">Tous les paniers actifs</h3>
        <span class="text-xs text-gray-500"><?= count($activeCarts) ?> panier(s)</span>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-rose-50 text-gray-700">
            <tr>
              <th class="text-left px-4 py-2">Panier</th>
              <th class="text-left px-4 py-2">Utilisateur</th>
              <th class="text-left px-4 py-2">Créé le</th>
              <th class="text-right px-4 py-2">Qté</th>
              <th class="text-right px-4 py-2">Total estimé</th>
              <th class="px-4 py-2">Lignes</th>
              <th class="text-right px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          <?php if (empty($activeCarts)): ?>
            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Aucun panier actif.</td></tr>
          <?php else: foreach ($activeCarts as $c):
            $pid = (int)$c['panier_id'];
            $items = linesOf($pc, $pid);
          ?>
            <tr class="align-top">
              <td class="px-4 py-3 font-medium">#<?= $pid ?></td>
<td class="px-4 py-3">
  <?php
    $prenom = trim($c['prenom'] ?? '');
    $nom    = trim($c['nom'] ?? '');
    $email  = trim($c['email'] ?? '');
    $fullName = trim($prenom . ' ' . $nom);
  ?>
  <?= htmlspecialchars($fullName !== '' ? $fullName : '—') ?>
  <?php if ($email !== ''): ?>
    <span class="text-xs text-gray-500">(<?= htmlspecialchars($email) ?>)</span>
  <?php endif; ?>
</td>
              <td class="px-4 py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($c['created_at']))) ?></td>
              <td class="px-4 py-3 text-right"><?= (int)$c['total_qte'] ?></td>
              <td class="px-4 py-3 text-right font-semibold text-pink-700"><?= number_format((float)$c['total_estime'], 2) ?> DT</td>
              <td class="px-4 py-3">
                <?php if (empty($items)): ?>
                  <span class="text-xs text-gray-500">Aucune ligne</span>
                <?php else: ?>
                  <ul class="space-y-2">
                    <?php foreach ($items as $it): ?>
                      <li class="rounded border bg-rose-50/40 p-2">
                        <div class="flex items-center justify-between gap-3">
                          <div class="min-w-0">
                            <div class="font-medium truncate"><?= htmlspecialchars($it['nom']) ?></div>
                            <div class="text-xs text-gray-600">PU: <?= number_format((float)$it['prix'], 2) ?> • Stock: <?= is_null($it['stock']) ? '∞' : (int)$it['stock'] ?></div>
                            <div class="text-xs font-semibold text-pink-700">Total ligne: <?= number_format((float)$it['total_ligne'], 2) ?> DT</div>
                          </div>
                          <div class="flex items-center gap-2">
                            <!-- Update qty -->
                            <form method="post" class="flex items-center gap-1">
                              <input type="hidden" name="action" value="update_line_qty">
                              <input type="hidden" name="ligne_id" value="<?= (int)$it['ligne_id'] ?>">
                              <input type="number" name="qte" min="1" value="<?= (int)$it['qte'] ?>" class="w-16 border rounded px-2 py-1">
                              <button class="rounded bg-pink-600 text-white text-xs px-2 py-1 hover:bg-pink-700">MAJ</button>
                            </form>
                            <!-- Delete line -->
                            <form method="post" onsubmit="return confirm('Supprimer cette ligne ?');">
                              <input type="hidden" name="action" value="delete_line">
                              <input type="hidden" name="ligne_id" value="<?= (int)$it['ligne_id'] ?>">
                              <button class="rounded border text-xs px-2 py-1 hover:bg-gray-50">Suppr.</button>
                            </form>
                          </div>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-right">
                <?php if (!empty($items)): ?>
                  <form method="post" onsubmit="return confirm('Valider le panier #<?= $pid ?> ?');" class="inline">
                    <input type="hidden" name="action" value="checkout_cart">
                    <input type="hidden" name="panier_id" value="<?= $pid ?>">
                    <button class="rounded-lg bg-pink-600 text-white text-xs px-3 py-1.5 hover:bg-pink-700">Valider le panier</button>
                  </form>
                <?php else: ?>
                  <span class="text-xs text-gray-400">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- COMMANDES -->
    <section id="tab-orders" class="mt-6 rounded-xl border bg-white shadow-sm overflow-hidden hidden">
      <div class="px-4 py-3 border-b bg-rose-50 flex items-center justify-between">
        <h3 class="font-bold text-rose-700">Toutes les commandes</h3>
        <span class="text-xs text-gray-500"><?= count($orders) ?> commande(s)</span>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-pink-50 text-gray-700">
          <tr>
            <th class="text-left px-4 py-2">Commande</th>
            <th class="text-left px-4 py-2">Client</th>
            <th class="text-left px-4 py-2">Date</th>
            <th class="text-left px-4 py-2">Statut</th>
            <th class="text-right px-4 py-2">Total</th>
            <th class="text-right px-4 py-2">Actions</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
          <?php if (empty($orders)): ?>
            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Aucune commande.</td></tr>
          <?php else: foreach ($orders as $o): ?>
            <tr>
              <td class="px-4 py-3 font-medium">#<?= (int)$o['id'] ?></td>
              <td class="px-4 py-3">User #<?= (int)$o['user_id'] ?></td>
              <td class="px-4 py-3"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($o['created_at']))) ?></td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[12px] font-medium <?= $statusClass($o['status']) ?>">
                  <?= htmlspecialchars($o['status']) ?>
                </span>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-pink-700"><?= number_format((float)$o['total'], 2) ?> DT</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <?php if ($o['status'] === 'en attente'): ?>
                    <form method="post">
                      <input type="hidden" name="action" value="set_status">
                      <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                      <input type="hidden" name="new_status" value="en cours">
                      <button class="rounded bg-blue-600 text-white text-xs px-3 py-1.5 hover:bg-blue-700">En cours</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Supprimer la commande #<?= (int)$o['id'] ?> ?');">
                      <input type="hidden" name="action" value="delete_pending">
                      <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                      <button class="rounded bg-rose-600 text-white text-xs px-3 py-1.5 hover:bg-rose-700">Supprimer</button>
                    </form>
                  <?php elseif ($o['status'] === 'en cours'): ?>
                    <form method="post">
                      <input type="hidden" name="action" value="set_status">
                      <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                      <input type="hidden" name="new_status" value="livrée">
                      <button class="rounded bg-emerald-600 text-white text-xs px-3 py-1.5 hover:bg-emerald-700">Livrée</button>
                    </form>
                  <?php else: ?>
                    <span class="text-xs text-gray-400">—</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="mt-10 border-t border-rose-100 py-6">
    <p class="text-center text-xs text-gray-500">© <?= date('Y') ?> Pâtisserie — Admin</p>
  </footer>

  <script>
    // Tabs
    const tabs = document.querySelectorAll('.tab');
    const secCarts = document.getElementById('tab-carts');
    const secOrders= document.getElementById('tab-orders');
    tabs.forEach(btn=>{
      btn.addEventListener('click', ()=>{
        tabs.forEach(b=> b.classList.remove('bg-pink-600','text-white'));
        btn.classList.add('bg-pink-600','text-white');
        const t = btn.dataset.tab;
        if (t==='carts'){ secCarts.classList.remove('hidden'); secOrders.classList.add('hidden'); }
        else            { secOrders.classList.remove('hidden'); secCarts.classList.add('hidden'); }
      });
    });
  </script>
</body>
</html>
