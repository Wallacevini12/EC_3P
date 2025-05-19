<?php
session_start();
include_once 'conecta_db.php';

$oMysql = conecta_db();

$resposta_id = $_POST['resposta_id'];
$aluno_id = $_POST['aluno_id'];
$nota = $_POST['nota'];

// Verificação: esta resposta é de uma pergunta feita por este aluno?
$query = "
    SELECT p.usuario_codigo
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

// Verifica se o aluno já avaliou essa resposta
$verifica = $oMysql->prepare("SELECT id FROM avaliacoes WHERE aluno_id = ? AND resposta_id = ?");
$verifica->bind_param("ii", $aluno_id, $resposta_id);
$verifica->execute();
$verifica->store_result();

if ($verifica->num_rows > 0) {
    echo "
        <script>
            alert('Você já avaliou essa resposta.');
            window.location.href = 'home_aluno.php';
        </script>
    ";
    exit();
}
$verifica->close();

// Se passou na verificação, insere a avaliação
$query = "INSERT INTO avaliacoes (resposta_id, aluno_id, nota) VALUES (?, ?, ?)";
$stmt = $oMysql->prepare($query);
$stmt->bind_param("iii", $resposta_id, $aluno_id, $nota);

if ($stmt->execute()) {
    echo "
        <script>
            alert('Avaliação registrada com sucesso!');
            window.location.href = 'home_aluno.php';
        </script>
    ";
} else {
    echo "
        <script>
            alert('Erro ao registrar avaliação: " . addslashes($stmt->error) . "');
            window.history.back();
        </script>
    ";
}

$stmt->close();
$oMysql->close();
?>