<?php
session_start();
include("../conexao.php");

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$sql = "SELECT * FROM psicologos WHERE email=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($senha, $user['senha'])) {
        $_SESSION['psicologo_id'] = $user['id'];
        $_SESSION['psicologo_nome'] = $user['nome'];
        header("Location: painel_psicologo.php"); // 👉 abre a tela em branco
        exit;
    }
}

header("Location: login_psicologo.php?erro=1");
exit;
