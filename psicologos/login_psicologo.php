<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login Psicólogo</title>
  <style>
    body { margin:0; padding:0; min-height:100vh; background:linear-gradient(-135deg,#131313,#26baff);
      font-family:Arial,sans-serif; color:white; display:flex; align-items:center; justify-content:center; }
    .login-container { background-color:rgba(0,0,0,0.5); padding:40px; border-radius:10px;
      width:100%; max-width:400px; box-shadow:0 0 10px rgba(0,0,0,0.4); }
    h2{text-align:center;margin-bottom:30px;}
    input{width:100%;padding:12px;margin-bottom:15px;border:none;border-radius:5px;}
    button{width:100%;padding:12px;background-color:#26baff;border:none;border-radius:5px;color:black;font-weight:bold;cursor:pointer;transition:0.3s;}
    button:hover{background-color:#1e90ff;color:white;}
    .links{margin-top:20px;text-align:center;}
    .links a{color:#26baff;text-decoration:none;}
    .links a:hover{text-decoration:underline;}
    .error{color:red;text-align:center;margin-bottom:15px;}
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login Psicólogo</h2>
    <?php if (isset($_GET['erro'])): ?>
      <p class="error">E-mail ou senha incorretos</p>
    <?php endif; ?>
    <form action="processa_login_psicologo.php" method="POST">
      <input type="email" name="email" placeholder="E-mail" required>
      <input type="password" name="senha" placeholder="Senha" required>
      <button type="submit">Entrar</button>
    </form>
    <div class="links">
      <p><a href="esqueci_senha_psicologo.php">Esqueci minha senha</a></p>
      <p>É novo aqui? <a href="cadastro_psicologo.php">Cadastre-se</a></p>
    </div>
  </div>
</body>
</html>
