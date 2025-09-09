<?php
// Arquivo: psicologos/confirmar_psicologo.php
include("../conexao.php");

$token = $_GET['token'] ?? '';
if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    exit("Token inválido.");
}

$sql = "SELECT * FROM email_verificacao_psicologos WHERE token=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    exit("Token inválido ou já utilizado.");
}
$pend = $res->fetch_assoc();

// Já existe confirmado?
$sqlChk = "SELECT 1 FROM psicologos WHERE email=? LIMIT 1";
$stmtChk = $conn->prepare($sqlChk);
$stmtChk->bind_param("s", $pend['email']);
$stmtChk->execute();
$jaExiste = $stmtChk->get_result()->num_rows > 0;
$stmtChk->close();

if ($jaExiste) {
    $del = $conn->prepare("DELETE FROM email_verificacao_psicologos WHERE id=?");
    $id = (int)$pend['id'];
    $del->bind_param("i", $id);
    $del->execute();
    exit("Este e-mail já foi confirmado. Faça login.");
}

// Move para tabela definitiva
$sqlIns = "INSERT INTO psicologos (nome,email,celular,crp,especialidade,senha) VALUES (?,?,?,?,?,?)";
$stmtIns = $conn->prepare($sqlIns);
$stmtIns->bind_param("ssssss", $pend['nome'],$pend['email'],$pend['celular'],$pend['crp'],$pend['especialidade'],$pend['senha']);

if ($stmtIns->execute()) {
    $del = $conn->prepare("DELETE FROM email_verificacao_psicologos WHERE id=?");
    $id = (int)$pend['id'];
    $del->bind_param("i", $id);
    $del->execute();
    echo "Cadastro confirmado! Agora você já pode <a href='login_psicologo.php'>fazer login</a>.";
} else {
    echo "Erro ao confirmar: " . $conn->error;
}
?>
