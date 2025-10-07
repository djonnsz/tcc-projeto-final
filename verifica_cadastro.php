<?php
// ========================================================================
// === BLOCO DE CABEÇALHOS PERMANENTE E SEGURO ===
$allowed_origins = [
    'http://localhost:5000', 
    'http://127.0.0.1:5000'
];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
// ========================================================================

// Inclui a conexão e o autoload do Composer
include_once('conexao.php'); 
require 'vendor/autoload.php';

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// URL base para o link de confirmação do usuário
$BASE_URL = "http://localhost/tcc-projeto-final";

// Array padrão da resposta
$response = [
    'success' => false,
    'message' => 'Ocorreu um erro inesperado.'
];

// Pega os dados enviados pelo JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Atribui os dados a variáveis
$nome = $data['nome'] ?? '';
$email = $data['email'] ?? '';
$senha = $data['senha'] ?? '';
$confirma = $data['confirma_senha'] ?? '';

// === Validações ===
if (empty($nome) || empty($email) || empty($senha) || empty($confirma)) {
    $response['message'] = 'Por favor, preencha todos os campos.';
    echo json_encode($response);
    exit();
}
if ($senha !== $confirma) {
    $response['message'] = 'As senhas não coincidem.';
    echo json_encode($response);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'O e-mail informado é inválido.';
    echo json_encode($response);
    exit();
}

try {
    // Verifica se o e-mail já está na tabela principal de usuários
    $sql_check = "SELECT 1 FROM usuarios WHERE email = ? LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $response['message'] = 'Este e-mail já está cadastrado. Por favor, faça login.';
        echo json_encode($response);
        exit();
    }
    $stmt_check->close();

    // Hash da senha e geração do token
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

    // Salva na tabela de verificação (usando a mesma lógica do seu código original)
    $sql_upsert = "INSERT INTO email_verificacao (nome, email, senha, token) VALUES (?, ?, ?, ?)
                   ON DUPLICATE KEY UPDATE nome=VALUES(nome), senha=VALUES(senha), token=VALUES(token), criado_em=NOW()";
    $stmt_upsert = $conn->prepare($sql_upsert);
    $stmt_upsert->bind_param("ssss", $nome, $email, $senhaHash, $token);
    
    if ($stmt_upsert->execute()) {
        // Se salvou no banco, tenta enviar o e-mail
        $link = $BASE_URL . "/confirmar.php?token=" . urlencode($token);
        $mail = new PHPMailer(true);
        
        // Configurações do servidor de e-mail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'djonnsouza29@gmail.com'; 
        $mail->Password = 'jrop pyqj bjpw mfbh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Remetente e Destinatário
        $mail->setFrom('djonnsouza29@gmail.com', 'TCC - Suporte');
        $mail->addAddress($email, $nome);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Confirme seu cadastro na plataforma';
        $mail->Body = "Olá, <b>{$nome}</b>!<br><br>Para confirmar seu cadastro, clique no link abaixo:<br><a href='{$link}'>Confirmar Cadastro</a>";
        $mail->AltBody = "Confirme seu cadastro: {$link}";

        $mail->send();
        
        $response['success'] = true;
        $response['message'] = "Cadastro iniciado! Enviamos um e-mail para {$email} para confirmação.";

    } else {
        $response['message'] = 'Erro ao salvar os dados no banco de dados.';
    }
    $stmt_upsert->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = 'Erro ao enviar o e-mail de confirmação: ' . $mail->ErrorInfo;
}

echo json_encode($response);
?>