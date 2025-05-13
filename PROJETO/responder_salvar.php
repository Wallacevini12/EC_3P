<?php
session_start();

// Incluir o arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; 

// Conectar ao banco de dados
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Verificar se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['resposta']) && isset($_POST['codigo_pergunta'])) {
    $resposta = $_POST['resposta'];
    $codigo_pergunta = $_POST['codigo_pergunta'];

    // Inserir a resposta na tabela 'respostas'
    $stmt = $oMysql->prepare("INSERT INTO respostas (codigo_pergunta, resposta, data_resposta) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $codigo_pergunta, $resposta);

    if ($stmt->execute()) {
        // Atualizar o status da pergunta manualmente (caso não queira depender da trigger)
        $stmtStatus = $oMysql->prepare("UPDATE perguntas SET status = 'respondida' WHERE codigo_pergunta = ?");
        $stmtStatus->bind_param("i", $codigo_pergunta);
        $stmtStatus->execute();
        $stmtStatus->close();

        $_SESSION['mensagem'] = "Sua resposta foi enviada com sucesso!";
        header('Location: listar_perguntas.php');
        exit;
    } else {
        $_SESSION['mensagem'] = "Erro ao registrar resposta: " . $stmt->error;
        header('Location: listar_perguntas.php');
        exit;
    }

    $stmt->close();
    $oMysql->close();
} else {
    $_SESSION['mensagem'] = "Dados inválidos!";
    header('Location: listar_perguntas.php');
    exit;
}
?>