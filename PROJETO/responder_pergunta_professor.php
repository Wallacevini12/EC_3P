<?php
session_start();

// Verifica se o usuário é professor
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login.php");
    exit;
}

require_once 'conecta_db.php';

$conn = conecta_db();
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Processa apenas requisições POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resposta = trim($_POST['resposta'] ?? '');
    $codigo_pergunta = intval($_POST['codigo_pergunta'] ?? 0);
    $professor_id = intval($_SESSION['id']);
    $respondente_tipo = 'professor';

    if (!empty($resposta) && $codigo_pergunta > 0) {
        // Grava a resposta
        $stmt = $conn->prepare("INSERT INTO respostas (codigo_pergunta, respondente_id, resposta, respondente_tipo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $codigo_pergunta, $professor_id, $resposta, $respondente_tipo);

        if ($stmt->execute()) {
            // Atualiza status da pergunta para respondida
            $conn->query("UPDATE perguntas SET status = 'respondida', respondida = 1 WHERE codigo_pergunta = $codigo_pergunta");

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

$conn->close();
?>
