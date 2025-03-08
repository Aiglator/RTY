<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php';

// Générer un token CSRF si absent
if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="p-6 bg-white rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center text-gray-700">Inscription</h2>

        <!-- ✅ Affichage des erreurs -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php 
                    echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); 
                    unset($_SESSION['error']); // Supprimer après affichage
                ?>
            </div>
        <?php endif; ?>

        <form action="../lib/register_login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="register">

            <label class="block mt-4">
                <span class="text-gray-600">Nom d'utilisateur</span>
                <input type="text" name="username" required 
                    class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>

            <label class="block mt-4">
                <span class="text-gray-600">Email</span>
                <input type="email" name="email" required 
                    class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>

            <label class="block mt-4">
                <span class="text-gray-600">Mot de passe</span>
                <input type="password" name="password" required minlength="8" autocomplete="new-password"
                    class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>

            <label class="block mt-4">
                <span class="text-gray-600">Confirmer le mot de passe</span>
                <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"
                    class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>

            <button type="submit" class="w-full mt-6 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                S'inscrire
            </button>
        </form>
    </div>
</body>
</html>
