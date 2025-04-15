<?php
session_start();

require_once 'conecta_db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$conn = conecta_db();

// Verifica conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Busca os dados do usuário
$sql = "SELECT nome, email, curso, tipo_usuario FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro na preparação da consulta: " . $conn->error);
}

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Usuário não encontrado.";
    exit();
}

$dados_usuario = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Conta</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>

<div class="container" style="margin-top: 70px;">
    <h2>Minha Conta</h2>
    <p><strong>Nome:</strong> <?= htmlspecialchars($dados_usuario['nome']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($dados_usuario['email']) ?></p>
    <p><strong>Curso:</strong> <?= htmlspecialchars($dados_usuario['curso']) ?></p>
    <p><strong>Tipo de Usuário:</strong> <?= ucfirst(htmlspecialchars($dados_usuario['tipo_usuario'])) ?></p>

    <a href="logout.php" class="btn btn-danger">Sair</a>
</div>

</body>
</html>