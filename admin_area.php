<?php
$requiredProfile = 'admin';
require_once 'verifica_sessao.php';

$displayUser = htmlspecialchars($_SESSION['usuario']);
?>
<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Admin Area</title></head>
<body>
<h2>Admin Area</h2>
<p>Olá, administrador <?php echo $displayUser; ?></p>
<p><a href="dashboard.php">Voltar</a></p>
</body></html>
