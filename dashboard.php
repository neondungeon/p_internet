<?php
$requiredProfile = null; // no specific profile required; set to 'admin' on pages that need it
require_once 'verifica_sessao.php';

// sanitize output
$displayUser = htmlspecialchars($_SESSION['usuario']);
$displayProfile = htmlspecialchars($_SESSION['perfil']);
?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Dashboard</title></head>
<body>
<h2>Dashboard</h2>
<p>Bem-vindo, <?php echo $displayUser; ?> (perfil: <?php echo $displayProfile; ?>)</p>
<p><a href="items.php">Gerenciar itens</a></p>
<p><a href="logout.php">Sair</a></p>
<?php if ($_SESSION['perfil'] === 'admin'): ?>
    <p><a href="admin_area.php">Área do Admin</a></p>
<?php endif; ?>
</body>
</html>
