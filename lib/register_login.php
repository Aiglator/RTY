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
require_once 'url.php';
$pdo = getDatabaseConnection();

// Vérifier si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        die("⚠️ Échec de vérification CSRF. Veuillez réessayer.");
    }
    
    $action = $_POST['action'] ?? '';

    // 🔹 Gestion de l'inscription
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['error'] = "⚠️ Vérification CSRF échouée. Veuillez réessayer.";
            header("Location: ../public/register.php");
            exit;
        }
    
        $action = $_POST['action'] ?? '';
    
        if ($action === 'register') {
            $username = trim($_POST["username"]);
            $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
            $password = trim($_POST["password"]);
            $confirm_password = trim($_POST["confirm_password"]);
    
            // Vérifications
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['error'] = "⚠️ Tous les champs sont obligatoires !";
                header("Location: ../public/register.php");
                exit();
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = "⚠️ Adresse email invalide !";
                header("Location: ../public/register.php");
                exit();
            }
            if ($password !== $confirm_password) {
                $_SESSION['error'] = "⚠️ Les mots de passe ne correspondent pas !";
                header("Location: ../public/register.php");
                exit();
            }
            if (strlen($password) < 8) {
                $_SESSION['error'] = "⚠️ Le mot de passe doit contenir au moins 8 caractères !";
                header("Location: ../public/register.php");
                exit();
            }
    
            // Vérifier si l'utilisateur existe déjà
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $checkStmt->bindValue(':username', $username, PDO::PARAM_STR);
            $checkStmt->bindValue(':email', $email, PDO::PARAM_STR);
            $checkStmt->execute();
    
            if ($checkStmt->fetch()) {
                $_SESSION['error'] = "⚠️ Ce nom d'utilisateur ou cet email est déjà utilisé !";
                header("Location: ../public/register.php");
                exit();
            }
    
            // Hachage du mot de passe et insertion en base
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
    
            if ($stmt->execute()) {
                $_SESSION['success'] = "🎉 Inscription réussie ! Connectez-vous.";
                header("Location: ../public/login.php");
                exit;
            } else {
                $_SESSION['error'] = "⚠️ Une erreur est survenue lors de l'inscription.";
                header("Location: ../public/register.php");
                exit;
            }
        }
    }
    // 🔹 Gestion de la connexion
    elseif ($action === 'login') {
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST["password"]);

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "⚠️ Tous les champs sont obligatoires !";
            header("Location: ../public/login.php");
            exit;
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
                header("Location: " . url_admin_dashboard());
                exit;
            }
        
            // 🔹 Vérifier s'il y avait un produit en attente d'ajout au panier
            if (isset($_SESSION['pending_product'])) {
                $_POST = $_SESSION['pending_product']; // Restaurer les données
                unset($_SESSION['pending_product']); // Supprimer après ajout
                require_once '../public/add_to_cart.php'; // Use direct path to public directory
                header("Location: " . BASE_URL . "public/cart.php"); // Use absolute path for redirection
                exit();
            }
        
            // 🔹 Rediriger l'utilisateur vers index.php si aucune action spécifique
            header("Location: " . index());
            exit();
        } else {
            $_SESSION['error'] = "⚠️ Email ou mot de passe incorrect !";
            header("Location: ../public/login.php");
            exit();
        }
    }
}

