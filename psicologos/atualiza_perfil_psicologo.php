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

include_once('../conexao.php'); // A sessão é iniciada aqui

$response = [
    'success' => false,
    'message' => 'Acesso negado. Faça o login novamente.'
];

// SEGURANÇA: Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
    echo json_encode($response);
    exit();
}

$psicologo_id = $_SESSION['psicologo_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Validação dos dados recebidos
if (empty($data['nome']) || empty($data['celular']) || empty($data['especialidade'])) {
    $response['message'] = 'Todos os campos devem ser preenchidos.';
    echo json_encode($response);
    exit();
}

$nome = $data['nome'];
$celular = $data['celular'];
$especialidade = $data['especialidade'];

try {
    $sql = "UPDATE psicologos SET nome = ?, celular = ?, especialidade = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nome, $celular, $especialidade, $psicologo_id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Perfil atualizado com sucesso!';

        // Retorna os dados atualizados para o frontend
        $response['updatedData'] = [
            'nome' => $nome,
            'celular' => $celular,
            'especialidade' => $especialidade,
            'email' => $data['email'],
            'crp' => $data['crp'] ?? ''
        ];

    } else {
        $response['message'] = 'Erro ao atualizar o perfil. Tente novamente.';
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

echo json_encode($response);