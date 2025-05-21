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
    $professor_id = intval($_SESSION['id']);
    $respondente_tipo = 'professor';

    if ($resposta && $codigo_pergunta) {
        $stmt = $oMysql->prepare("INSERT INTO respostas (codigo_pergunta, respondente_id, resposta, respondente_tipo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $codigo_pergunta, $professor_id, $resposta, $respondente_tipo);

        if ($stmt->execute()) {
            header("Location: perguntas_encaminhadas.php?sucesso=1");
            exit;
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
