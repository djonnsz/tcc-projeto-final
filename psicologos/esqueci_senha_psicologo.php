<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Senha - Psicólogos</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: linear-gradient(-135deg, #131313, #26baff);
      font-family: Arial, sans-serif;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .container {
      background-color: rgba(0, 0, 0, 0.5);
      padding: 30px;
      border-radius: 10px;
      width: 100%;
      max-width: 400px;
      text-align: center;
    }
    input[type="email"] {
      width: 100%;
      padding: 12px;
      margin: 15px 0;
      border: none;
      border-radius: 5px;
    }
    button {
      width: 100%;
      padding: 12px;
      background-color: #26baff;
      border: none;
      border-radius: 5px;
      color: black;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      background-color: #1e90ff;
      color: white;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Recuperar Senha</h2>
    <form action="processa_esqueci_senha_psicologo.php" method="POST">
      <input type="email" name="email" placeholder="Digite seu e-mail" required>
      <button type="submit">Enviar link de recuperação</button>
    </form>
  </div>
</body>
</html>
