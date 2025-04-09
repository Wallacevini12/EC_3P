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
    $resposta = $_POST['resposta']; // Resposta digitada
    $codigo_pergunta = $_POST['codigo_pergunta']; // Código da pergunta associada

    // Preparar a inserção na tabela 'respostas'
    $stmt = $oMysql->prepare("INSERT INTO respostas (codigo_pergunta, resposta, data_resposta) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $codigo_pergunta, $resposta);

    // Executar a inserção
    if ($stmt->execute()) {
        // Redirecionar com mensagem de sucesso
        $_SESSION['mensagem'] = "Sua resposta foi enviada com sucesso!";
        header('Location: listar_perguntas.php'); // Redireciona para listar perguntas
        exit;
    } else {
        // Exibir mensagem de erro
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
