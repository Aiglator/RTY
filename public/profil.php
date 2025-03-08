<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../lib/url.php';
require_once '../lib/db.php';
require_once path_lib_register_login();

// Vérifier si l'utilisateur est connecté
if (!isUserLoggedIn()) {
    header("Location: " . login());
    exit();
}

$pdo = getDatabaseConnection();
$userId = intval($_SESSION['user_id']); // Sécurisation en forçant un entier

// Récupérer les informations de l'utilisateur de manière sécurisée
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :id");
$stmt->bindParam(":id", $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
}

// ✅ Générer un token CSRF pour éviter les attaques
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Récupération du message stocké en session (après redirection PRG)
$message = "";
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Supprimer le message après l'affichage
}

// ✅ Traitement du formulaire de mise à jour
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérification du CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("⚠️ Échec de vérification CSRF. Veuillez réessayer.");
    }

    // Sécurisation des entrées utilisateur
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (empty($username) || empty($email)) {
        $_SESSION['message'] = "⚠️ Tous les champs sont obligatoires !";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "⚠️ Adresse email invalide !";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":id", $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['message'] = "✅ Profil mis à jour avec succès !";
        } else {
            $_SESSION['message'] = "❌ Une erreur est survenue lors de la mise à jour.";
        }
    }

    // ✅ Redirection PRG pour éviter la resoumission du formulaire
    header("Location: profil.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-lg mx-auto bg-white p-6 rounded shadow-md">
        <h1 class="text-2xl font-bold mb-4">👤 Mon Profil</h1>
        
        <!-- ✅ Affichage du message avec PRG -->
        <?php if (!empty($message)): ?>
            <div class="p-3 mb-4 rounded <?php echo strpos($message, '✅') !== false ? 'bg-green-500' : 'bg-red-500'; ?> text-white">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="profil.php" class="space-y-4">
            <!-- ✅ Champ caché pour le CSRF -->
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

            <label class="block text-sm font-medium text-gray-700">Nom d'utilisateur :</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full p-2 border rounded">
            
            <label class="block text-sm font-medium text-gray-700">Email :</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full p-2 border rounded">
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">Mettre à jour</button>
        </form>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="text-blue-600 hover:underline">⬅️ Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
