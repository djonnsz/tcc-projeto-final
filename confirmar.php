<?php
// Arquivo: confirmar.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexao.php");

$token = $_GET['token'] ?? '';
if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
  exit("Token inválido.");
}

// Busca o pré-cadastro
$sql = "SELECT * FROM email_verificacao WHERE token = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
  exit("Token inválido ou já utilizado.");
}

$pend = $res->fetch_assoc();

// Confere se por acaso já existe esse email em 'usuarios' (evita duplicar)
$sqlChk = "SELECT 1 FROM usuarios WHERE email = ? LIMIT 1";
$stmtChk = $conn->prepare($sqlChk);
$stmtChk->bind_param("s", $pend['email']);
$stmtChk->execute();
$jaExiste = $stmtChk->get_result()->num_rows > 0;
$stmtChk->close();

if ($jaExiste) {
  // Só limpa o pendente
  $del = $conn->prepare("DELETE FROM email_verificacao WHERE id = ?");
  $id = (int)$pend['id'];
  $del->bind_param("i", $id);
  $del->execute();
  exit("Este e-mail já foi confirmado anteriormente. Você já pode fazer login.");
}

// Move para 'usuarios'
$sqlIns = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
$stmtIns = $conn->prepare($sqlIns);
$stmtIns->bind_param("sss", $pend['nome'], $pend['email'], $pend['senha']);

if ($stmtIns->execute()) {
  // Remove o pendente
  $del = $conn->prepare("DELETE FROM email_verificacao WHERE id = ?");
  $id = (int)$pend['id'];
  $del->bind_param("i", $id);
  $del->execute();

  echo "Cadastro confirmado com sucesso! Agora você já pode <a href='login.php'>fazer login</a>.";
} else {
  echo "Erro ao confirmar cadastro: " . $conn->error;
}
