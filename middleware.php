<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentPage = basename($_SERVER['PHP_SELF']);
    // Pages that are allowed to receive POST requests by ANY logged-in user
    $allowed_for_all = ['alterar_senha.php', 'login.php'];
    
    if (!in_array($currentPage, $allowed_for_all)) {
        if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'Administrador') {
            die("Acesso negado: Apenas administradores podem realizar esta operacao.");
        }
    }
}
