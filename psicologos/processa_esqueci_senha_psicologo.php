<?php
// Cabeçalhos para permitir a comunicação (CORS) e definir a resposta como JSON
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

include("../conexao.php");
require '../vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;

// Captura o e-mail do POST
$email = $_POST['email'] ?? '';

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, preencha o campo de e-mail.']);
    exit();
}

// Verifica se o e-mail existe na tabela de psicólogos
$sql = "SELECT id, nome FROM psicologos WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $psicologo = $result->fetch_assoc();
    $token = bin2hex(random_bytes(32)); // Token mais longo para maior segurança
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour")); // Adiciona data de expiração

    // Assumindo que a sua tabela de reset se chama 'reset_senhas_psicologos'
    // E que ela tem as colunas psicologo_id, token, expira_em
    $sqlInsert = "INSERT INTO reset_senhas_psicologos (psicologo_id, token, expira_em) VALUES (?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iss", $psicologo['id'], $token, $expira);
    $stmtInsert->execute();

    // Link de redefinição apontando para a nova página EM PYTHON
    $link = "http://127.0.0.1:5000/redefinir-senha-psicologo?token=" . $token;

    // Bloco de Envio de E-mail com PHPMailer
    $mail = new PHPMailer();
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'djonnsouza29@gmail.com'; // Seu e-mail
        $mail->Password = 'jrop pyqj bjpw mfbh'; // Sua senha de aplicativo
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8'; // Adicionado para caracteres especiais

        $mail->setFrom('djonnsouza29@gmail.com', 'TCC - Suporte Colaborador');
        $mail->addAddress($email, $psicologo['nome']);
        $mail->Subject = 'Recuperação de Senha - Painel do Colaborador';
        $mail->isHTML(true);
        $mail->Body = "Olá, {$psicologo['nome']}!<br><br>
                       Clique no link a seguir para redefinir sua senha (válido por 1 hora):<br>
                       <a href='$link'>Redefinir Senha</a>";
        
        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Enviamos um link de recuperação para seu e-mail.']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Erro ao enviar e-mail: {$mail->ErrorInfo}"]);
    }
} else {
    // Por segurança, não informamos que o e-mail não foi encontrado.
    echo json_encode(['status' => 'success', 'message' => 'Se o e-mail estiver cadastrado, um link de recuperação foi enviado.']);
}

$stmt->close();
$conn->close();
?>