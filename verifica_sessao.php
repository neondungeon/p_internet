<?php
// verifica_sessao.php - Guard reusable
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// session cookie safety
ini_set('session.cookie_httponly', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

// Check if user is logged in
if (!isset($_SESSION['usuario'])) {
    $_SESSION['flash_message'] = 'Sessão ausente ou expirada. Faça login.';
    header('Location: index.php');
    exit;
}

// If a specific profile is required by the page, the page should set $requiredProfile before requiring this file.
if (isset($requiredProfile)) {
    if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== $requiredProfile) {
        header('Location: sem_permissao.php');
        exit;
    }
}
