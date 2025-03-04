<?php

require_once '../lib/db.php';
require_once '../lib/url.php';

$pdo = getDatabaseConnection();



$searchQuery = "";
$params = [];
$conditions = [];

if (!empty($_GET['search'])) {
    $search = "%" . trim($_GET['search']) . "%";
    $conditions[] = "(title LIKE :search_title OR description LIKE :search_desc OR color LIKE :search_color OR size LIKE :search_size OR tags LIKE :search_tags)";
    $params['search_title'] = $search;
    $params['search_desc'] = $search;
    $params['search_color'] = $search;
    $params['search_size'] = $search;
    $params['search_tags'] = $search;
}

if (!empty($_GET['min_price'])) {
    $conditions[] = "price >= :min_price";
    $params['min_price'] = floatval($_GET['min_price']);
}
if (!empty($_GET['max_price'])) {
    $conditions[] = "price <= :max_price";
    $params['max_price'] = floatval($_GET['max_price']);
}

if (!empty($_GET['color'])) {
    $conditions[] = "color = :color";
    $params['color'] = $_GET['color'];
}

if (!empty($_GET['size'])) {
    $conditions[] = "FIND_IN_SET(:size, size)";
    $params['size'] = $_GET['size'];
}

if (!empty($_GET['in_stock'])) {
    $conditions[] = "stock > 0";
}

$query = "SELECT * FROM products";
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $pdo->prepare($query);
foreach ($params as $key => &$value) {
    $stmt->bindParam(":$key", $value, PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecom Kimono</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="flex flex-col sm:flex-row items-center justify-between p-4 bg-white shadow-md">
        <nav class="flex items-center space-x-4 mb-2 sm:mb-0">
            <a href="index.php" class="text-lg font-semibold text-gray-700 hover:text-gray-900">Accueil</a>
        </nav>

        <h1 class="text-2xl font-bold text-gray-800">Ecom Kimono</h1>

        <nav class="flex items-center space-x-4">
            <?php include '../lib/graphic_logout.php'; ?>
        </nav>
    </header>

    <main class="p-4 sm:p-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 text-center sm:text-left">Nos Produits</h2>

        <form method="GET" action="index.php" class="flex flex-wrap gap-4 items-center mb-4">
            <input type="number" name="min_price" placeholder="Prix min (€)" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>" class="border p-2 rounded">
            <input type="number" name="max_price" placeholder="Prix max (€)" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>" class="border p-2 rounded">
            <select name="size" class="border p-2 rounded">
                <option value="">Toutes les tailles</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
            </select>
            <label class="flex items-center">
                <input type="checkbox" name="in_stock" value="1" <?php echo isset($_GET['in_stock']) ? 'checked' : ''; ?> class="mr-2">
                En stock uniquement
            </label>
            <button type="submit" class="border p-2 rounded">Filtrer</button>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <?php foreach ($products as $product): ?>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <a href="./public/produit.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="w-full h-40 object-cover mb-4 rounded-lg">
                        <h3 class="text-lg font-bold text-gray-700"><?php echo htmlspecialchars($product['title']); ?></h3>
                    </a>
                    <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($product['description']); ?></p>
                    <p class="font-bold mt-2 text-sm">Prix: <?php echo number_format($product['price'], 2, ',', ' '); ?> €</p>
                    <p class="text-sm">Stock: <?php echo $product['stock']; ?></p>
                    <p class="text-sm">Couleur: <?php echo htmlspecialchars($product['color']); ?></p>
                    <p class="text-sm">Taille: <?php echo htmlspecialchars($product['size']); ?></p>
                    <p class="text-sm">Tags: <?php echo htmlspecialchars($product['tags']); ?></p>
                    <a href="produit.php?id=<?php echo $product['id']; ?>" class="mt-4 block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">Voir Détails</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>

