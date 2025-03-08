<?php
session_start();
require_once '../lib/url.php';  
require_once path_lib_db(); // ✅ Correct et testé

// Assurez-vous que la fonction de génération de token CSRF est disponible
require_once path_lib_register_login(); // Supposons que ce fichier contient la fonction generateCsrfToken

$pdo = getDatabaseConnection();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Produit introuvable.");
}

$productId = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $productId, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produit introuvable.");
}

$sizes = explode(",", $product['size']);
$colors = explode(",", $product['color']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <div class="bg-white p-6 rounded shadow-md max-w-xl mx-auto">
        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="w-full h-64 object-cover mb-4">
        <h1 class="text-3xl font-bold"><?php echo htmlspecialchars($product['title']); ?></h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($product['description']); ?></p>
        <p class="font-bold mt-2 text-lg">Prix: <?php echo number_format($product['price'], 2, ',', ' '); ?> €</p>

        <form method="POST" action="<?php echo url('add_to_cart.php'); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <label class="block mt-4 font-semibold">Taille :</label>
            <select name="size" required class="border p-2 rounded w-full">
                <?php foreach ($sizes as $size): ?>
                    <option value="<?php echo htmlspecialchars(trim($size)); ?>"><?php echo htmlspecialchars(trim($size)); ?></option>
                <?php endforeach; ?>
            </select>

            <label class="block mt-4 font-semibold">Couleur :</label>
            <select name="color" required class="border p-2 rounded w-full">
                <?php foreach ($colors as $color): ?>
                    <option value="<?php echo htmlspecialchars(trim($color)); ?>"><?php echo htmlspecialchars(trim($color)); ?></option>
                <?php endforeach; ?>
            </select>

            <label class="block mt-4 font-semibold">Quantité :</label>
            <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required class="border p-2 rounded w-full">
            <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
                Ajouter au panier
            </button>
        </form>
    </div>
</body>
</html>
