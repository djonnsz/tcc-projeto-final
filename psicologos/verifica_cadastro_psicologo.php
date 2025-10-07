<?php
// ========================================================================
// === BLOCO DE CABEÇALHOS PERMANENTE E SEGURO ===

// 1. Lista de endereços (origens) que têm permissão para acessar este script
$allowed_origins = [
    'http://localhost:5000', 
    'http://127.0.0.1:5000'
];

// 2. Verifica se a origem da requisição do navegador está na nossa lista
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    // Se estiver, responde autorizando aquela origem específica
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
}

// 3. O restante das permissões
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 4. Se o método for OPTIONS (verificação CORS), encerra o script aqui
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}
// ========================================================================

// Inclui o arquivo de conexão e o autoload do Composer
include_once('../conexao.php'); 
require '../vendor/autoload.php'; // Caminho para o autoload do Composer

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// URL base para o link de confirmação (aponte para o script de confirmação)
$BASE_URL = "http://localhost/tcc-projeto-final/psicologos"; 

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
$celular = $data['celular'] ?? '';
$crp = $data['crp'] ?? '';
$especialidade = $data['especialidade'] ?? '';
$senha = $data['senha'] ?? '';
$confirma = $data['confirma_senha'] ?? '';

// === Validações ===
if (empty($nome) || empty($email) || empty($celular) || empty($crp) || empty($senha) || empty($confirma)) {
    $response['message'] = 'Por favor, preencha todos os campos obrigatórios.';
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
    // Verifica se o e-mail já está na tabela principal de psicólogos (já confirmado)
    $sql_check = "SELECT 1 FROM psicologos WHERE email=? LIMIT 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        $response['message'] = 'Este e-mail já está cadastrado e ativo. Por favor, faça login.';
        echo json_encode($response);
        exit();
    }
    $stmt_check->close();

    // Hash da senha e geração do token
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

    // Salva na tabela de verificação (pré-cadastro)
    $sql_upsert = "INSERT INTO email_verificacao_psicologos (nome,email,celular,crp,especialidade,senha,token) VALUES (?,?,?,?,?,?,?) 
                   ON DUPLICATE KEY UPDATE nome=VALUES(nome), celular=VALUES(celular), crp=VALUES(crp), especialidade=VALUES(especialidade), senha=VALUES(senha), token=VALUES(token), criado_em=NOW()";
    $stmt_upsert = $conn->prepare($sql_upsert);
    $stmt_upsert->bind_param("sssssss", $nome, $email, $celular, $crp, $especialidade, $senhaHash, $token);
    
    if ($stmt_upsert->execute()) {
        // Se salvou no banco, tenta enviar o e-mail
        $link = $BASE_URL . "/confirmar_psicologo.php?token=" . urlencode($token);
        $mail = new PHPMailer(true);
        
        // Configurações do servidor de e-mail (usando as suas)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'djonnsouza29@gmail.com'; 
        $mail->Password = 'jrop pyqj bjpw mfbh'; // Lembre-se de usar uma senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Remetente e Destinatário
        $mail->setFrom('djonnsouza29@gmail.com', 'Apoio a Viciados em Apostas');
        $mail->addAddress($email, $nome);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Confirme seu cadastro em nossa plataforma';
        $mail->Body = "Olá, <b>{$nome}</b>.<br><br>Obrigado por se juntar à nossa rede de apoio. Por favor, confirme seu cadastro clicando no link abaixo:<br><br><a href='{$link}' style='padding:10px 15px; background-color:#007bff; color:white; text-decoration:none; border-radius:5px;'>Confirmar Cadastro</a><br><br>Se o botão não funcionar, copie e cole este link no seu navegador:<br>{$link}";
        $mail->AltBody = "Olá, {$nome}. Confirme seu cadastro copiando e colando este link no seu navegador: {$link}";

        $mail->send();
        
        // Se o e-mail foi enviado com sucesso
        $response['success'] = true;
        $response['message'] = "Cadastro iniciado! Enviamos um e-mail para {$email}. Por favor, acesse o link para confirmar seu cadastro.";

    } else {
        $response['message'] = 'Erro ao salvar os dados no banco de dados.';
    }
    $stmt_upsert->close();
    $conn->close();

} catch (Exception $e) {
    // Captura erros do PHPMailer ou do Banco de Dados
    $response['message'] = 'Erro ao enviar o e-mail de confirmação: ' . $mail->ErrorInfo;
}

// Retorna a resposta final em formato JSON
echo json_encode($response);