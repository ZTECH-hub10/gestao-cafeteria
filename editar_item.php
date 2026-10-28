<?php
session_start();
if (empty($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
include "script/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    $_SESSION["mensagem"] = "ID do item inválido.";
    header("Location: painel.php");
    exit;
}

$sql = "SELECT id, nome_item, categoria, descricao, preco FROM cardapio WHERE id = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$item) {
    $_SESSION["mensagem"] = "Item não encontrado.";
    header("Location: painel.php");
    exit;
}

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
        $item["nome_item"] = $nome_item;
        $item["categoria"] = $categoria;
        $item["descricao"] = $descricao;
        $item["preco"] = $preco_texto;
    } else {
        $preco = (float)$preco_texto;
        $sql_update = "UPDATE cardapio SET nome_item = ?, categoria = ?, descricao = ?, preco = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conexao, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "sssdi", $nome_item, $categoria, $descricao, $preco, $id);

        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            $_SESSION["mensagem"] = "Item atualizado com sucesso!";
            header("Location: painel.php");
            exit;
        } else {
            $erro = "Erro ao atualizar o item.";
        }
        mysqli_stmt_close($stmt_update);
    }
}
function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editar item | Café &amp; Prosa</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="painel.php">☕ Café &amp; Prosa</a>
    <div class="topbar-right"><span>Olá, <?= h($_SESSION["usuario_nome"] ?? "Gerente") ?></span><a class="button button-outline" href="logout.php">Sair</a></div>
</header>
<main class="container narrow">
    <a class="back-link" href="painel.php">← Voltar ao painel</a>
    <section class="panel">
        <div class="section-title"><div><p class="eyebrow">ATUALIZAÇÃO</p><h1>Editar item</h1><p class="muted">Altere os dados e salve as mudanças.</p></div></div>
        <?php if ($erro !== ""): ?><div class="alert error"><?= h($erro) ?></div><?php endif; ?>
        <form method="post" action="editar_item.php?id=<?= (int)$id ?>" class="form-grid">
            <div class="field field-wide"><label for="nome_item">Nome do item</label><input type="text" id="nome_item" name="nome_item" maxlength="100" value="<?= h($item["nome_item"]) ?>" required></div>
            <div class="field"><label for="categoria">Categoria</label><select id="categoria" name="categoria" required>
                <?php foreach (["Café", "Doce", "Salgado", "Bebida Gelada"] as $cat): ?>
                    <option value="<?= h($cat) ?>" <?= $item["categoria"] === $cat ? "selected" : "" ?>><?= h($cat) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="field"><label for="preco">Preço (R$)</label><input type="number" id="preco" name="preco" min="0" step="0.01" value="<?= h($item["preco"]) ?>" required></div>
            <div class="field field-wide"><label for="descricao">Descrição</label><textarea id="descricao" name="descricao" rows="4"><?= h($item["descricao"]) ?></textarea></div>
            <div class="field field-wide form-actions"><button class="button" type="submit">Salvar alterações</button><a class="button button-quiet" href="painel.php">Cancelar</a></div>
        </form>
    </section>
</main>
</body>
</html>
