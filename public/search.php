<?php
require_once '../lib/url.php';
require_once path_lib_db();


$pdo = getDatabaseConnection();

if (isset($_POST['search']) && !empty($_POST['search'])) {
    $search = "%" . trim($_POST['search']) . "%";
    $stmt = $pdo->prepare("SELECT title FROM products WHERE title LIKE :search LIMIT 5");
    $stmt->execute(['search' => $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        echo "<p class='suggestion p-2 border-b hover:bg-gray-200 cursor-pointer'>" . htmlspecialchars($row['title']) . "</p>";
    }
}
?>
