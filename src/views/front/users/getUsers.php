<?php
require_once '../../../../src/controllers/userController.php';
require_once '../../../../src/models/User.php';

$controller = new UserController();


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $controller->delete($id);
    $message = "user supprimé.";
    $users = $controller->getUsers();
}

$users=$controller->getUsers()





?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des Utilisateurs</title>
</head>
<body>

<h2>Liste des utilisateurs</h2>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['nom']) ?></td>
            <td><?= htmlspecialchars($user['prenom']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td> <a href="indexUsers.php?id=<?php echo $user['id']; ?>">Modifier</a> |
                      <!-- lien ili bich ytb3ath url o m3ah id comme parametre  <a href="indexProduits.php?id=7">Modifier</a> -->
                     <!-- Redirige l'utilisateur vers indexProduits.php en ajoutant dans url lid -->
                        <a href="?delete=<?php echo $user['id']; ?>" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
                        <!-- je veux rester dans meme page o bich n3adi id ili bich nfs5o b echo yktbli valeur fiurl --></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>