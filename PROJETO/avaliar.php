<?php
session_start();
include_once 'conecta_db.php';

$oMysql = conecta_db();

$resposta_id = filter_input(INPUT_POST, 'resposta_id', FILTER_VALIDATE_INT);
$aluno_id = filter_input(INPUT_POST, 'aluno_id', FILTER_VALIDATE_INT);
$nota = filter_input(INPUT_POST, 'nota', FILTER_VALIDATE_INT);

if ($resposta_id === false || $aluno_id === false || $nota === false || $nota < 0 || $nota > 5) {
    die("Dados inválidos.");
}

// Verifica se a resposta existe, pertence à pergunta do aluno e pega o respondente_tipo
$query = "
    SELECT p.usuario_codigo, r.respondente_tipo
    FROM respostas r
    JOIN perguntas p ON r.codigo_pergunta = p.codigo_pergunta
    WHERE r.codigo_resposta = ?
";

$stmt = $oMysql->prepare($query);
$stmt->bind_param("i", $resposta_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Resposta não encontrada.");
}

$row = $result->fetch_assoc();

if ($row['usuario_codigo'] != $aluno_id) {
    die("Você não tem permissão para avaliar esta resposta.");
}

// Bloqueia avaliação se respondente for professor
if ($row['respondente_tipo'] === 'professor') {
    echo "
        <script>
            alert('Esta resposta foi dada por um professor e não pode ser avaliada.');
            window.location.href = 'home_aluno.php';
        </script>
    ";
    exit;
}

// Se chegou aqui, respondente é monitor, permite avaliação
$query = "INSERT INTO avaliacoes (aluno_id, resposta_id, nota) VALUES (?, ?, ?)
          ON DUPLICATE KEY UPDATE nota = VALUES(nota), data_avaliacao = CURRENT_TIMESTAMP";

$stmt = $oMysql->prepare($query);
$stmt->bind_param("iii", $aluno_id, $resposta_id, $nota);

if ($stmt->execute()) {
    echo "
        <script>
            alert('Avaliação registrada com sucesso!');
            window.location.href = 'home_aluno.php';
        </script>
    ";
} else {
    $erro = addslashes($stmt->error);
    echo "
        <script>
            alert('Erro ao registrar avaliação: {$erro}');
            window.history.back();
        </script>
    ";
}

$stmt->close();
$oMysql->close();
?>
