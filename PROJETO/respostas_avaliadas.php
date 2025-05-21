<?php
include_once 'conecta_db.php'; 
include "header.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é aluno
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: login.php");
    exit();
}

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$idAluno = $_SESSION['id'];

// Consulta perguntas com avaliação feita pelo aluno logado
$query = "
    SELECT 
        p.codigo_pergunta,
        p.enunciado,
        d.nome_disciplina,
        r.resposta,
        a.nota,
        u.nome AS nome_respondente,
        r.respondente_tipo
    FROM perguntas p
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    JOIN usuarios u ON r.respondente_id = u.id
    JOIN avaliacoes a ON r.codigo_resposta = a.resposta_id
    WHERE p.usuario_codigo = ? AND a.aluno_id = ?
    ORDER BY p.data_criacao DESC
";

$stmt = $oMysql->prepare($query);
if (!$stmt) {
    die("Erro ao preparar a consulta: " . $oMysql->error);
}
$stmt->bind_param("ii", $idAluno, $idAluno);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Perguntas Avaliadas</title>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Perguntas Avaliadas</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Enunciado</th>
                    <th>Disciplina</th>
                    <th>Resposta</th>
                    <th>Respondido por</th>
                    <th>Avaliação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['enunciado']) ?></td>
                        <td><?= htmlspecialchars($row['nome_disciplina']) ?></td>
                        <td><?= htmlspecialchars($row['resposta']) ?></td>
                        <td>
                            <?= htmlspecialchars($row['nome_respondente']) ?>
                            (<?= htmlspecialchars($row['respondente_tipo']) ?>)
                        </td>
                        <td>
                            <?= str_repeat('⭐', (int)$row['nota']) ?>
                            <?= $row['nota'] ?> estrela<?= $row['nota'] != 1 ? 's' : '' ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">Você ainda não avaliou nenhuma resposta.</div>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$oMysql->close();
?>
