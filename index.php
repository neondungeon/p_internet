<?php
// index.php - Login form
session_start();

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login</title>
<style>body{font-family:Arial,Helvetica,sans-serif;max-width:540px;margin:40px auto;padding:20px}input{display:block;margin:8px 0;padding:8px;width:100%}</style>
</head>
<body>
<h2>Login</h2>
<?php if ($message): ?>
    <div style="padding:10px;border:1px solid #ccc;margin-bottom:10px;"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>
<form method="post" action="autentica.php" novalidate>
    <label for="email">E-mail</label>
    <input id="email" name="email" type="email" required>
    <label for="password">Senha</label>
    <input id="password" name="password" type="password" required>
    <button type="submit">Entrar</button>
</form>
</body>
</html>
