<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../lib/url.php';

$error_message = $_SESSION['error'] ?? "Une erreur est survenue";
unset($_SESSION['error']); // Clear the error after displaying
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="p-6 bg-white rounded-lg shadow-md w-96 text-center">
        <h2 class="text-2xl font-bold text-red-600 mb-4">⚠️ Erreur</h2>
        <p class="text-gray-700 mb-6"><?php echo htmlspecialchars($error_message); ?></p>
        <div class="space-y-4">
            <a href="<?php echo register(); ?>" class="block w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Réessayer l'inscription
            </a>
            <a href="<?php echo index(); ?>" class="block w-full bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>