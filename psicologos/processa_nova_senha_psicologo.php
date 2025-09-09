<?php
include("../conexao.php");

if ($_POST['senha'] !== $_POST['confirma']) {
    die("As senhas não coincidem.");
}

$token = $_POST['token'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

// Busca e-mail pelo token
$sql = "SELECT email FROM reset_senhas WHERE token = ? AND tipo_usuario='psicologo'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];

    // Atualiza senha
    $sqlUpdate = "UPDATE psicologos SET senha=? WHERE email=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ss", $senha, $email);
    $stmtUpdate->execute();

    // Remove token usado
    $sqlDel = "DELETE FROM reset_senhas WHERE token=?";
    $stmtDel = $conn->prepare($sqlDel);
    $stmtDel->bind_param("s", $token);
    $stmtDel->execute();

    echo "Senha redefinida com sucesso!";
} else {
    echo "Token inválido.";
}
?>
