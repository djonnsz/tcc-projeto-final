<?php
session_start();
include("conexao.php");

$token = $_GET['token'] ?? '';
$msg = '';
$tipo_msg = ''; // "erro" ou "sucesso"

if (isset($_SESSION['msg_nova_senha'])) {
    $msg = $_SESSION['msg_nova_senha']['texto'] ?? '';
    $tipo_msg = $_SESSION['msg_nova_senha']['tipo'] ?? '';
    unset($_SESSION['msg_nova_senha']);
}

if (!$token) {
    $msg = "Token inválido.";
    $tipo_msg = "erro";
} else {
    // Busca token válido e não expirado
    $sql = "SELECT rs.id AS reset_id, u.id AS usuario_id, u.nome 
            FROM reset_senhas rs
            JOIN usuarios u ON u.id = rs.usuario_id
            WHERE rs.token = ? AND rs.expira_em > NOW() AND rs.usado = 0
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $msg = "Token inválido ou expirado.";
        $tipo_msg = "erro";
    } else {
        $user = $result->fetch_assoc();
        $_SESSION['reset_usuario_id'] = $user['usuario_id'];
        $_SESSION['reset_id'] = $user['reset_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Redefinir Senha - TCC</title>
<style>
body {
  margin:0;
  padding:0;
  min-height:100vh;
  background: linear-gradient(-135deg, #131313, #26baff);
  font-family: Arial, sans-serif;
  color: white;
  display:flex;
  align-items:center;
  justify-content:center;
}
.container {
  background-color: rgba(0,0,0,0.5);
  padding:40px;
  border-radius:10px;
  width:100%;
  max-width:400px;
  box-shadow:0 0 10px rgba(0,0,0,0.4);
}
input[type="password"] { width:100%; padding:12px; margin-bottom:10px; border:none; border-radius:5px; }
button { width:100%; padding:12px; background-color:#26baff; border:none; border-radius:5px; color:black; font-weight:bold; cursor:pointer; }
button:hover { background-color:#1e90ff; color:white; }
.msg { text-align:center; margin-bottom:10px; padding:5px; border-radius:5px; }
.msg.erro { color:#ff4d4d; background: rgba(255, 0, 0, 0.2); }
.msg.sucesso { color:#00ff99; background: rgba(0, 255, 128, 0.2); }
</style>
</head>
<body>
<div class="container">
  <h2>Redefinir Senha</h2>

  <?php if($msg): ?>
      <div class="msg <?php echo $tipo_msg; ?>"><?php echo $msg; ?></div>
  <?php endif; ?>

  <?php if(isset($_SESSION['reset_usuario_id'])): ?>
  <form action="processa_nova_senha.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
    <input type="password" name="senha" placeholder="Nova senha" required>
    <input type="password" name="confirma_senha" placeholder="Confirmar senha" required>
    <button type="submit">Redefinir Senha</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
