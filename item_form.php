<?php
require_once 'verifica_sessao.php';
require_once 'db.php';

$editing = false;
$itemId = null;
$title = '';
$content = '';

// Se existe id via GET, carregar o item para edição
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $itemId = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare('SELECT id, title, content, created_by FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        $item = $stmt->fetch();
        if ($item) {
            $editing = true;
            $title = $item['title'];
            $content = $item['content'];
            // Checar se o usuário tem permissão para editar
            if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] !== 'admin' && $_SESSION['usuario'] !== $item['created_by'])) {
                $_SESSION['flash_message'] = 'Você não tem permissão para editar este item.';
                header('Location: items.php');
                exit;
            }
        } else {
            $_SESSION['flash_message'] = 'Item não encontrado.';
            header('Location: items.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Erro ao carregar item.';
        header('Location: items.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?php echo $editing ? 'Editar Item' : 'Criar Item'; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body{font-family:Arial,Helvetica,sans-serif;max-width:700px;margin:30px auto;padding:10px}
        label{display:block;margin-top:8px}
        input[type="text"], textarea{width:100%;padding:8px;margin-top:4px}
        button{margin-top:10px;padding:8px 12px}
        .small{font-size:90%;color:#666}
    </style>
</head>
<body>
    <h2><?php echo $editing ? 'Editar Item' : 'Criar Item'; ?></h2>

    <form method="post" action="item_action.php" novalidate>
        <input type="hidden" name="action" value="save">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($itemId); ?>">
        <?php endif; ?>

        <label for="title">Título *</label>
        <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars($title); ?>">

        <label for="content">Conteúdo</label>
        <textarea id="content" name="content" rows="8"><?php echo htmlspecialchars($content); ?></textarea>

        <button type="submit"><?php echo $editing ? 'Salvar alterações' : 'Criar item'; ?></button>
        &nbsp;<a href="items.php">Cancelar</a>
        <p class="small">Campos obrigatórios marcados com *</p>
    </form>
</body>
</html>
