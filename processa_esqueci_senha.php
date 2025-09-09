<?php
session_start();
include("conexao.php");
require __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require __DIR__ . '/libs/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['email'] ?? '';

if(!$email){
    $_SESSION['msg'] = "Preencha o e-mail.";
    header("Location: esqueci_senha.php");
    exit();
}

// Verifica se o e-mail existe
$sql = "SELECT id, nome FROM usuarios WHERE email=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    $_SESSION['msg'] = "E-mail não cadastrado.";
    header("Location: esqueci_senha.php");
    exit();
}

$user = $result->fetch_assoc();

// Gera token de recuperação (64 caracteres hex)
$token = bin2hex(random_bytes(32));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour")); // Expira em 1 hora

// Salva token no banco (tabela nova: reset_senhas)
$sqlIns = "INSERT INTO reset_senhas (usuario_id, token, expira_em) VALUES (?, ?, ?)";
$stmtIns = $conn->prepare($sqlIns);
$stmtIns->bind_param("iss", $user['id'], $token, $expira);
$stmtIns->execute();
$stmtIns->close();

// Envia e-mail com link de recuperação
$link = "http://localhost/site/nova_senha.php?token=".$token; // ajuste conforme seu projeto

$mail = new PHPMailer(true);
try{
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'djonnsouza29@gmail.com'; // ajuste
    $mail->Password   = 'jrop pyqj bjpw mfbh';     // senha de app
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
    $_SESSION['msg'] = "Enviamos um link de recuperação para seu e-mail.";
    header("Location: esqueci_senha.php");
    exit();

}catch(Exception $e){
    $_SESSION['msg'] = "Erro ao enviar e-mail: ".$mail->ErrorInfo;
    header("Location: esqueci_senha.php");
    exit();
}
?>
