<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Senha - TCC</title>
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
    input[type="email"] { width:100%; padding:12px; margin-bottom:10px; border:none; border-radius:5px; }
    button { width:100%; padding:12px; background-color:#26baff; border:none; border-radius:5px; color:black; font-weight:bold; cursor:pointer; }
    button:hover { background-color:#1e90ff; color:white; }
    .msg { color:#ff4d4d; text-align:center; margin-bottom:10px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Recuperar Senha</h2>
    <?php
      session_start();
      if(isset($_SESSION['msg'])){
        echo "<div class='msg'>".$_SESSION['msg']."</div>";
        unset($_SESSION['msg']);
      }
    ?>
    <form action="processa_esqueci_senha.php" method="POST">
      <input type="email" name="email" placeholder="Digite seu e-mail" required>
      <button type="submit">Enviar link de recuperação</button>
    </form>
  </div>
</body>
</html>