<?php
session_start();
include("conexao.php");

$usuario_id = $_SESSION['reset_usuario_id'] ?? '';
$reset_id = $_SESSION['reset_id'] ?? '';
$token = $_GET['token'] ?? '';

if (!$usuario_id || !$reset_id) {
    $_SESSION['msg_nova_senha'] = ['texto'=>"Ação inválida.", 'tipo'=>"erro"];
    header("Location: nova_senha.php?token=".$token);
    exit();
}

$senha = $_POST['senha'] ?? '';
$confirma = $_POST['confirma_senha'] ?? '';

if (!$senha || !$confirma) {
    $_SESSION['msg_nova_senha'] = ['texto'=>"Preencha todos os campos.", 'tipo'=>"erro"];
    header("Location: nova_senha.php?token=".$token);
    exit();
}

if ($senha !== $confirma) {
    $_SESSION['msg_nova_senha'] = ['texto'=>"As senhas não coincidem.", 'tipo'=>"erro"];
    header("Location: nova_senha.php?token=".$token);
    exit();
}

// Atualiza senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE usuarios SET senha=? WHERE id=?");
$stmt->bind_param("si", $senhaHash, $usuario_id);
$stmt->execute();
$stmt->close();

// Marca token como usado
$stmt2 = $conn->prepare("UPDATE reset_senhas SET usado=1 WHERE id=?");
$stmt2->bind_param("i", $reset_id);
$stmt2->execute();
$stmt2->close();

// Limpa sessões temporárias
unset($_SESSION['reset_usuario_id']);
unset($_SESSION['reset_id']);

// Mensagem de sucesso
$_SESSION['msg_nova_senha'] = ['texto'=>"Senha redefinida com sucesso! Faça login.", 'tipo'=>"sucesso"];
header("Location: login.php");
exit();
?>
