<?php
session_start();
include "script/conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($usuario === "" || $senha === "") {
        $erro = "Preencha o usuário e a senha.";
    } else {
        $sql = "SELECT id, nome, usuario, senha FROM usuarios WHERE usuario = ?";
        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $usuario);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $dados = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);

            // O SQL da atividade insere admin123 sem hash. Aceitamos essa senha inicial
            // e atualizamos para hash assim que ela for usada pela primeira vez.
            $valido = false;
            if ($dados) {
                $valido = password_verify($senha, $dados["senha"]) ||
                    hash_equals((string)$dados["senha"], (string)$senha);

                if ($valido && !password_verify($senha, $dados["senha"])) {
                    $hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt_hash = mysqli_prepare($conexao, "UPDATE usuarios SET senha = ? WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_hash, "si", $hash, $dados["id"]);
                    mysqli_stmt_execute($stmt_hash);
                    mysqli_stmt_close($stmt_hash);
                }
            }

            if ($valido) {
                session_regenerate_id(true);
                $_SESSION["usuario_id"] = (int)$dados["id"];
                $_SESSION["usuario_nome"] = $dados["nome"];
                header("Location: painel.php");
                exit;
            } else {
                $erro = "Usuário ou senha inválidos.";
            }
        } else {
            $erro = "Não foi possível verificar o login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Café &amp; Prosa</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
<main class="login-card">
    <div class="cup">☕</div>
    <p class="eyebrow">PAINEL ADMINISTRATIVO</p>
    <h1>Café &amp; Prosa</h1>
    <p class="muted">Entre para gerenciar o cardápio.</p>

    <?php if ($erro !== ""): ?>
        <div class="alert error"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" name="usuario" autocomplete="username" required>
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" autocomplete="current-password" required>
        <button class="button full" type="submit">Entrar no painel</button>
    </form>
    <p class="hint">Acesso inicial: <strong>admin</strong> / <strong>admin123</strong></p>
</main>
</body>
</html>
