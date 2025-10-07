<?php
// Adicionamos os cabeçalhos CORS para permitir a comunicação
header("Access-Control-Allow-Origin: http://127.0.0.1:5000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Informa ao navegador que a resposta é JSON

// Se o pedido for um OPTIONS (preflight request), apenas retorne os cabeçalhos.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

include("conexao.php");
require __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require __DIR__ . '/libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// A partir do PHP 8, é mais seguro pegar os dados assim:
$post_data = file_get_contents("php://input");
$decoded_data = json_decode($post_data, true);
$email = $decoded_data['email'] ?? ($_POST['email'] ?? '');


if(!$email){
    echo json_encode(['status' => 'error', 'message' => 'Preencha o e-mail.']);
    exit();
}

// Verifica se o e-mail existe
$sql = "SELECT id, nome FROM usuarios WHERE email=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(['status' => 'error', 'message' => 'E-mail não cadastrado.']);
    exit();
}

$user = $result->fetch_assoc();
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Salva token no banco
$sqlIns = "INSERT INTO reset_senhas (usuario_id, token, expira_em) VALUES (?, ?, ?)";
$stmtIns = $conn->prepare($sqlIns);
$stmtIns->bind_param("iss", $user['id'], $token, $expira);
$stmtIns->execute();
$stmtIns->close();

$link = "http://127.0.0.1:5000/redefinir-senha?token=".$token;

$mail = new PHPMailer(true);
try{
    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'djonnsouza29@gmail.com';
    $mail->Password   = 'jrop pyqj bjpw mfbh';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('djonnsouza29@gmail.com', 'TCC - Suporte');
    $mail->addAddress($email, $user['nome']);

    $mail->isHTML(true);
    $mail->Subject = 'Recuperação de senha';
    $mail->Body    = "Olá, {$user['nome']}!<br><br>
                      Clique no link abaixo para redefinir sua senha (válido por 1 hora):<br>
                      <a href='{$link}'>{$link}</a>";
    $mail->AltBody = "Redefinir senha: {$link}";

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Enviamos um link de recuperação para seu e-mail.']);
    exit();

} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao enviar e-mail: '.$mail->ErrorInfo]);
    exit();
}
?>