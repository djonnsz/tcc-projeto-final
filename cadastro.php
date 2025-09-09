<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro - TCC</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: linear-gradient(to right, #0077be, #0f2027);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .card {
      background-color: #0f2c3a;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
      color: #fff;
      width: 350px;
    }

    .card h2 {
      text-align: center;
      margin-bottom: 25px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: none;
      border-radius: 5px;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #00aaff;
      border: none;
      border-radius: 5px;
      color: #fff;
      font-weight: bold;
      cursor: pointer;
      margin-top: 15px;
    }

    button:hover {
      background-color: #008fcc;
    }

    a {
      color: #00aaff;
      text-decoration: none;
      display: block;
      text-align: center;
      margin-top: 15px;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Criar Conta</h2>
    <form action="verifica_cadastro.php" method="POST">
      <input type="text" name="nome" placeholder="Nome completo" required>
      <input type="email" name="email" placeholder="E-mail" required>
      <input type="password" name="senha" placeholder="Senha" required>
      <input type="password" name="confirma_senha" placeholder="Confirmar senha" required>
      <button type="submit" id="bt_cadastrar">Cadastrar</button>
      <a href="login.php">Já tem conta? Faça login</a>
    </form>
  </div>
</body>
</html>