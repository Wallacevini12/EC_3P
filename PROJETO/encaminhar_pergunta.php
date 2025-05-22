<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include_once 'conecta_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_pergunta'])) {
    $codigo_pergunta = intval($_POST['codigo_pergunta']);

    $oMysql = conecta_db();
    if ($oMysql->connect_error) {
        die("Erro de conexão: " . $oMysql->connect_error);
    }

    // Atualizar a pergunta para encaminhada
    $sql = "UPDATE perguntas SET encaminhada = 1 WHERE codigo_pergunta = $codigo_pergunta";

    if ($oMysql->query($sql)) {
        // Redirecionar de volta para a lista de perguntas do monitor
        header("Location: perguntas_monitor.php?msg=Pergunta encaminhada com sucesso");
    } else {
        echo "Erro ao encaminhar a pergunta: " . $oMysql->error;
    }

    $oMysql->close();
} else {
    header("Location: perguntas_monitor.php");
    exit;
}