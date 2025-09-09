<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Psicólogos</title>
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
    .form-container {
      background: rgba(0,0,0,0.5);
      padding: 40px;
      border-radius: 10px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 0 10px rgba(0,0,0,0.4);
    }
    h2 { text-align: center; margin-bottom: 20px; }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: none;
      border-radius: 5px;
    }
    button {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 5px;
      background: #26baff;
      color: black;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover { background: #1e90ff; color: white; }
    .links {
      margin-top: 15px;
      text-align: center;
    }
    .links a {
      color: #26baff;
      text-decoration: none;
    }
    .links a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Cadastro de Psicólogos</h2>
    <form action="verifica_cadastro_psicologo.php" method="POST">
      <input type="text" name="nome" placeholder="Nome completo" required>
      <input type="email" name="email" placeholder="E-mail" required>
      <input type="text" name="celular" placeholder="Celular (ex: 11 99999-9999)" required>
      <input type="text" name="crp" placeholder="CRP (Registro profissional)" required>
      <input type="text" name="especialidade" placeholder="Especialidade" required>
      <input type="password" name="senha" placeholder="Senha" required>
      <input type="password" name="confirma_senha" placeholder="Confirmar senha" required>
      <button type="submit">Cadastrar</button>
    </form>
    <div class="links">
      <p>Já tem conta? <a href="login_psicologo.php">Faça login</a></p>
    </div>
  </div>
</body>
</html>
