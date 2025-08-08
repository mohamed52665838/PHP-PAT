<?php
require_once '../../../../src/controllers/userController.php';
require_once '../../../../src/models/User.php';

$controller = new UserController();

$message = '';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];




    $user = new User($nom, $prenom, $password, $role, $email);

    $controller->add($user);

    header('Location: getUsers.php');
    exit;
}

$userAModifier = null;
//isset($_GET['id']) on verifie si l'url contient l'id a modifier 
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Récupérer le produit par id
    $users = $controller->getUsers();
    //na3mlo parcours o nlawjo 3ala produits id ta3o nhebo alih
    foreach ($users as $p) {
        if ($p['id'] === $id) {
            $userAModifier = $p;
            break;
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $id = intval($_POST['id']);
    // Sécuriser le mot de passe (optionnel mais recommandé)

    $user = new User($nom, $prenom, $password, $role, $email);

    $controller->update($user, $id);

    header('Location: getUsers.php');
    exit;
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Gestion Users Simple</title>
</head>

<body>
    <h1>Gestion des Users</h1>

    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <?php if ($userAModifier): ?>
        <!-- si produit exsiste n3mlo affichage  -->
        <h2>Modifier un user</h2>
        <form method="post">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $userAModifier['id']; ?>">
            <!-- htmlspecialchars() code jay min base oila haja externe nforci navigateur yt3aml m3aha ka html -->
            <label>Nom : <input type="text" name="nom" value="<?php echo htmlspecialchars($userAModifier['nom']); ?>" required></label><br>
            <label>prenom : <input type="text" name="prenom" value="<?php echo htmlspecialchars($userAModifier['prenom']); ?>" required></label><br>
            <label>Rôle :
                <select name="role" required>
                    <option value="client" <?php if ($userAModifier['role'] === 'client') echo 'selected'; ?>>Client</option>
                    <option value="preparateur" <?php if ($userAModifier['role'] === 'preparateur') echo 'selected'; ?>>Préparateur</option>
                </select>
            </label><br> <label>email : <input type="text" name="email" value="<?php echo htmlspecialchars($userAModifier['email']); ?>"></label><br>
            <label>password : <input type="password" name="password" value="<?php echo htmlspecialchars($userAModifier['password']); ?>" required></label><br>

            <button type="submit">Modifier</button>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Annuler</a>
            <!-- tenzel aliha tnailik les parametres -->
        </form>
    <?php else: ?>
        <h2>Ajouter un user</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <!-- hidden cache 3ala utilisateur ttb3ath fi request action add -->

            <label>Nom : <input type="text" name="nom" required></label><br>
            <label>prenom : <input type="text" name="prenom" required></label><br>
            <label>Rôle :
                <select name="role" required>
                    <option value="">--Choisir un rôle--</option>
                    <option value="client">Client</option>
                    <option value="preparateur">Préparateur</option>
                </select>
            </label><br> <label>email : <input type="text" name="email"></label><br>
            <label>password : <input type="password" name="password" required></label><br>

            <button type="submit">Ajouter</button>
        </form>
    <?php endif; ?>


</body>

</html>