<?php
session_start();
if (empty($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
include "script/conexao.php";

$mensagem = $_SESSION["mensagem"] ?? "";
unset($_SESSION["mensagem"]);
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_item = trim($_POST["nome_item"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco_texto = str_replace(",", ".", trim($_POST["preco"] ?? ""));
    $categorias = ["Café", "Doce", "Salgado", "Bebida Gelada"];

    if ($nome_item === "" || !in_array($categoria, $categorias, true) ||
        !is_numeric($preco_texto) || (float)$preco_texto < 0) {
        $erro = "Preencha os campos corretamente.";
    } else {
        $preco = (float)$preco_texto;
        $sql = "INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssd", $nome_item, $categoria, $descricao, $preco);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                $_SESSION["mensagem"] = "Item cadastrado com sucesso!";
                header("Location: painel.php");
                exit;
            } else {
                $erro = "Erro ao cadastrar o item.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $erro = "Erro ao preparar o cadastro.";
        }
    }
}

$sql_lista = "SELECT id, nome_item, categoria, descricao, preco, disponivel FROM cardapio ORDER BY id DESC";
$resultado = mysqli_query($conexao, $sql_lista);
$total = $resultado ? mysqli_num_rows($resultado) : 0;
function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Painel | Café &amp; Prosa</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="painel.php">☕ Café &amp; Prosa</a>
    <div class="topbar-right">
        <span>Olá, <?= h($_SESSION["usuario_nome"] ?? "Gerente") ?></span>
        <a class="button button-outline" href="logout.php">Sair</a>
    </div>
</header>

<main class="container">
    <section class="page-heading">
        <div><p class="eyebrow">ADMINISTRAÇÃO</p><h1>Gestão do cardápio</h1><p class="muted">Cadastre, consulte, edite e exclua os itens da cafeteria.</p></div>
        <div class="stat-card"><span>Itens cadastrados</span><strong><?= (int)$total ?></strong></div>
    </section>

    <?php if ($mensagem !== ""): ?><div class="alert success"><?= h($mensagem) ?></div><?php endif; ?>
    <?php if ($erro !== ""): ?><div class="alert error"><?= h($erro) ?></div><?php endif; ?>

    <section class="panel">
        <div class="section-title"><div><p class="eyebrow">NOVO PRODUTO</p><h2>Cadastrar item</h2></div></div>
        <form method="post" action="painel.php" class="form-grid">
            <div class="field field-wide"><label for="nome_item">Nome do item</label><input type="text" id="nome_item" name="nome_item" maxlength="100" placeholder="Ex.: Cappuccino cremoso" required></div>
            <div class="field"><label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Selecione</option>
                    <option value="Café">Café</option>
                    <option value="Doce">Doce</option>
                    <option value="Salgado">Salgado</option>
                    <option value="Bebida Gelada">Bebida Gelada</option>
                </select>
            </div>
            <div class="field"><label for="preco">Preço (R$)</label><input type="number" id="preco" name="preco" min="0" step="0.01" placeholder="0.00" required></div>
            <div class="field field-wide"><label for="descricao">Descrição</label><textarea id="descricao" name="descricao" rows="3" placeholder="Detalhes do item"></textarea></div>
            <div class="field field-wide"><button class="button" type="submit">＋ Cadastrar item</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="section-title"><div><p class="eyebrow">SEU MENU</p><h2>Itens do cardápio</h2></div></div>
        <?php if ($resultado && $total > 0): ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Produto</th><th>Categoria</th><th>Descrição</th><th>Preço</th><th>Ações</th></tr></thead>
                <tbody>
                <?php while ($item = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td class="product-name"><?= h($item["nome_item"]) ?></td>
                        <td><span class="badge"><?= h($item["categoria"]) ?></span></td>
                        <td class="description"><?= h($item["descricao"] ?: "—") ?></td>
                        <td class="price">R$ <?= number_format((float)$item["preco"], 2, ",", ".") ?></td>
                        <td><div class="actions">
                            <a class="button button-small button-edit" href="editar_item.php?id=<?= (int)$item["id"] ?>">Editar</a>
                            <form method="post" action="excluir_item.php" onsubmit="return confirm('Deseja realmente excluir este item?');">
                                <input type="hidden" name="id" value="<?= (int)$item["id"] ?>">
                                <button class="button button-small button-delete" type="submit">Excluir</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><span>☕</span><h3>Nenhum item cadastrado</h3><p>Use o formulário acima para adicionar o primeiro produto.</p></div>
        <?php endif; ?>
    </section>
    <footer>Feito com carinho · Café &amp; Prosa</footer>
</main>
</body>
</html>
