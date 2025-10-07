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

include_once('../conexao.php'); // A sessão já é iniciada aqui

$response = [
    'success' => false,
    'message' => 'Ocorreu um erro.'
];

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? null;
$senha = $data['senha'] ?? null;

if (!$email || !$senha) {
    $response['message'] = 'E-mail e senha são obrigatórios.';
    echo json_encode($response);
    exit();
}

try {
    $sql = "SELECT id, nome, email, celular, crp, especialidade, senha FROM psicologos WHERE email = ? AND status = 'ativo' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $psicologo = $result->fetch_assoc();
        
        if (password_verify($senha, $psicologo['senha'])) {
            $_SESSION['psicologo_id'] = $psicologo['id'];
            $_SESSION['user_type'] = 'psicologo';
            
            $response['success'] = true;
            $response['message'] = 'Login realizado com sucesso!';

            unset($psicologo['senha']); // Remove a senha da resposta por segurança
            $response['psicologoData'] = $psicologo;

        } else {
            $response['message'] = 'E-mail ou senha inválidos.';
        }
    } else {
        $response['message'] = 'E-mail ou senha inválidos, ou cadastro ainda pendente de aprovação.';
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

echo json_encode($response);