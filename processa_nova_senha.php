<?php
// Não usaremos sessões para este script
// session_start();
include("conexao.php");

// Cabeçalhos para permitir a comunicação (CORS) e definir a resposta como JSON
header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Acesso inválido.']);
    exit();
}

// Recebemos os dados via POST
$token = $_POST['token'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirma_senha = $_POST['confirma_senha'] ?? '';

// 1. Validação dos campos
if (empty($token) || empty($senha) || empty($confirma_senha)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, preencha todos os campos.']);
    exit();
}

if ($senha !== $confirma_senha) {
    echo json_encode(['status' => 'error', 'message' => 'As senhas não coincidem.']);
    exit();
}

// 2. Validar o token diretamente no banco de dados
$stmt = $conn->prepare("SELECT id, usuario_id FROM reset_senhas WHERE token=? AND usado=0 AND expira_em > NOW() LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Token inválido ou expirado. Tente novamente.']);
    exit();
}

$reset_data = $result->fetch_assoc();
$usuario_id = $reset_data['usuario_id'];
$reset_id = $reset_data['id'];
$stmt->close();

// 3. Atualizar a senha do usuário
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$stmt_update = $conn->prepare("UPDATE usuarios SET senha=? WHERE id=?");
$stmt_update->bind_param("si", $senhaHash, $usuario_id);

if ($stmt_update->execute()) {
    $stmt_update->close();
    
    // 4. Marcar o token como usado para não ser reutilizado
    $stmt_deactivate = $conn->prepare("UPDATE reset_senhas SET usado=1 WHERE id=?");
    $stmt_deactivate->bind_param("i", $reset_id);
    $stmt_deactivate->execute();
    $stmt_deactivate->close();
    
    echo json_encode(['status' => 'success', 'message' => 'Senha redefinida com sucesso! Você será redirecionado.']);
    exit();

} else {
    echo json_encode(['status' => 'error', 'message' => 'Ocorreu um erro ao atualizar sua senha. Tente novamente.']);
    exit();
}
?>