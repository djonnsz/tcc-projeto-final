<?php
include("../conexao.php");
require '../vendor/autoload.php'; // PHPMailer (se já estiver usando)

// Captura o e-mail
$email = $_POST['email'];

// Verifica se existe no banco
$sql = "SELECT * FROM psicologos WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $token = bin2hex(random_bytes(16));

    // Salva token na tabela de reset
    $sqlInsert = "INSERT INTO reset_senhas (email, token, tipo_usuario, criado_em)
                  VALUES (?, ?, 'psicologo', NOW())";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ss", $email, $token);
    $stmtInsert->execute();

    // Link de redefinição
    $link = "http://localhost/site/psicologos/nova_senha_psicologo.php?token=" . $token;

    // Enviar e-mail
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'djonnsouza29@gmail.com';
    $mail->Password = 'jrop pyqj bjpw mfbh'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('seu_email@gmail.com', 'TCC - Recuperação de Senha');
    $mail->addAddress($email);
    $mail->Subject = 'Recupere sua senha';
    $mail->Body = "Clique no link para redefinir sua senha: $link";

    if ($mail->send()) {
        echo "Enviamos um link de recuperação para seu e-mail.";
    } else {
        echo "Erro ao enviar e-mail: " . $mail->ErrorInfo;
    }
} else {
    echo "E-mail não encontrado.";
}
?>
