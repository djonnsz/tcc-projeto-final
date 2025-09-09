<?php
// Arquivo: psicologos/verifica_cadastro_psicologo.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../conexao.php");

// Ajuste para o caminho do seu projeto
$BASE_URL = "http://localhost/site/psicologos"; 

// Importa PHPMailer
require __DIR__ . '/../libs/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../libs/PHPMailer/src/SMTP.php';
require __DIR__ . '/../libs/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$nome         = $_POST['nome'] ?? '';
$email        = $_POST['email'] ?? '';
$celular      = $_POST['celular'] ?? '';
$crp          = $_POST['crp'] ?? '';
$especialidade= $_POST['especialidade'] ?? '';
$senha        = $_POST['senha'] ?? '';
$confirma     = $_POST['confirma_senha'] ?? '';

if (!$nome || !$email || !$celular || !$crp || !$especialidade || !$senha || !$confirma) {
    exit("Preencha todos os campos.");
}
if ($senha !== $confirma) {
    exit("As senhas não coincidem.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("E-mail inválido.");
}

// Já existe confirmado?
$sql = "SELECT 1 FROM psicologos WHERE email=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$jaConfirmado = $stmt->get_result()->num_rows > 0;
$stmt->close();
if ($jaConfirmado) {
    exit("E-mail já cadastrado e confirmado. Faça login.");
}

// Hash da senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Gera token
$token = bin2hex(random_bytes(32));

// Salva em pré-cadastro
$sql = "INSERT INTO email_verificacao_psicologos (nome,email,celular,crp,especialidade,senha,token) VALUES (?,?,?,?,?,?,?) 
        ON DUPLICATE KEY UPDATE nome=VALUES(nome), celular=VALUES(celular), crp=VALUES(crp), especialidade=VALUES(especialidade), senha=VALUES(senha), token=VALUES(token), criado_em=NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $nome, $email, $celular, $crp, $especialidade, $senhaHash, $token);
if (!$stmt->execute()) {
    exit("Erro ao salvar pré-cadastro: " . $conn->error);
}
$stmt->close();

// Envia e-mail
$link = $BASE_URL . "/confirmar_psicologo.php?token=" . urlencode($token);

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'djonnsouza29@gmail.com'; 
    $mail->Password = 'jrop pyqj bjpw mfbh'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('SEU_EMAIL@gmail.com', 'Cadastro Psicólogos');
    $mail->addAddress($email, $nome);
    $mail->isHTML(true);
    $mail->Subject = 'Confirme seu cadastro de psicólogo';
    $mail->Body = "Olá, {$nome}.<br> Confirme seu cadastro clicando no link:<br> <a href='{$link}'>{$link}</a>";
    $mail->AltBody = "Confirme seu cadastro: {$link}";

    $mail->send();
    echo "Enviamos um e-mail para <b>{$email}</b>. Acesse o link para confirmar seu cadastro.";
} catch (Exception $e) {
    echo "Erro ao enviar e-mail: " . $mail->ErrorInfo;
}
?>
