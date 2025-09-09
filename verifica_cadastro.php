<?php
// Arquivo: verifica_cadastro.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexao.php");

// ====== CONFIGURAÇÕES ====
// Ajuste para o caminho correto do seu projeto no localhost
$BASE_URL = "http://localhost/site"; // ex.: http://localhost/tcc  (sem barra no final)

// ====== IMPORTA PHPMailer (sem Composer) ======
require __DIR__ . '/libs/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/libs/PHPMailer/src/SMTP.php';
require __DIR__ . '/libs/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);




// Coleta os dados do formulário
$nome     = $_POST['nome']          ?? '';
$email    = $_POST['email']         ?? '';
$senha    = $_POST['senha']         ?? '';
$confirma = $_POST['confirma_senha']?? '';

// Validações mínimas
if (!$nome || !$email || !$senha || !$confirma) {
  exit("Preencha todos os campos.");
}
if (trim($senha) !== trim($confirma)) {
  exit("As senhas não coincidem.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit("E-mail inválido.");
}

// Já existe confirmado?
$sql = "SELECT 1 FROM usuarios WHERE email = ? LIMIT 1";
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

// Gera token (64 hex)
$token = bin2hex(random_bytes(32));

// Se já existe pré-cadastro, atualiza; senão, insere
$sql = "SELECT id FROM email_verificacao WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
$temPendente = $res->num_rows > 0;
$stmt->close();

if ($temPendente) {
  $row = $res->fetch_assoc();
  $idPend = (int)$row['id'];
  $sqlUp = "UPDATE email_verificacao SET nome=?, senha=?, token=?, criado_em=NOW() WHERE id=?";
  $stmtUp = $conn->prepare($sqlUp);
  $stmtUp->bind_param("sssi", $nome, $senhaHash, $token, $idPend);
  $ok = $stmtUp->execute();
  $stmtUp->close();
} else {
  $sqlIns = "INSERT INTO email_verificacao (nome, email, senha, token) VALUES (?, ?, ?, ?)";
  $stmtIns = $conn->prepare($sqlIns);
  $stmtIns->bind_param("ssss", $nome, $email, $senhaHash, $token);
  $ok = $stmtIns->execute();
  $stmtIns->close();
}

if (!$ok) {
  exit("Erro ao iniciar a verificação: " . $conn->error);
}

// ====== Envia e-mail com link ======
$link = $BASE_URL . "/confirmar.php?token=" . urlencode($token);

// Configure seu SMTP aqui:
$mail = new PHPMailer(true);
try {
  $mail->isSMTP();
  // EXEMPLO COM GMAIL — requer senha de app
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'djonnsouza29@gmail.com';  // <-- ajuste
  $mail->Password   = 'jrop pyqj bjpw mfbh';               // <-- ajuste (senha de app, não a sua)
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = 587;

  $mail->setFrom('djonnsouza29@gmail.com', 'TCC - Suporte'); // remetente
  $mail->addAddress($email, $nome); // destinatário

  $mail->isHTML(true);
  $mail->Subject = 'Confirme seu cadastro';
  $mail->Body    = "Olá, {$nome}!<br><br>
    Para confirmar seu cadastro, clique no link abaixo:<br>
    <a href='{$link}'>{$link}</a><br><br>
    Se você não fez este cadastro, ignore este e-mail.";
  $mail->AltBody = "Confirme seu cadastro: {$link}";

  $mail->send();
  echo "Enviamos um e-mail para <b>{$email}</b>. Acesse o link para confirmar seu cadastro.";
} catch (Exception $e) {
  // (opcional) Limpeza se quiser remover o pendente quando falha o e-mail
  // $conn->query("DELETE FROM email_verificacao WHERE email = '" . $conn->real_escape_string($email) . "'");
  echo "Erro ao enviar o e-mail de confirmação: " . $mail->ErrorInfo;
}
