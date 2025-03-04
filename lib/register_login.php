<?php
// Vérification et démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
    ]);
}

// Activer l'affichage des erreurs (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour générer un token CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le token CSRF
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour vérifier si un utilisateur est connecté
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

require_once 'db.php';
$pdo = getDatabaseConnection();

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("⚠️ Échec de vérification CSRF. Veuillez réessayer.");
    }
    
    $action = $_POST['action'] ?? '';

    // 🔹 Gestion de l'inscription
    if ($action === 'register') {
        $username = trim($_POST["username"]);
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST["password"]);
        $confirm_password = trim($_POST["confirm_password"]);

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            die("⚠️ Tous les champs sont obligatoires !");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die("⚠️ Adresse email invalide !");
        }

        if ($password !== $confirm_password) {
            die("⚠️ Les mots de passe ne correspondent pas !");
        }

        if (strlen($password) < 8) {
            die("⚠️ Le mot de passe doit contenir au moins 8 caractères !");
        }

        // Vérifier si l'utilisateur existe déjà
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $checkStmt->bindValue(':username', $username, PDO::PARAM_STR);
        $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            die("⚠️ Ce nom d'utilisateur ou cet email est déjà utilisé !");
        }

        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        // Insertion en base
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header('Location: login.php?success=register', true, 303);
            exit;
        } else {
            die("⚠️ Une erreur est survenue lors de l'inscription.");
        }
    } 
    // 🔹 Gestion de la connexion
    elseif ($action === 'login') {
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST["password"]);

        if (empty($email) || empty($password)) {
            die("⚠️ Tous les champs sont obligatoires !");
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
        
            // Si l'utilisateur est un admin, rediriger vers admin.php
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin.php");
                exit;
            }
        
            // 🔹 Vérifier s'il y avait un produit en attente d'ajout au panier
            if (isset($_SESSION['pending_product'])) {
                $_POST = $_SESSION['pending_product']; // Restaurer les données
                unset($_SESSION['pending_product']); // Supprimer après ajout
                require '../publicadd_to_cart.php'; // Exécuter l'ajout au panier
                header("Location: ../public/cart.php"); // Rediriger vers le panier
                exit();
            }
        
            // 🔹 Rediriger l'utilisateur vers index.php si aucune action spécifique
            header("Location: ../public/index.php");
            exit();
        } else {
            die("⚠️ Email ou mot de passe incorrect !");
        }
    }
}