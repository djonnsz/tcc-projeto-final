<?php
// Inicia ou resume a sessão existente para poder destruí-la
session_start();

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

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Define que a resposta será no formato JSON (embora já esteja no bloco acima, é uma boa prática manter)
header('Content-Type: application/json');

// Envia uma resposta de sucesso para o JavaScript
echo json_encode(['success' => true, 'message' => 'Logout realizado com sucesso.']);
exit();
?>