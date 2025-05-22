<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

require_once 'conecta_db.php';

$conn = conecta_db();
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_pergunta = intval($_POST['codigo_pergunta'] ?? 0);
    $resposta = trim($_POST['resposta'] ?? '');
    $id_monitor = $_SESSION['id'];

    if ($codigo_pergunta <= 0 || empty($resposta)) {
        die("Dados inválidos. Verifique o conteúdo e tente novamente.");
    }

    // Inserir resposta
    $stmt_insert = $conn->prepare("
        INSERT INTO respostas (codigo_pergunta, respondente_id, resposta, respondente_tipo, data_resposta)
        VALUES (?, ?, ?, 'monitor', NOW())
    ");

    if (!$stmt_insert) {
        die("Erro no prepare (INSERT): " . $conn->error);
    }

    $stmt_insert->bind_param("iis", $codigo_pergunta, $id_monitor, $resposta);
    if (!$stmt_insert->execute()) {
        die("Erro ao salvar resposta: " . $stmt_insert->error);
    }
    $stmt_insert->close();

    // Atualizar status da pergunta
    $stmt_update = $conn->prepare("UPDATE perguntas SET status = 'respondida', respondida = 1 WHERE codigo_pergunta = ?");
    if (!$stmt_update) {
        die("Erro no prepare (UPDATE): " . $conn->error);
    }

    $stmt_update->bind_param("i", $codigo_pergunta);
    if (!$stmt_update->execute()) {
        die("Erro ao atualizar status da pergunta: " . $stmt_update->error);
    }
    $stmt_update->close();

    header("Location: perguntas_monitor.php?msg=Resposta registrada com sucesso!");
    exit;
} else {
    die("Acesso inválido.");
}

$conn->close();
?>
