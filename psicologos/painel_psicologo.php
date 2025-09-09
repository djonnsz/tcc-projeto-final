<?php
session_start();
if (!isset($_SESSION['psicologo_id'])) {
    header("Location: login_psicologo.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel Psicólogo</title>
</head>
<body>
  <!-- 👉 Aqui está a tela em branco do painel -->
</body>
</html>
