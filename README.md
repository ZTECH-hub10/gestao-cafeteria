# Gestão de Cafeteria — PHP procedural + MySQLi

Projeto acadêmico com login protegido e CRUD de itens do cardápio.

## Como executar no XAMPP
1. Extraia a pasta `gestao_cafeteria_professor` para `C:\xampp\htdocs\`.
2. Inicie Apache e MySQL no XAMPP.
3. Abra o phpMyAdmin e importe o arquivo `database.sql`.
4. Se necessário, ajuste usuário/senha do MySQL em `script/conexao.php`.
5. Acesse `http://localhost/gestao_cafeteria_professor/login.php`.

## Acesso inicial
Usuário: `admin`
Senha: `admin123`

## Arquivos
- `login.php`: autenticação.
- `painel.php`: cadastro e listagem.
- `editar_item.php`: edição de item existente.
- `excluir_item.php`: exclusão de item via POST.
- `logout.php`: encerra a sessão.
- `script/conexao.php`: conexão MySQLi.
- `script/proteger.php`: exemplo auxiliar de proteção por sessão.
- `database.sql`: criação do banco e tabelas.
- `style.css`: estilo visual.

## Observações
- O código usa PHP procedural e MySQLi, seguindo o padrão de consultas preparadas visto em aula.
- Consultas que recebem dados do usuário usam prepared statements.
- A exclusão é feita por POST com confirmação no navegador.
- O SQL da atividade cria a senha padrão como texto simples; no primeiro login, o sistema converte essa senha em hash. Para uso real, altere a senha padrão.
