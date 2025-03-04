<?php
session_start();
require_once '../lib/db.php';
require_once '../url.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}



if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé.");
}

$pdo = getDatabaseConnection();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du produit invalide.");
}

$product_id = intval($_GET['id']);

// Récupérer les données du produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Produit non trouvé.");
}

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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_product'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $color = trim($_POST['color']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $size = isset($_POST['size']) && is_array($_POST['size']) ? implode(",", $_POST['size']) : '';
    $tags = isset($_POST['tags']) && is_array($_POST['tags']) ? implode(",", $_POST['tags']) : '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = uniqid() . "_" . basename($_FILES['image']['name']);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath);

        if (file_exists($product['image'])) {
            unlink($product['image']);
        }

        $stmt = $pdo->prepare("UPDATE products SET image = :image, title = :title, description = :description, color = :color, price = :price, stock = :stock, size = :size, tags = :tags WHERE id = :id");
        $stmt->bindValue(':image', $targetFilePath, PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare("UPDATE products SET title = :title, description = :description, color = :color, price = :price, stock = :stock, size = :size, tags = :tags WHERE id = :id");
    }

    $stmt->bindValue(':title', $title, PDO::PARAM_STR);
    $stmt->bindValue(':description', $description, PDO::PARAM_STR);
    $stmt->bindValue(':color', $color, PDO::PARAM_STR);
    $stmt->bindValue(':price', $price, PDO::PARAM_STR);
    $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
    $stmt->bindValue(':size', $size, PDO::PARAM_STR);
    $stmt->bindValue(':tags', $tags, PDO::PARAM_STR);
    $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        header("Location: admin.php");
        exit();
    } else {
        echo "<p class='text-red-600'>Erreur lors de la mise à jour du produit.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Produit</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <h1 class="text-3xl font-bold mb-6">Modifier le Produit</h1>
    
    <form action="modif.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md">
        <label class="block mb-2">Image actuelle:</label>
        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="w-40 h-40 object-cover mb-4">
        <label class="block mb-2">Nouvelle Image:</label>
        <input type="file" name="image" class="block w-full mb-4">

        <label class="block mb-2">Titre:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Description:</label>
        <textarea name="description" required class="w-full mb-4 p-2 border rounded"><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label class="block mb-2">Couleur:</label>
        <input type="text" name="color" value="<?php echo htmlspecialchars($product['color']); ?>" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Prix:</label>
        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Stock:</label>
        <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Tailles:</label>
        <div class="flex gap-2 flex-wrap">
            <?php
            $selectedSizes = explode(",", $product['size']);
            foreach (["XS", "S", "M", "L", "XL", "XXL"] as $sizeOption): ?>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="size[]" value="<?php echo $sizeOption; ?>" <?php echo in_array($sizeOption, $selectedSizes) ? 'checked' : ''; ?> class="mr-2"> <?php echo $sizeOption; ?>
                </label>
            <?php endforeach; ?>
        </div>

        <label class="block mb-2">Tags:</label>
        <div class="flex gap-2 flex-wrap">
            <?php
            $selectedTags = explode(",", $product['tags']);
            foreach ($tagOptions as $tag): ?>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>" <?php echo in_array($tag, $selectedTags) ? 'checked' : ''; ?> class="mr-2"> <?php echo htmlspecialchars($tag); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <input type="text" name="tags[]" placeholder="Ajoutez de nouveaux tags, séparés par une virgule" class="w-full mb-4 p-2 border rounded">

        <button type="submit" name="update_product" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Mettre à jour</button>
        <a href="admin.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 ml-4">Annuler</a>
    </form>
</body>
</html>
