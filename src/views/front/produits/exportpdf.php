<?php
// exportpdf.php — version simple

require_once '../../../../src/controllers/produitsController.php';
require_once '../../../../src/models/Produit.php';

// Récup des filtres passés en GET
$searchId   = $_GET['search_id']   ?? '';
$searchName = $_GET['search_name'] ?? '';
$sort       = $_GET['sort']        ?? '';

// Charger les produits selon le filtre
$c = new ProduitsController();
$produits = [];

if ($searchId !== '') {
    // chercher par ID → un seul produit
    $p = $c->researcher((int)$searchId);
    $produits = $p ? [$p] : []; // on met dans un tableau pour pouvoir faire foreach
} elseif ($searchName !== '') {
    // chercher par nom → 0, 1 ou plusieurs
    $res = $c->researchByName($searchName);
    if ($res === null) {
        $produits = [];
    } else {
        $produits = $res;       // déjà une liste
    } 
} else {
    // pas de filtre → soit trié, soit tout
    if ($sort !== '') {
        $tmp = $c->getProduitsTri($sort);
    } else {
        $tmp = $c->getProduits();
    }
    $produits = $tmp ?? [];// resultat finales f produits chnparcouriha baed
}

// Base publique des images (à adapter à ton chemin)
//si $_SERVER['HTTP_HOST'] si HTTP_HOST contient nom domaine  exsiste sinon localhost 
$host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
//kain securiser site https o lient serveur t3ada https o https mhich desactiver f serveur nhototo https sinon http
$imgBase = $scheme.'://'.$host.'/pat/src/views/front/produits/uploads/';

//$imgBase = 'http://localhost/pat/src/views/front/produits/uploads/';


// Préparer une petite ligne “filtres”
//$filters = [];
// if ($searchId   !== '') $filters[] = 'ID #'.htmlspecialchars($searchId);
// if ($searchName !== '') $filters[] = 'Nom "'.htmlspecialchars($searchName).'"';
// if ($sort       !== '') $filters[] = 'Tri: '.htmlspecialchars($sort);
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Export produits</title>
<style>
  /* Styles simples */
  @media print { @page { size: A4 landscape; margin: 500mm; } }
  *{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  /* webkit nforciw biha bich ttb3lna les coleurs 33al pages  */ 
  body{ font-family: Arial, sans-serif; color:#111; }
  h1{ margin:0 0 8px; color:#c2185b; }
  .meta{ font-size:12px; color:#555; margin-bottom:10px; }

  table{ width:100%; border-collapse:collapse; font-size:12px; }
  th, td{ border:1px solid #ddd; padding:6px 8px; vertical-align:middle; }
  thead th{ background:#ffe4ec; color:#a4165e; text-align:left; }
  tbody tr:nth-child(even){ background:#fafafa; }

  .pimg{ width:60px; height:60px; object-fit:cover; border-radius:6px; display:block; }
  .price{ font-weight:bold; color:#a4165e; }
  .stock-ok{ color:#0f5132; font-weight:bold; }
  .stock-low{ color:#842029; font-weight:bold; }
</style>
</head>
<body onload="window.print()">
 <!-- lorsque on finir de charger la page window.print thelilna automatiquement fenetre mta3  dialoque ta pdf -->
  <h1>Liste des produits</h1>
<div class="meta">Export du <?= date('d/m/Y H:i:s') ?></div>
<!-- nhoto date bil php khtrha qote serveur 7athra fi php tol ta3tini date -->

  

  <table>
    <thead>
      <tr>
        <th style="width:60px;">ID</th>
        <th style="width:100px;">Image</th>
        <th style="width:200px;">Nom</th>
        <th style="width:500px;">Description</th>
        <th style="width:110px;">Prix (DT)</th>
        <th style="width:80px;">Stock</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($produits)): ?>
        <!-- kain tableau des produits mich fergh bich nparcourih -->
        <?php foreach ($produits as $p): ?>
          <?php
            // Construire l’URL de l’image à partir du nom de fichier
            $file = isset($p['image_url']) ? basename((string)$p['image_url']) : '';
            // on encode le nom pour éviter les espaces/accents qui cassent l’URL
            $src  = $file ? $imgBase . rawurlencode($file) : '';
            //kain fichier nest pas vide nhot imagBase o nlsklha ism  rawurlencode bich kif tbda tswira fiha #@!,?
            //tjibhom sans probleme les caractere  spéciaux
            $prix = number_format((float)($p['prix'] ?? 0), 2, ',', ' ');
            //format du nombre kain $p['prix'] ?? 0 kain fmch prix nesiste pas  thot 0
            $stk  = (int)($p['stock'] ?? 0);
          ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td>
              <?php if ($src): ?>
                <!-- kain image exsiste src mwjoud nhot fil src path mta3 image -->
                <img src="<?= htmlspecialchars($src) ?>" alt="" class="pimg">
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars((string)($p['nom'] ?? '')) ?></td>
            <td><?= htmlspecialchars((string)($p['description'] ?? '')) ?></td>
            <td class="price"><?= $prix ?></td>
            <td class="<?= $stk < 10 ? 'stock-low' : 'stock-ok' ?>"><?= $stk ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center; color:#666; padding:12px;">Aucun produit trouvé.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
