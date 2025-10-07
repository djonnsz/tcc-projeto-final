<?php
// Arquivo: psicologos/confirmar_psicologo.php
include("../conexao.php");

// ======================================================================================
// === ÁREA DE CONFIGURAÇÃO DE REDIRECIONAMENTO ===
// Define as URLs de sucesso e erro DENTRO do seu site Flask.
$URL_BASE_FLASK = "http://localhost:5000";
$URL_SUCESSO = $URL_BASE_FLASK . "/confirmacao?status=sucesso";
$URL_ERRO = $URL_BASE_FLASK . "/confirmacao?status=erro";
// ======================================================================================

$token = $_GET['token'] ?? '';

// Validação inicial do token
if (!$token || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    header("Location: " . $URL_ERRO . "&msg=" . urlencode("Token em formato inválido."));
    exit();
}

try {
    // Inicia uma transação: ou tudo funciona, ou nada é salvo.
    $conn->begin_transaction();

    // 1. Encontra o pré-cadastro pelo token na tabela de verificação
    $sql_find = "SELECT * FROM email_verificacao_psicologos WHERE token=? LIMIT 1";
    $stmt_find = $conn->prepare($sql_find);
    $stmt_find->bind_param("s", $token);
    $stmt_find->execute();
    $result = $stmt_find->get_result();

    if ($result->num_rows === 0) {
        // Se não encontrou, redireciona para a página de erro
        header("Location: " . $URL_ERRO . "&msg=" . urlencode("Link de confirmação inválido ou já utilizado."));
        exit();
    }
    $pendente = $result->fetch_assoc();
    $stmt_find->close();

    // 2. Verifica se o e-mail já existe na tabela principal (caso o usuário tenha tentado se cadastrar de novo)
    $sqlChk = "SELECT 1 FROM psicologos WHERE email=? LIMIT 1";
    $stmtChk = $conn->prepare($sqlChk);
    $stmtChk->bind_param("s", $pendente['email']);
    $stmtChk->execute();
    if ($stmtChk->get_result()->num_rows > 0) {
        // Limpa o token pendente, pois o usuário já está confirmado
        $del = $conn->prepare("DELETE FROM email_verificacao_psicologos WHERE token=?");
        $del->bind_param("s", $token);
        $del->execute();
        $conn->commit(); // Salva a exclusão
        header("Location: " . $URL_ERRO . "&msg=" . urlencode("Este e-mail já foi confirmado. Por favor, faça login."));
        exit();
    }
    $stmtChk->close();

    // 3. Move os dados para a tabela definitiva de psicólogos
    $sqlIns = "INSERT INTO psicologos (nome,email,celular,crp,especialidade,senha) VALUES (?,?,?,?,?,?)";
    $stmtIns = $conn->prepare($sqlIns);
    $stmtIns->bind_param("ssssss", $pendente['nome'], $pendente['email'], $pendente['celular'], $pendente['crp'], $pendente['especialidade'], $pendente['senha']);
    $stmtIns->execute();
    $stmtIns->close();

    // 4. Deleta o registro da tabela de verificação
    $sqlDel = "DELETE FROM email_verificacao_psicologos WHERE token=?";
    $delStmt = $conn->prepare($sqlDel);
    $delStmt->bind_param("s", $token);
    $delStmt->execute();
    $delStmt->close();

    // 5. Se todos os passos acima deram certo, salva permanentemente no banco
    $conn->commit();
    
    // 6. Redireciona para a página de sucesso no Flask
    header("Location: " . $URL_SUCESSO);
    exit();

} catch (Exception $e) {
    // Se qualquer um dos passos falhar, desfaz todas as operações
    $conn->rollback();
    header("Location: " . $URL_ERRO . "&msg=" . urlencode("Ocorreu um erro no servidor. Tente novamente."));
    exit();
}
?>