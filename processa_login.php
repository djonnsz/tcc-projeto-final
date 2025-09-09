<?php
session_start();
include("conexao.php");

// Pega os dados do formulário
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Valida campos
if (!$email || !$senha) {
    $_SESSION['erro'] = "Preencha e-mail e senha.";
    header("Location: login.php");
    exit();
}

// Busca usuário no banco
$sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['erro'] = "Usuário ou senha incorreto.";
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

// Verifica senha
if (password_verify($senha, $user['senha'])) {
    // Login correto → cria sessão
    $_SESSION['usuario_id'] = $user['id'];

    // Redireciona para a tela em branco (painel.php)
    header("Location: painel.php");
    exit();
} else {
    $_SESSION['erro'] = "Usuário ou senha incorreto.";
    header("Location: login.php");
    exit();
}
?>
