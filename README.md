## Requisitos

- PHP 7.4+ (com PDO MySQL)
- MySQL / MariaDB
- Servidor web (Apache, Nginx) ou PHP built-in server

## Arquivos principais

- `index.php`: formulário de login.
- `autentica.php`: valida credenciais, inicia sessão e `session_regenerate_id(true)`.
- `verifica_sessao.php`: guard reutilizável para páginas restritas.
- `dashboard.php`: página restrita (requer login).
- `admin_area.php`: exemplo de página que exige perfil `admin`.
- `logout.php`: encerra sessão.
- `sem_permissao.php`: mensagem de acesso negado.
- `db.php`: configuração da conexão PDO.
- `setup.sql`: script SQL para criar tabela e inserir um usuário de teste.

## Usuário de teste

- E-mail:   `test@example.com`
- Senha:    `Test@123!`

## Passos para rodar

1. Crie um banco de dados MySQL chamado `auth_demo` (ou altere `db.php` para usar outro nome).
2. Importe o arquivo `setup.sql` no banco de dados. Exemplo via terminal:
   ```bash
   mysql -u root -p auth_demo < setup.sql
   ```

3. Configure as credenciais do banco em `db.php` (host, database, user, password).
4. Coloque os arquivos em um servidor PHP + MySQL e acesse `index.php`.
   - Alternativa rápida (PHP built-in): `php -S 127.0.0.1:8000` no diretório do projeto.
5. Faça login com o usuário de teste acima.

## Fluxo implementado

1. `index.php` envia e-mail/senha para `autentica.php` via POST.
2. `autentica.php` compara credenciais usando PDO + prepared statement e `password_verify()`.
3. Se válido: `session_regenerate_id(true)` é chamado; `$_SESSION['usuario']` e `$_SESSION['perfil']` são definidos; redirect para `dashboard.php`.
4. Páginas restritas devem `require_once 'verifica_sessao.php'` no topo. O `verifica_sessao.php` redireciona para `index.php` se `$_SESSION['usuario']` não existir e redireciona para `sem_permissao.php` caso o perfil requerido (definido pela página) não corresponda.
5. `logout.php` encerra a sessão e redireciona para `index.php`.

## Observações de segurança

- Senhas armazenadas com `password_hash()` e verificadas com `password_verify()`.
- Uso de PDO com prepared statements para prevenir SQL injection.
- `session_regenerate_id(true)` após login para mitigar fixation.
- `session.cookie_httponly` habilitado; `session.cookie_secure` habilitado se HTTPS detectado.
- Limite de tentativas de login por sessão (5 tentativas).
