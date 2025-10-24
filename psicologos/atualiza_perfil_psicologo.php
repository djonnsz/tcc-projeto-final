<?php
// ========================================================================
// === CABEÇALHOS DE CORS E CONFIGURAÇÃO SEGURA ===

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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ========================================================================
// === INICIALIZAÇÃO DE SESSÃO E CONEXÃO ===

session_start();
include_once('../conexao.php');

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'message' => 'Acesso negado. Faça o login novamente.'
];

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
    echo json_encode($response);
    exit();
}

$psicologo_id = $_SESSION['psicologo_id'];

// ========================================================================
// === TRATAMENTO DOS DADOS RECEBIDOS ===

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $response['message'] = 'Nenhum dado recebido ou formato inválido.';
    echo json_encode($response);
    exit();
}

if (empty($data['nome']) || empty($data['celular']) || empty($data['especialidade'])) {
    $response['message'] = 'Todos os campos devem ser preenchidos.';
    echo json_encode($response);
    exit();
}

$nome = trim($data['nome']);
$celular = trim($data['celular']);
$especialidade = trim($data['especialidade']);

// ========================================================================
// === EXECUÇÃO DA ATUALIZAÇÃO ===

try {
    $sql = "UPDATE psicologos SET nome = ?, celular = ?, especialidade = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nome, $celular, $especialidade, $psicologo_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Perfil atualizado com sucesso!';
        $response['updatedData'] = [
            'nome' => $nome,
            'celular' => $celular,
            'especialidade' => $especialidade
        ];
    } else {
        $response['message'] = 'Erro ao atualizar o perfil. Tente novamente.';
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

// ========================================================================
// === RETORNO JSON PARA O FRONTEND ===

echo json_encode($response);
