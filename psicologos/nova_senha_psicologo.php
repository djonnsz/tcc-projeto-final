<?php
include("../conexao.php");

if (!isset($_GET['token'])) {
    die("Token inválido.");
}

$token = $_GET['token'];
$sql = "SELECT * FROM reset_senhas WHERE token = ? AND tipo_usuario='psicologo'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Token inválido ou expirado.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Redefinir Senha</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: linear-gradient(-135deg, #131313, #26baff);
      font-family: Arial, sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }
    .container {
      background-color: rgba(0, 0, 0, 0.6);
      padding: 30px;
      border-radius: 12px;
      width: 100%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 0 15px rgba(0,0,0,0.5);
    }
    h2 {
      margin-bottom: 20px;
      color: #26baff;
    }
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: none;
      border-radius: 5px;
      font-size: 14px;
    }
    button {
      width: 100%;
      padding: 12px;
      margin-top: 15px;
      background-color: #26baff;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      background-color: #1e90ff;
      color: white;
    }
    .links {
      margin-top: 15px;
    }
    .links a {
      color: #26baff;
      text-decoration: none;
      font-size: 14px;
    }
    .links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Redefinir Senha</h2>
    <form action="processa_nova_senha_psicologo.php" method="POST">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <input type="password" name="senha" placeholder="Nova senha" required>
      <input type="password" name="confirma" placeholder="Confirme a nova senha" required>
      <button type="submit">Alterar senha</button>
    </form>
    <div class="links">
      <p><a href="login_psicologo.php">Voltar para o login</a></p>
    </div>
  </div>
</body>
</html>
