<?php
// gerenciador de ações
require_once 'verifica_sessao.php';
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: items.php');
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'save') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $id = (isset($_POST['id']) && ctype_digit($_POST['id'])) ? (int)$_POST['id'] : null;

    if ($title === '') {
        $_SESSION['flash_message'] = 'O campo título é obrigatório.';
        if ($id) {
            header('Location: item_form.php?id=' . urlencode($id));
        } else {
            header('Location: item_form.php');
        }
        exit;
    }

    try {
        if ($id) {
            $stmtCheck = $pdo->prepare('SELECT created_by FROM items WHERE id = :id LIMIT 1');
            $stmtCheck->execute(['id' => $id]);
            $existing = $stmtCheck->fetch();
            if (!$existing) {
                $_SESSION['flash_message'] = 'Item não encontrado.';
                header('Location: items.php');
                exit;
            }
            if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] !== 'admin' && $_SESSION['usuario'] !== $existing['created_by'])) {
                $_SESSION['flash_message'] = 'Você não tem permissão para editar este item.';
                header('Location: items.php');
                exit;
            }

            $stmt = $pdo->prepare('UPDATE items SET title = :title, content = :content WHERE id = :id');
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'id' => $id
            ]);
            $_SESSION['flash_message'] = 'Item atualizado com sucesso.';
            header('Location: items.php');
            exit;
        } else {
            $stmt = $pdo->prepare('INSERT INTO items (title, content, created_by) VALUES (:title, :content, :created_by)');
            $stmt->execute([
                'title' => $title,
                'content' => $content,
                'created_by' => isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null
            ]);
            $_SESSION['flash_message'] = 'Item criado com sucesso.';
            header('Location: items.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Erro ao salvar item.';
        header('Location: items.php');
        exit;
    }
} elseif ($action === 'delete') {
    $id = (isset($_POST['id']) && ctype_digit($_POST['id'])) ? (int)$_POST['id'] : null;
    if (!$id) {
        $_SESSION['flash_message'] = 'ID inválido para exclusão.';
        header('Location: items.php');
        exit;
    }

    try {
        // propriedade/permissão
        $stmtCheck = $pdo->prepare('SELECT created_by FROM items WHERE id = :id LIMIT 1');
        $stmtCheck->execute(['id' => $id]);
        $existing = $stmtCheck->fetch();
        if (!$existing) {
            $_SESSION['flash_message'] = 'Item não encontrado.';
            header('Location: items.php');
            exit;
        }

        if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] !== 'admin' && $_SESSION['usuario'] !== $existing['created_by'])) {
            $_SESSION['flash_message'] = 'Você não tem permissão para excluir este item.';
            header('Location: items.php');
            exit;
        }

        $stmt = $pdo->prepare('DELETE FROM items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $_SESSION['flash_message'] = 'Item removido.';
        header('Location: items.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Erro ao excluir item.';
        header('Location: items.php');
        exit;
    }
} else {
    header('Location: items.php');
    exit;
}
