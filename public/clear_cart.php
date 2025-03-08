<?php
session_start();
require_once '../lib/url.php';  
require_once '../lib/db.php';
require_once path_lib_register_login();

if (!isUserLoggedIn()) {
    header("Location: ../public/login.php");
    exit();
}

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->execute([$userId]);

$_SESSION['message'] = "✅ Panier vidé.";
header("Location: cart.php");
exit();
?>
