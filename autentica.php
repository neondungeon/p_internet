<?php
require_once 'db.php';
session_start();

ini_set('session.cookie_httponly', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

// limit failed attempts per session
$maxFailedAttempts = 5;
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

$email      = isset($_POST['email']) ? trim($_POST['email']) : '';
$password   = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    $_SESSION['flash_message'] = 'Campos obrigatórios: e-mail e senha.';
    header('Location: index.php');
    exit;
}

if ($_SESSION['failed_attempts'] >= $maxFailedAttempts) {
    $_SESSION['flash_message'] = 'Bloqueado temporariamente por muitas tentativas falhas. Tente novamente mais tarde.';
    header('Location: index.php');
    exit;
}

try {
    $statement = $pdo->prepare('SELECT id, email, senha_hash, perfil FROM usuarios WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if ($user && password_verify($password, $user['senha_hash'])) {
        // successful login
        session_regenerate_id(true);
        // RF2
        $_SESSION['usuario']            = $user['email'];
        $_SESSION['perfil']             = $user['perfil'];
        $_SESSION['failed_attempts']    = 0;
        $_SESSION['flash_message']      = 'Login realizado com sucesso.';
        header('Location: dashboard.php');
        exit;
    } else {
        // failed login
        $_SESSION['failed_attempts']   += 1;
        $remaining = $maxFailedAttempts - $_SESSION['failed_attempts'];
        $_SESSION['flash_message']      = 'Credenciais inválidas. Tentativas restantes: ' . $remaining;
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['flash_message'] = 'Erro no servidor. Tente novamente.';
    header('Location: index.php');
    exit;
}
