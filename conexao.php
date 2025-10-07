<?php

// ======================================================================================
// === CONFIGURAÇÃO AVANÇADA DO COOKIE DE SESSÃO (CORREÇÃO FINAL) ===
session_set_cookie_params([
    'lifetime' => 86400, // Duração do cookie (24 horas)
    'path' => '/',
    // 'domain' => 'localhost', // REMOVIDO para deixar o navegador decidir o domínio correto
    'secure' => false,   // Mude para 'true' se um dia você usar HTTPS
    'httponly' => true, // O cookie só é acessível via HTTP (mais seguro)
    'samesite' => 'Lax'  // Política principal para compatibilidade
]);
// ======================================================================================

session_start();

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "tcc";

// Cria a conexão com o banco de dados
$conn = new mysqli($servidor, $usuario, $senha, $banco);

// Verifica se a conexão falhou
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados: ' . $conn->connect_error]);
    exit();
}
?>