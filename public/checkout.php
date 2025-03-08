<?php
// Vérifier si la session est active avant de la démarrer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php'; 
require_once '../lib/db.php';  

$pdo = getDatabaseConnection();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = "checkout.php";
    header("Location: login.php");
    exit();
}

// Récupérer les articles du panier depuis la base de données
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT c.*, p.title, p.price, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si le panier existe et n'est pas vide
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// Calcul du total du panier
$totalPanier = 0;
foreach ($cartItems as $item) {
    $totalPanier += ($item['price'] * $item['quantity']);
}

// Traitement du paiement et mise à jour du stock
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo->beginTransaction(); // 🔹 Démarrer une transaction pour éviter les incohérences

    try {
        // 🔹 Insérer une nouvelle commande
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $totalPanier]);
        $orderId = $pdo->lastInsertId();

        // 🔹 Insérer les articles commandés et mettre à jour le stock
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $updateStockStmt = $pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");

        foreach ($cartItems as $item) {
            // Vérifier si le stock est suffisant
            if ($item['stock'] < $item['quantity']) {
                throw new Exception("Stock insuffisant pour le produit : " . htmlspecialchars($item['title']));
            }

            // Ajouter chaque produit à la commande
            $stmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);

            // Mettre à jour le stock
            $updateStockStmt->execute([$item['quantity'], $item['product_id']]);
        }

        // 🔹 Vider le panier après la validation de la commande
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        $pdo->commit(); // 🔹 Valider la transaction

        header("Location: order_success.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack(); // ❌ Annuler la transaction en cas d'erreur
        die("Erreur lors du paiement : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 sm:p-10">
    <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-center sm:text-left">Checkout</h1>

    <?php include '../lib/graphic_logout.php'; ?>

    <div class="w-full max-w-4xl bg-white p-4 sm:p-6 rounded-lg shadow-md mx-auto">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-xs sm:text-sm">
                        <th class="p-2 sm:p-3 text-left">Produit</th>
                        <th class="p-2 sm:p-3 text-left">Quantité</th>
                        <th class="p-2 sm:p-3 text-left">Prix Unitaire</th>
                        <th class="p-2 sm:p-3 text-left">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr class="border-b text-xs sm:text-sm">
                            <td class="p-2 sm:p-3"><?php echo htmlspecialchars($item['title'] ?? 'Produit inconnu'); ?></td>
                            <td class="p-2 sm:p-3"><?php echo htmlspecialchars($item['quantity'] ?? "0"); ?></td>
                            <td class="p-2 sm:p-3"><?php echo number_format($item['price'] ?? 0, 2, ',', ' '); ?> €</td>
                            <td class="p-2 sm:p-3 font-semibold"><?php echo number_format(($item['price'] * $item['quantity']), 2, ',', ' '); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="text-right font-bold text-lg sm:text-xl mt-4">
            Total : <span class="text-green-600"><?php echo number_format($totalPanier, 2, ',', ' '); ?> €</span>
        </div>

        <form method="POST" action="checkout.php" class="mt-6">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Payer</button>
        </form>
    </div>

    <div class="mt-6 text-center">
        <a href="cart.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">Retour au panier</a>
    </div>
</body>
</html>