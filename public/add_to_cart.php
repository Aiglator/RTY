<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php';  

require_once path_lib_register_login();

require_once path_lib_db(); // ✅ Correct et testé

$pdo = getDatabaseConnection();

// Vérifier si un produit était en attente d'ajout après connexion
if (isset($_SESSION['pending_product'])) {
    $_POST = $_SESSION['pending_product']; // Restaurer les données
    unset($_SESSION['pending_product']); // Supprimer après ajout
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['pending_product'] = $_POST; // Sauvegarder le produit temporairement
    $_SESSION['redirect_after_login'] = "add_to_cart.php";
    header("Location: " . login()); // Use URL function instead of relative path
    exit();
}

// Vérifier si les données du produit sont bien envoyées
if (!isset($_POST['product_id'], $_POST['size'], $_POST['color'])) {
    header("Location: " . index()); // Use URL function instead of relative path
    exit();
}

$userId = $_SESSION['user_id'];
$productId = intval($_POST['product_id']);
$size = htmlspecialchars($_POST['size']);
$color = htmlspecialchars($_POST['color']);
$quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

// Vérifier si le produit existe en BDD
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $productId, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Vérifier si le produit est déjà dans le panier en BDD
$stmt = $pdo->prepare("
    SELECT id, quantity FROM cart 
    WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?
");
$stmt->execute([$userId, $productId, $size, $color]);
$existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingItem) {
    // 🚀 Si le produit est déjà dans le panier, on met à jour la quantité
    $newQuantity = $existingItem['quantity'] + $quantity;
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$newQuantity, $existingItem['id']]);
} else {
    // 🆕 Ajouter le produit dans le panier
    $stmt = $pdo->prepare("
        INSERT INTO cart (user_id, product_id, title, price, size, color, quantity) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId, 
        $product['id'], 
        $product['title'], 
        $product['price'], 
        $size, 
        $color, 
        $quantity, 
    ]);
}

// Rediriger vers `cart.php`
header("Location: " . cart()); // Use URL function instead of relative path
exit();
