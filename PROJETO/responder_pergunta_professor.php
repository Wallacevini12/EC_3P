<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login.php");
    exit;
}

include_once 'conecta_db.php';

$oMysql = conecta_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resposta = trim($_POST['resposta']);
    $codigo_pergunta = intval($_POST['codigo_pergunta']);
    $monitor_id = intval($_POST['monitor_id']);

    if ($resposta && $codigo_pergunta && $monitor_id) {
        $stmt = $oMysql->prepare("INSERT INTO respostas (codigo_pergunta, monitor_id, resposta) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $codigo_pergunta, $monitor_id, $resposta);

        if ($stmt->execute()) {
            // Redireciona de volta com mensagem de sucesso
            header("Location: perguntas_encaminhadas.php?sucesso=1");
        } else {
            echo "Erro ao gravar resposta: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Dados inválidos.";
    }
}

$oMysql->close();
?>
