<?php
// Cabeçalhos para permitir a comunicação (CORS) e definir a resposta como JSON
header("Access-control-allow-origin: *");
header("Access-control-allow-methods: POST, OPTIONS");
header("Access-control-allow-headers: content-type");
header('content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

include("../conexao.php"); // Garanta que o caminho para a conexão está correto

$token = $_POST['token'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirma = $_POST['confirma'] ?? '';

// Validações
if (empty($token) || empty($senha) || $senha !== $confirma) {
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos ou as senhas não coincidem.']);
    exit();
}

// ==============================================================================
// CORREÇÃO 1: Procurar o token na nova tabela dedicada 'reset_senhas_psicologos'
// ==============================================================================
$sql = "SELECT psicologo_id FROM reset_senhas_psicologos WHERE token = ? AND usado = 0 AND expira_em > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $psicologo_id = $row['psicologo_id'];

    // ==============================================================================
    // CORREÇÃO 2: Atualizar a senha na tabela 'psicologos' usando o ID
    // ==============================================================================
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $sqlUpdate = "UPDATE psicologos SET senha = ? WHERE id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("si", $senhaHash, $psicologo_id);
    
    if ($stmtUpdate->execute()) {
        // ==============================================================================
        // CORREÇÃO 3: Marcar o token como usado na nova tabela 'reset_senhas_psicologos'
        // ==============================================================================
        $sqlUsed = "UPDATE reset_senhas_psicologos SET usado = 1 WHERE token = ?";
        $stmtUsed = $conn->prepare($sqlUsed);
        $stmtUsed->bind_param("s", $token);
        $stmtUsed->execute();

        echo json_encode(['status' => 'success', 'message' => 'Senha redefinida com sucesso! Redirecionando...']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao atualizar a senha.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Token inválido, expirado ou já utilizado.']);
}
$conn->close();
?>