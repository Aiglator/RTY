<?php
require_once '../lib/url.php';  

require_once path_lib_register_login();



// Vérifier si l'utilisateur est connecté
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande réussie !</title>
</head>
<body>
    <h1>✅ Votre commande a été passée avec succès !</h1>
    <p>Merci, <?= htmlspecialchars($_SESSION['username']) ?>, pour votre achat !</p>
    <a href="index.php">Retourner à la boutique</a>
</body>
</html>
