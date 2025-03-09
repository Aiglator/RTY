<?php
require_once '../lib/url.php';
require_once path_lib_register_login();

if (!isUserLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit();
}

require_once '../lib/db.php';
$pdo = getDatabaseConnection();

// Démarrer la session si elle n'est pas encore active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Générer un jeton CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Initialisation du message
$message = "";

//  Traitement du formulaire pour ajouter ou retirer du stock
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ Échec de vérification CSRF. Veuillez réessayer.");
    }

    $productId = intval($_POST["product_id"]);
    $stockChange = intval($_POST["stock_change"]);
    $operation = $_POST["operation"];

    if ($operation === "add") {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + :change WHERE id = :id");
    } elseif ($operation === "remove") {
        $stmt = $pdo->prepare("UPDATE products SET stock = GREATEST(stock - :change, 0) WHERE id = :id");
    } else {
        die("❌ Opération invalide.");
    }

    if ($stmt->execute(['change' => $stockChange, 'id' => $productId])) {
        $_SESSION['message'] = " Stock mis à jour avec succès.";
    } else {
        $_SESSION['message'] = "❌ Erreur lors de la mise à jour du stock.";
    }

    //  Redirection pour éviter le rechargement du formulaire
    header("Location: stock.php");
    exit();
}

// Récupérer la liste des produits
$stmt = $pdo->query("SELECT id, title, stock FROM products ORDER BY title ASC");
$products = $stmt->fetchAll();

// Récupérer les produits les plus achetés
$stmt = $pdo->query("
    SELECT p.title, SUM(oi.quantity) AS total_sold 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
$topProducts = $stmt->fetchAll();

// Récupérer le message stocké en session (et le supprimer)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Stocks</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
<nav>
    <ul class="flex gap-4">
        <li><a href="<?php echo url_admin_dashboard(); ?>" class="text-blue-600 hover:underline">Gestion des Produits</a></li>
    </ul>
</nav>
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-center text-gray-700">🛒 Gestion des Stocks</h1>

        <!--  Affichage du message après mise à jour -->
        <?php if (!empty($message)): ?>
            <div class="p-3 mt-4 text-white font-semibold rounded <?php echo strpos($message, '✅') !== false ? 'bg-green-500' : 'bg-red-500'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de mise à jour du stock -->
        <div class="mt-6 bg-gray-50 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4 text-gray-600">🔄 Modifier le stock</h2>
            <form method="POST" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                <label class="block text-sm font-medium text-gray-700">Produit :</label>
                <select name="product_id" required class="w-full p-2 border rounded">
                    <option value="" disabled selected>Choisissez un produit</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="block text-sm font-medium text-gray-700">Quantité :</label>
                <input type="number" name="stock_change" required min="1" class="w-full p-2 border rounded">

                <div class="flex gap-2">
                    <button type="submit" name="operation" value="add" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">
                        ➕ Ajouter au stock
                    </button>
                    <button type="submit" name="operation" value="remove" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 w-full">
                        ➖ Retirer du stock
                    </button>
                </div>
            </form>
        </div>

        <!--  Tableau des stocks actuels -->
        <div class="mt-8 bg-gray-50 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4 text-gray-600">📦 Produits et Stock Actuel</h2>
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 text-left">ID</th>
                        <th class="p-2 text-left">Produit</th>
                        <th class="p-2 text-left">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($product['id']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($product['title']); ?></td>
                            <td class="p-2 font-semibold <?php echo $product['stock'] > 5 ? 'text-green-500' : 'text-red-500'; ?>">
                                <?php echo $product['stock']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!--  Produits les plus achetés -->
        <div class="mt-8 bg-gray-50 p-4 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4 text-gray-600">🔥 Produits les plus achetés</h2>
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 text-left">Produit</th>
                        <th class="p-2 text-left">Quantité vendue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topProducts as $product): ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($product['title']); ?></td>
                            <td class="p-2 font-semibold text-blue-600"><?php echo $product['total_sold']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
