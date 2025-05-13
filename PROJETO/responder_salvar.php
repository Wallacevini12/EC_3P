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
    if ($stmt === false) {
        $_SESSION['mensagem'] = "Erro ao preparar consulta para inserir resposta.";
        header('Location: listar_perguntas.php');
        exit;
    }
    $stmt->bind_param("is", $codigo_pergunta, $resposta);

    if ($stmt->execute()) {
        // Atualizar o status da pergunta manualmente (caso não queira depender da trigger)
        $stmtStatus = $oMysql->prepare("UPDATE perguntas SET status = 'respondida' WHERE codigo_pergunta = ?");
        if ($stmtStatus === false) {
            $_SESSION['mensagem'] = "Erro ao preparar consulta para atualizar o status da pergunta.";
            header('Location: listar_perguntas.php');
            exit;
        }
        $stmtStatus->bind_param("i", $codigo_pergunta);
        $stmtStatus->execute();

        // Verificar se o update afetou alguma linha
        if ($stmtStatus->affected_rows > 0) {
            $_SESSION['mensagem'] = "Sua resposta foi enviada com sucesso!";
        } else {
            $_SESSION['mensagem'] = "Erro: O status da pergunta não foi atualizado.";
        }
        $stmtStatus->close();

        header('Location: listar_perguntas.php');
        exit;
    } else {
        $_SESSION['mensagem'] = "Erro ao registrar resposta: " . $stmt->error;
        header('Location: listar_perguntas.php');
        exit;
    }

    $stmt->close();
} else {
    $_SESSION['mensagem'] = "Dados inválidos!";
    header('Location: listar_perguntas.php');
    exit;
}

$oMysql->close();
?>
