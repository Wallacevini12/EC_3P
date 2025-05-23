<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

if (!isset($_POST['codigo_pergunta'])) {
    header("Location: perguntas_monitor.php?msg=" . urlencode("Erro: pergunta não especificada"));
    exit;
}

include_once 'conecta_db.php';
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$codigo_pergunta = intval($_POST['codigo_pergunta']);

// Atualizar a pergunta para marcar como encaminhada
$sql_update = "UPDATE perguntas SET encaminhada = 1 WHERE codigo_pergunta = $codigo_pergunta";

if ($oMysql->query($sql_update)) {
    $msg = "Pergunta encaminhada com sucesso!";
} else {
    $msg = "Erro ao encaminhar pergunta: " . $oMysql->error;
}

$oMysql->close();

// Redirecionar para perguntas_monitor com a mensagem
header("Location: perguntas_monitor.php?msg=" . urlencode($msg));
exit;
?>

