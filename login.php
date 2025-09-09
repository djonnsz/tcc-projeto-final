<?php
session_start();
$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['erro']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - TCC</title>
  <style>
    /* Todo seu CSS original permanece igual */
    body { margin:0; padding:0; min-height:100vh; background:linear-gradient(-135deg, #131313, #26baff); font-family:Arial,sans-serif; color:white; display:flex; align-items:center; justify-content:center; }
    .login-container { background-color: rgba(0,0,0,0.5); padding:40px; border-radius:10px; width:100%; max-width:400px; box-shadow:0 0 10px rgba(0,0,0,0.4); }
    h2 { text-align:center; margin-bottom:30px; }
    input[type="email"], input[type="password"] { width:100%; padding:12px; margin-bottom:5px; border:none; border-radius:5px; }
    .error-msg { color:#ff4d4d; font-size:0.9em; margin-bottom:15px; }
    button { width:100%; padding:12px; background-color:#26baff; border:none; border-radius:5px; color:black; font-weight:bold; cursor:pointer; transition:0.3s; }
    button:hover { background-color:#1e90ff; color:white; }
    .links { margin-top:20px; text-align:center; }
    .links a { color:#26baff; text-decoration:none; }
    .links a:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <form action="processa_login.php" method="POST">
      <input type="email" name="email" placeholder="E-mail" required>
      
      <?php if($erro): ?>
        <div class="error-msg"><?php echo $erro; ?></div>
      <?php endif; ?>

      <input type="password" name="senha" placeholder="Senha" required>
      <button type="submit">Entrar</button>
    </form>
    <div class="links">
      <p><a href="esqueci_senha.php" target="_blank" rel="noopener noreferrer">Esqueci minha senha</a></p>
      <p>É novo aqui? <a href="cadastro.php" target="_blank" rel="noopener noreferrer">Clique aqui</a></p>
    </div>
  </div>
</body>
</html>
