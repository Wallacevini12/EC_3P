<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include_once 'conecta_db.php';

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_pergunta = intval($_POST['codigo_pergunta']);
    $resposta = trim($_POST['resposta']);
    $id_monitor = $_SESSION['id'];

    if (empty($resposta)) {
        die("A resposta não pode ser vazia.");
    }

    // Inserir resposta na tabela respostas
 
    $sql_insert = "INSERT INTO respostas (codigo_pergunta, respondente_id, resposta, data_resposta) VALUES (?, ?, ?, NOW())";
    $stmt_insert = $oMysql->prepare($sql_insert);

    if (!$stmt_insert) {
        die("Erro no prepare: " . $oMysql->error);
    }

    $stmt_insert->bind_param("iis", $codigo_pergunta, $id_monitor, $resposta);

    if (!$stmt_insert->execute()) {
        die("Erro ao salvar resposta: " . $stmt_insert->error);
    }

    $stmt_insert->close();

    // Atualizar status da pergunta para 'respondida' e respondida = 1
    $sql_update = "UPDATE perguntas SET status = 'respondida', respondida = 1 WHERE codigo_pergunta = ?";
    $stmt_update = $oMysql->prepare($sql_update);

    if (!$stmt_update) {
        die("Erro no prepare: " . $oMysql->error);
    }

    $stmt_update->bind_param("i", $codigo_pergunta);

    if (!$stmt_update->execute()) {
        die("Erro ao atualizar status da pergunta: " . $stmt_update->error);
    }

    $stmt_update->close();

    // Redirecionar para a página do monitor para listar perguntas pendentes
    header("Location: perguntas_monitor.php");
    exit;
} else {
    die("Método inválido.");
}
?>
