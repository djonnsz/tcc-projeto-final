<?php
session_start();

// Se não estiver logado, volta para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel</title>
</head>
<body>
    <!-- Aqui é a tela em branco para onde o usuário vai após login correto -->
</body>
</html>
