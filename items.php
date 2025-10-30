<?php
// listagem
require_once 'verifica_sessao.php';
require_once 'db.php';

$flashMessage = '';
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

try {
    $statement = $pdo->query('SELECT id, title, created_by, created_at FROM items ORDER BY created_at DESC');
    $items = $statement->fetchAll();
} catch (Exception $e) {
    // Em produção preferir logar o erro
    $_SESSION['flash_message'] = 'Erro ao carregar itens.';
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Gerenciar Itens</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body{font-family:Arial,Helvetica,sans-serif;max-width:900px;margin:30px auto;padding:10px}
        table{width:100%;border-collapse:collapse}
        th,td{padding:8px;border:1px solid #ddd;text-align:left}
        a.button{display:inline-block;padding:6px 10px;background:#28a745;color:#fff;text-decoration:none;border-radius:4px}
        form.inline{display:inline}
        .flash{padding:10px;border:1px solid #ccc;margin-bottom:10px;background:#f9f9f9}
    </style>
</head>
<body>
    <h2>Itens</h2>

    <?php if ($flashMessage): ?>
        <div class="flash"><?php echo htmlspecialchars($flashMessage); ?></div>
    <?php endif; ?>

    <p><a class="button" href="item_form.php">Criar novo item</a> &nbsp; <a href="dashboard.php">Voltar ao dashboard</a></p>

    <?php if (empty($items)): ?>
        <p>Nenhum item encontrado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Criado por</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['created_by']); ?></td>
                    <td><?php echo htmlspecialchars($item['created_at']); ?></td>
                    <td>
                        <a href="item_form.php?id=<?php echo urlencode($item['id']); ?>">Editar</a>
                        &nbsp;|&nbsp;
                        <form class="inline" method="post" action="item_action.php" onsubmit="return confirm('Confirma exclusão deste item?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($item['id']); ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
