<?php
require_once '../lib/url.php';
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
    <form action="../lib/register_login.php" method="POST" class="p-6 bg-white rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold text-center text-gray-700">Inscription</h2>

        <label class="block mt-4">
            <span class="text-gray-600">Nom d'utilisateur</span>
            <input type="text" name="username" required class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </label>

        <label class="block mt-4">
            <span class="text-gray-600">Email</span>
            <input type="email" name="email" required class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </label>

        <label class="block mt-4">
            <span class="text-gray-600">Mot de passe</span>
            <input type="password" name="password" required class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </label>

        <label class="block mt-4">
            <span class="text-gray-600">Confirmer le mot de passe</span>
            <input type="password" name="confirm_password" required class="w-full mt-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </label>

        <button type="submit" class="w-full mt-6 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">S'inscrire</button>
    </form>
</body>
</html>
<section class="flex items-center  border rounded p-4 mt-4 bg-gray-100">
    <a class="flex solid border bg-black-100">+</a>
    <a>ajouter taille</a>
</section>
