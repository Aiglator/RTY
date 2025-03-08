<?php
session_start();
require_once '../lib/db.php';
require_once '../lib/url.php';



// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé.");
}

$pdo = getDatabaseConnection();

// Récupérer les tags existants
$existingTags = $pdo->query("SELECT DISTINCT tags FROM products WHERE tags IS NOT NULL AND tags != ''")->fetchAll(PDO::FETCH_COLUMN);
$tagOptions = [];
foreach ($existingTags as $tagList) {
    $tagsArray = explode(',', $tagList);
    foreach ($tagsArray as $tag) {
        $tag = trim($tag);
        if (!in_array($tag, $tagOptions)) {
            $tagOptions[] = $tag;
        }
    }
}
sort($tagOptions);

// Ajouter un produit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_product'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $color = trim($_POST['color']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    
    // Vérification et stockage des tailles en texte
    $size = isset($_POST['size']) && is_array($_POST['size']) ? implode(",", array_map('trim', $_POST['size'])) : '';
    
    // Vérification et stockage des tags
    $tags = isset($_POST['tags']) && is_array($_POST['tags']) ? implode(",", array_map('trim', $_POST['tags'])) : '';
    
    // Gestion de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = uniqid() . "_" . basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath);
    } else {
        die("Erreur lors du téléchargement de l'image.");
    }
    
    // Insérer le produit en base de données
    $stmt = $pdo->prepare("INSERT INTO products (image, title, description, color, price, stock, size, tags) VALUES (:image, :title, :description, :color, :price, :stock, :size, :tags)");
    $stmt->bindValue(':image', $targetFilePath, PDO::PARAM_STR);
    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':color', $color, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_STR);
    $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
    $stmt->bindValue(':size', $size, PDO::PARAM_STR);
    $stmt->bindValue(':tags', $tags, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "<p class='text-red-600'>Erreur lors de l'ajout du produit.</p>";
    }
}


// Supprimer un produit
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_product'])) {
    $productId = intval($_POST['product_id']);

    // Vérifier si le produit existe
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Supprimer l'image du serveur
        if (file_exists($product['image'])) {
            unlink($product['image']);
        }

        // Supprimer le produit de la base de données
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->bindValue(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();

        // Redirection pour éviter la soumission multiple
        header("Location: admin.php");
        exit();
    }
}
// Récupérer les produits
$products = $pdo->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Produits</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <h1 class="text-3xl font-bold mb-6">Gestion des Produits</h1>
    <?php
        include '../lib/graphic_logout.php'
    ?>
    <nav>
        <ul class="flex gap-4">
            <li><a href="admin.php" class="text-blue-600 hover:underline">Gestion des Produits</a></li>
            <li><a href="stock.php" class="text-blue-600 hover:underline">Gestion des Stocks</a></li>
    </nav>
    <form action="admin.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-bold mb-4">Ajouter un produit</h2>
        <label class="block mb-2">Image:</label>
        <input type="file" name="image" required class="block w-full mb-4">
        <label class="block mb-2">Titre:</label>
        <input type="text" name="title" required class="w-full mb-4 p-2 border rounded">
        <label class="block mb-2">Description:</label>
        <textarea name="description" required class="w-full mb-4 p-2 border rounded"></textarea>
        <label class="block mb-2">Couleur:</label>
        <input type="text" name="color" required class="w-full mb-4 p-2 border rounded">
        <label class="block mb-2">Prix:</label>
        <input type="number" step="0.01" name="price" required class="w-full mb-4 p-2 border rounded">
        <label class="block mb-2">Stock:</label>
        <input type="number" name="stock" required class="w-full mb-4 p-2 border rounded">
        <label class="block mb-2">Tailles:</label>
        <div class="flex gap-2 flex-wrap">
            <?php foreach (["XS", "S", "M", "L", "XL", "XXL"] as $sizeOption): ?>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="size[]" value="<?php echo $sizeOption; ?>" class="mr-2"> <?php echo $sizeOption; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <label class="block mb-2">Tags:</label>
        <div class="flex gap-2 flex-wrap">
            <?php foreach ($tagOptions as $tag): ?>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>" class="mr-2"> <?php echo htmlspecialchars($tag); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <input type="text" name="tags[]" placeholder="Ajoutez de nouveaux tags, séparés par une virgule" class="w-full mb-4 p-2 border rounded">
        <button type="submit" name="add_product" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Ajouter</button>
    </form>
    <h2 class="text-2xl font-bold mt-10 mb-4">Liste des Produits</h2>
    <div class="grid grid-cols-3 gap-4">
        <?php foreach ($products as $product): ?>
            <div class="bg-white p-4 rounded shadow-md">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="w-full h-40 object-cover mb-4">
                <h3 class="text-lg font-bold"><?php echo htmlspecialchars($product['title']); ?></h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($product['description']); ?></p>
                <p class="font-bold mt-2">Prix: <?php echo number_format($product['price'], 2, ',', ' '); ?> €</p>
                <p class="text-sm">Stock: <?php echo $product['stock']; ?></p>
                <p class="text-sm">Couleur: <?php echo htmlspecialchars($product['color']); ?></p>
                <p class="text-sm">Tailles: <?php echo nl2br(htmlspecialchars(str_replace(",", "\n", $product['size']))); ?></p>
                <p class="text-sm">Tags: <?php echo nl2br(htmlspecialchars(str_replace(",", "\n", $product['tags']))); ?></p>
                <div class="mt-4 flex gap-2">
                    <a href="modif.php?id=<?php echo $product['id']; ?>" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Modifier</a>
                    <form method="POST" action="admin.php">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="delete_product" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Supprimer</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>