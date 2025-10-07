<?php
// ========================================================================
// === BLOCO DE CABEÇALHOS PERMANENTE E SEGURO ===
$allowed_origins = [
    'http://localhost:5000', 
    'http://127.0.0.1:5000',
    'http://tcc.local'
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

include_once('conexao.php'); // A sessão já é iniciada aqui

// Resposta padrão
$response = [
    'success' => false,
    'message' => 'Ocorreu um erro.'
];

// Pega os dados do JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? null;
$senha = $data['senha'] ?? null;

// Valida campos
if (!$email || !$senha) {
    $response['message'] = 'E-mail e senha são obrigatórios.';
    echo json_encode($response);
    exit();
}

try {
    // ALTERAÇÃO: Pedimos também a coluna 'nome' para retornar ao frontend
    $sql = "SELECT id, nome, senha FROM usuarios WHERE email = ? AND status = 'ativo' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifica a senha
        if (password_verify($senha, $user['senha'])) {
            // Login correto -> cria sessão
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['user_type'] = 'usuario';
            
            $response['success'] = true;
            $response['message'] = 'Login realizado com sucesso!';
            
            // ADICIONADO: Anexamos os dados do usuário na resposta para o painel
            unset($user['senha']); // Remove a senha da resposta por segurança
            $response['userData'] = $user;

        } else {
            // Senha incorreta
            $response['message'] = 'Usuário ou senha incorretos.';
        }
    } else {
        // Usuário não encontrado ou não ativo
        $response['message'] = 'Usuário ou senha incorretos.';
    }
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

// Envia a resposta final para o JavaScript
echo json_encode($response);
?>