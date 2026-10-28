<?php
session_start();
if (empty($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
include "script/conexao.php";

// Exclusão por POST, com confirmação no painel, é mais segura do que apagar via GET.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: painel.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if ($id && $id > 0) {
    $sql = "DELETE FROM cardapio WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $excluidos = mysqli_stmt_affected_rows($stmt);
            $_SESSION["mensagem"] = $excluidos > 0
                ? "Item excluído com sucesso!"
                : "Nenhum item encontrado com esse ID.";
        } else {
            $_SESSION["mensagem"] = "Erro ao excluir o item.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION["mensagem"] = "Erro ao preparar a exclusão.";
    }
} else {
    $_SESSION["mensagem"] = "ID inválido.";
}

header("Location: painel.php");
exit;
