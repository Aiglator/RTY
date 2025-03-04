<!-- ce fichier et inutile je l'avais coder pour mais il ne marche pas sur une potentielle prochaine version je compte le modifier Dévelloppeur:Rayan Chattaoui -->

<?php
$protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
$baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

$baseUrl = rtrim($baseUrl, '/');

function url($path) {
    global $baseUrl;
    return $baseUrl . '/' . ltrim($path, '/');
}
?>
