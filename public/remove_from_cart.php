<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../lib/url.php';  
require_once '../lib/db.php';
require_once path_lib_register_login();

if (!isUserLoggedIn()) {
    header("Location: ../public/login.php");
    exit();
}

$pdo = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ Échec de vérification CSRF. Veuillez réessayer.");
    }

    if (!isset($_POST["cart_id"]) || !is_numeric($_POST["cart_id"])) {
        $_SESSION['message'] = "❌ Erreur : ID du produit invalide.";
        header("Location: cart.php");
        exit();
    }

    $cartId = intval($_POST["cart_id"]);

    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cartId]);

        $_SESSION['message'] = "✅ Produit retiré du panier.";
    } catch (PDOException $e) {
        $_SESSION['message'] = "❌ Erreur lors de la suppression : " . $e->getMessage();
    }

    header("Location: cart.php");
    exit();
}
?>
