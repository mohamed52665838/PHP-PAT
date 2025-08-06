<?php
require_once './src/controllers/produitsController.php';
$listProduit = new ProduitsController();


var_dump( $listProduit->getProduits());