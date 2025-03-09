<?php
//  Démarrer la session si elle n'est pas active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//  Inclure les fichiers nécessaires
require_once '../lib/url.php';
require_once '../lib/db.php';
require_once path_lib_register_login();

//  Vérifier que l'utilisateur est bien connecté
if (!isUserLoggedIn()) {
    header("Location: ../public/login.php");
    exit();
}

//  Récupérer la connexion à la base de données
$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

//  Générer un jeton CSRF s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

//  Récupérer les articles du panier depuis la BDD
$stmt = $pdo->prepare("SELECT c.id, p.title, p.price, c.size, c.color, c.quantity, (p.price * c.quantity) AS total_price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-center text-gray-700">🛒 Mon Panier</h1>

        <?php if (!empty($_SESSION['message'])): ?>
            <div class="mt-4 p-3 text-white font-semibold rounded <?php echo strpos($_SESSION['message'], '') !== false ? 'bg-green-500' : 'bg-red-500'; ?>">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <p class="text-gray-600 text-center mt-4">Votre panier est vide.</p>
            <a href="index.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4 block text-center">
                🛒 Continuer mes achats
            </a>
        <?php else: ?>
            <!-- 📦 Tableau du panier -->
            <table class="w-full border-collapse mt-6">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 text-left">Produit</th>
                        <th class="p-2 text-left">Taille</th>
                        <th class="p-2 text-left">Couleur</th>
                        <th class="p-2 text-left">Quantité</th>
                        <th class="p-2 text-left">Prix Unitaire</th>
                        <th class="p-2 text-left">Total</th>
                        <th class="p-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $totalPanier = 0; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <?php $totalPanier += $item['total_price']; ?>
                        <tr class="border-b">
                            <td class="p-2"><?php echo htmlspecialchars($item['title']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($item['size']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($item['color']); ?></td>
                            <td class="p-2"><?php echo (int) $item['quantity']; ?></td>
                            <td class="p-2"><?php echo number_format($item['price'], 2, ',', ' '); ?> €</td>
                            <td class="p-2"><?php echo number_format($item['total_price'], 2, ',', ' '); ?> €</td>
                            <td class="p-2">
                                <form method="POST" action="remove_from_cart.php" novalidate>
                                    <input type="hidden" name="cart_id" value="<?php echo (int) $item['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-700">❌ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!--  Affichage du total -->
            <div class="text-right font-bold text-lg sm:text-xl mt-4">
                Total : <span class="text-green-600"><?php echo number_format($totalPanier, 2, ',', ' '); ?> €</span>
            </div>

            <!-- 🔄 Vider le panier -->
            <div class="mt-6 flex justify-between">
                <form method="POST" action="clear_cart.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        🗑️ Vider le panier
                    </button>
                </form>
                <a href="checkout.php" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                     Passer la commande
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
