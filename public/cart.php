<?php
// Vérifier si la session est active avant de la démarrer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = "cart.php";
    header("Location: login.php");
    exit();
}

// Vérifier si le panier existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Supprimer un produit du panier
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $index = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Réindexer le tableau
    }
}

// Vider le panier
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}

// Calcul du total du panier
$totalPanier = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPanier += ($item['price'] * $item['quantity']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 sm:p-10">
    <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-center sm:text-left">Mon Panier</h1>

    <?php include '../lib/graphic_logout.php'; ?>

    <?php if (empty($_SESSION['cart'])): ?>
        <p class="text-gray-600 text-center">Votre panier est vide.</p>
    <?php else: ?>
        <div class="w-full max-w-4xl bg-white p-4 sm:p-6 rounded-lg shadow-md mx-auto">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-xs sm:text-sm">
                            <th class="p-2 sm:p-3 text-left">Produit</th>
                            <th class="p-2 sm:p-3 text-left">Taille</th>
                            <th class="p-2 sm:p-3 text-left">Couleur</th>
                            <th class="p-2 sm:p-3 text-left">Quantité</th>
                            <th class="p-2 sm:p-3 text-left">Prix Unitaire</th>
                            <th class="p-2 sm:p-3 text-left">Total</th>
                            <th class="p-2 sm:p-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <tr class="border-b text-xs sm:text-sm">
                                <td class="p-2 sm:p-3"><?php echo htmlspecialchars($item['title'] ?? 'Produit inconnu'); ?></td>
                                <td class="p-2 sm:p-3"><?php echo htmlspecialchars($item['size'] ?? "Inconnu"); ?></td>
                                <td class="p-2 sm:p-3"><?php echo htmlspecialchars($item['color'] ?? "Inconnu"); ?></td>
                                <td class="p-2 sm:p-3"><?php echo htmlspecialchars($item['quantity'] ?? "0"); ?></td>
                                <td class="p-2 sm:p-3"><?php echo number_format($item['price'] ?? 0, 2, ',', ' '); ?> €</td>
                                <td class="p-2 sm:p-3 font-semibold"><?php echo number_format(($item['price'] * $item['quantity']), 2, ',', ' '); ?> €</td>
                                <td class="p-2 sm:p-3">
                                    <a href="cart.php?remove=<?php echo $index; ?>" class="text-red-500 hover:text-red-700 text-xs sm:text-sm">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 🚀 Total du panier -->
            <div class="text-right font-bold text-lg sm:text-xl mt-4">
                Total : <span class="text-green-600"><?php echo number_format($totalPanier, 2, ',', ' '); ?> €</span>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row justify-between space-y-2 sm:space-y-0">
                <a href="cart.php?clear=1" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-center sm:text-left">Vider le panier</a>
                <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center sm:text-left">Continuer les achats</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- 🚀 Bouton Retour à l'accueil -->
    <div class="mt-6 text-center">
        <a href="index.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">Retour à l'accueil</a>
    </div
