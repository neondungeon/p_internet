<?php
require_once 'verifica_sessao.php';
require_once 'db.php';

$editing = false;
$itemId = null;
$title = '';
$content = '';

// Variáveis para endereço (serão preenchidas pelo JavaScript via ViaCEP se o usuário fornecer o CEP)
$cep = '';
$logradouro = '';
$bairro = '';
$localidade = '';
$uf = '';

// Se existe id via GET, carregar o item para edição
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $itemId = (int) $_GET['id'];
    try {
        // Atenção: a tabela original pode não ter colunas de endereço. Aqui tentamos ler, 
        // mas usamos coalescência para evitar warnings se as chaves não existirem.
        $stmt = $pdo->prepare('SELECT id, title, content, created_by, cep, logradouro, bairro, localidade, uf FROM items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $itemId]);
        $item = $stmt->fetch();

        if ($item) {
            $editing = true;
            $title = $item['title'] ?? '';
            $content = $item['content'] ?? '';
            $cep = $item['cep'] ?? '';
            $logradouro = $item['logradouro'] ?? '';
            $bairro = $item['bairro'] ?? '';
            $localidade = $item['localidade'] ?? '';
            $uf = $item['uf'] ?? '';
        } else {
            // Item não encontrado
            header('Location: items.php');
            exit;
        }
    } catch (Exception $e) {
        // Tratamento simples de erro (log opcional)
        error_log('Erro ao carregar item para edição: ' . $e->getMessage());
        header('Location: items.php');
        exit;
    }
}

// Ação do formulário (criar/editar) - mantém o action original do projeto
$actionUrl = $editing ? "item_action.php?action=edit&id={$itemId}" : "item_action.php?action=create";
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title><?php echo $editing ? 'Editar item' : 'Criar item'; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        /* Estilos simples in-line para não depender de arquivos externos */
        body { font-family: Arial, sans-serif; margin: 20px; max-width:900px; }
        label { display:block; margin-top:10px; font-weight:600; }
        input[type="text"], input[type="email"], textarea { width:100%; padding:8px; box-sizing:border-box; margin-top:4px; }
        .inline { display:inline-block; vertical-align:middle; }
        .small { font-size:0.9em; color:#666; }
        .cep-row { display:flex; gap:8px; align-items:center; }
        .cep-row input { flex:1; }
        .btn { padding:8px 12px; cursor:pointer; }
        .notice { margin-top:10px; color:#b00; }
    </style>
</head>
<body>
    <h1><?php echo $editing ? 'Editar item' : 'Criar item'; ?></h1>

    <form id="itemForm" method="post" action="<?php echo htmlspecialchars($actionUrl); ?>" novalidate>
        <?php if ($editing): ?>
            <p class="small">Editando item ID: <?php echo htmlspecialchars($itemId); ?></p>
        <?php endif; ?>

        <label for="title">Título *</label>
        <input id="title" name="title" type="text" required value="<?php echo htmlspecialchars($title); ?>">

        <label for="content">Conteúdo</label>
        <textarea id="content" name="content" rows="8"><?php echo htmlspecialchars($content); ?></textarea>

        <!-- Campos de CEP / Endereço integrados com ViaCEP -->
        <h3>Endereço (preenchimento via CEP)</h3>

        <label for="cep">CEP</label>
        <div class="cep-row">
            <input id="cep" name="cep" type="text" maxlength="9" placeholder="00000-000 ou 00000000" value="<?php echo htmlspecialchars($cep); ?>">
            <button type="button" id="buscarCepBtn" class="btn">Buscar</button>
        </div>
        <p class="small">Informe o CEP e clique em "Buscar" ou saia do campo para preencher automaticamente.</p>

        <label for="logradouro">Logradouro</label>
        <input id="logradouro" name="logradouro" type="text" value="<?php echo htmlspecialchars($logradouro); ?>">

        <label for="bairro">Bairro</label>
        <input id="bairro" name="bairro" type="text" value="<?php echo htmlspecialchars($bairro); ?>">

        <label for="localidade">Cidade</label>
        <input id="localidade" name="localidade" type="text" value="<?php echo htmlspecialchars($localidade); ?>">

        <label for="uf">UF</label>
        <input id="uf" name="uf" type="text" maxlength="2" value="<?php echo htmlspecialchars($uf); ?>">

        <div id="cepMessage" class="notice" role="status" aria-live="polite" style="display:none;"></div>

        <button type="submit" class="btn"><?php echo $editing ? 'Salvar alterações' : 'Criar item'; ?></button>
        &nbsp;<a href="items.php">Cancelar</a>
        <p class="small">Campos obrigatórios marcados com *</p>
    </form>

<script>
/*
  Integração com ViaCEP (GET)
  - Endpoint usado: https://viacep.com.br/ws/{cep}/json/
  - Formato esperado: JSON
  - Implementação: fetch() nativo do navegador, tratamento de erros e preenchimento dos campos.
*/

(function(){
    const cepInput = document.getElementById('cep');
    const buscarBtn = document.getElementById('buscarCepBtn');
    const msgEl = document.getElementById('cepMessage');

    function showMessage(text, isError) {
        msgEl.style.display = 'block';
        msgEl.textContent = text;
        msgEl.style.color = isError ? '#b00' : '#080';
    }

    function clearMessage() {
        msgEl.style.display = 'none';
        msgEl.textContent = '';
    }

    function sanitizeCep(value) {
        return (value || '').replace(/\D/g, '');
    }

    function preencherEndereco(data) {
        document.getElementById('logradouro').value = data.logradouro || '';
        document.getElementById('bairro').value = data.bairro || '';
        document.getElementById('localidade').value = data.localidade || '';
        document.getElementById('uf').value = data.uf || '';
    }

    async function buscarCep(cepRaw) {
        clearMessage();
        const cep = sanitizeCep(cepRaw);
        if (!cep) {
            showMessage('CEP vazio.', true);
            return;
        }
        if (!/^[0-9]{8}$/.test(cep)) {
            showMessage('CEP inválido. Deve conter 8 dígitos numéricos.', true);
            return;
        }

        // Endpoint ViaCEP (public, sem autenticação)
        const url = `https://viacep.com.br/ws/${cep}/json/`;

        try {
            showMessage('Buscando endereço...', false);
            const resp = await fetch(url, { method: 'GET' });
            if (!resp.ok) {
                throw new Error('Falha na requisição. HTTP ' + resp.status);
            }
            const data = await resp.json();

            // ViaCEP indica erro com { "erro": true }
            if (data.erro) {
                preencherEndereco({}); // limpa
                showMessage('CEP não encontrado.', true);
                return;
            }

            preencherEndereco(data);
            showMessage('Endereço preenchido com sucesso.', false);
            // Opcional: mover foco para o próximo campo
            document.getElementById('logradouro').focus();

        } catch (err) {
            console.error('Erro ao consultar ViaCEP:', err);
            showMessage('Erro ao consultar ViaCEP. Tente novamente mais tarde.', true);
        }
    }

    // Evento: clique no botão Buscar
    buscarBtn.addEventListener('click', function(e){
        e.preventDefault();
        buscarCep(cepInput.value);
    });

    // Evento: ao sair do campo CEP (blur), faz a busca automaticamente
    cepInput.addEventListener('blur', function(){
        const cep = sanitizeCep(cepInput.value);
        if (cep.length === 8) {
            buscarCep(cep);
        }
    });

    // Permitir o usuário enviar com o CEP formatado (xxxxx-xxx) mas sanitizar ao submeter
    document.getElementById('itemForm').addEventListener('submit', function(){
        // normaliza para apenas números antes do envio (será enviado como 'cep' contendo só dígitos)
        cepInput.value = sanitizeCep(cepInput.value);
        // Nota: se desejar persistir os campos de endereço no banco, garanta que o backend (item_action.php)
        // esteja preparado para salvar cep, logradouro, bairro, localidade e uf.
    });
})();
</script>
</body>
</html>
