<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php';
require_once path_lib_db();

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
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="w-96 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-700">Connexion</h2>

        <!--  Affichage des erreurs stockées en session -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="mt-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <?php 
                    echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); 
                    unset($_SESSION['error']); // Supprimer après affichage
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo path_lib_register_login()?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="login">
            
            <label class="block mt-4">
                <span class="text-gray-600">Email</span>
                <input type="email" name="email" required autocomplete="off" 
                       class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>
            
            <label class="block mt-4">
                <span class="text-gray-600">Mot de passe</span>
                <input type="password" name="password" required minlength="6" autocomplete="new-password"
                       class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </label>
            
            <button type="submit" class="w-full mt-6 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Se connecter
            </button>
        </form>
        
        <p class="mt-4 text-center text-gray-600">Pas encore de compte ? 
            <a href="register.php" class="text-blue-600 hover:underline">Inscris-toi ici</a>
        </p>
    </div>
</body>
</html>
