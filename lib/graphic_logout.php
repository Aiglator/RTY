<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php';

echo '<div class="flex justify-end p-4 space-x-4">';

if (isset($_SESSION['user_id'])) {
    echo '<span class="text-sm sm:text-lg font-semibold text-green-600">Bienvenue, ' . htmlspecialchars($_SESSION['username']) . ' !</span>';
    echo '<a href="logout.php" class="text-sm sm:text-lg font-semibold text-red-600 hover:text-red-800">Se Déconnecter</a>';
} else {
    echo '<a href="register.php" class="text-sm sm:text-lg font-semibold text-green-600 hover:text-blue-800">S\'inscrire</a>';
    echo '<a href="login.php" class="text-sm sm:text-lg font-semibold text-blue-600 hover:text-blue-800">Se Connecter</a>';
}

echo '</div>';
?>
