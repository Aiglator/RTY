<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/db.php';

// Vérifier si un produit était en attente d'ajout après connexion
if (isset($_SESSION['pending_product'])) {
    $_POST = $_SESSION['pending_product']; // Restaurer les données
    unset($_SESSION['pending_product']); // Supprimer après ajout
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_product'] = $_POST; // Sauvegarder le produit temporairement
    $_SESSION['redirect_after_login'] = "add_to_cart.php";
    header("Location: ../public/login.php");
    exit();
}

// Vérifier si les données du produit sont bien envoyées
if (!isset($_POST['product_id'], $_POST['size'], $_POST['color'])) {
    header("Location: ../index.php");
    exit();
}

$productId = intval($_POST['product_id']);
$size = htmlspecialchars($_POST['size']);
$color = htmlspecialchars($_POST['color']);
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

// Récupérer les détails du produit depuis la base de données
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $productId, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Vérifier si le panier existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Vérifier si le produit est déjà dans le panier
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['product_id'] == $productId && $item['size'] == $size && $item['color'] == $color) {
        $item['quantity'] += $quantity; // 🚀 Incrémentation si le produit existe déjà
        $found = true;
        break;
    }
}

// Ajouter un nouveau produit s'il n'existe pas encore dans le panier
if (!$found) {
    $_SESSION['cart'][] = [
        'product_id' => $product['id'],
        'title' => $product['title'],
        'price' => $product['price'],
        'size' => $size,
        'color' => $color,
        'quantity' => $quantity,
        'image' => $product['image']
    ];
}

// Rediriger vers `cart.php`
header("Location: cart.php");
exit();
