<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Acesso Negado</title></head>
<body>
<h2>Acesso Negado</h2>
<p>Você não tem permissão para acessar esta página.</p>
<p><a href="index.php">Voltar ao login</a></p>
</body></html>
