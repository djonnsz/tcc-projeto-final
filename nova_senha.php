<?php
session_start();
include("conexao.php");

if (!isset($_GET['token'])) { die("Token não fornecido."); }
$token = $_GET['token'];

// Verifica se o token é válido apenas para mostrar o formulário
$stmt = $conn->prepare("SELECT id FROM reset_senhas WHERE token=? AND usado=0 AND expira_em > NOW() LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Token inválido ou expirado. Por favor, solicite uma nova recuperação de senha.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <style>
        body { margin:0; padding:0; min-height:100vh; background: linear-gradient(-135deg, #131313, #26baff); font-family: Arial, sans-serif; color: white; display:flex; align-items:center; justify-content:center; }
        .container { background-color: rgba(0,0,0,0.5); padding:40px; border-radius:10px; width:100%; max-width:400px; box-shadow:0 0 10px rgba(0,0,0,0.4); }
        h2 { text-align: center; }
        input[type="password"] { width:100%; padding:12px; margin-bottom:10px; border:none; border-radius:5px; }
        button { width:100%; padding:12px; background-color:#26baff; border:none; border-radius:5px; color:black; font-weight:bold; cursor:pointer; }
        button:hover { background-color:#1e90ff; color:white; }
        .msg { text-align:center; margin-bottom:15px; background-color: rgba(255, 255, 255, 0.1); padding: 10px; border-radius: 5px;}
        .msg.success { color: #90ee90; }
        .msg.error { color: #ff4d4d; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Crie sua Nova Senha</h2>
        
        <div id="feedback-message"></div>

        <form id="form-nova-senha" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <label for="senha">Nova Senha:</label>
            <input type="password" name="senha" id="senha" placeholder="Digite sua nova senha" required>
            
            <label for="confirma_senha">Confirme a Nova Senha:</label>
            <input type="password" name="confirma_senha" id="confirma_senha" placeholder="Confirme sua nova senha" required>
            
            <button type="submit">Redefinir Senha</button>
        </form>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-nova-senha');
    const feedbackDiv = document.getElementById('feedback-message');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        const urlPhp = 'processa_nova_senha.php';
        
        feedbackDiv.innerHTML = '<p class="msg">A processar...</p>';

        fetch(urlPhp, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageClass = data.status === 'success' ? 'success' : 'error';
            feedbackDiv.innerHTML = '<p class="msg ' + messageClass + '">' + data.message + '</p>';

            if (data.status === 'success') {
                // Redireciona para a página inicial do site Python após 3 segundos
                setTimeout(function() {
                    window.location.href = 'http://127.0.0.1:5000'; // Página de Login/Inicial do Python
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            feedbackDiv.innerHTML = '<p class="msg error">Ocorreu um erro de comunicação.</p>';
        });
    });
});
</script>

</body>
</html>