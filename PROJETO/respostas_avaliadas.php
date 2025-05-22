<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: login.php");
    exit();
}

include_once 'conecta_db.php';
include "header.php";

$conn = conecta_db();
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$idAluno = $_SESSION['id'];

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

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Erro ao preparar a consulta: " . $conn->error);
}
$stmt->bind_param("ii", $idAluno, $idAluno);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>Perguntas Avaliadas</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered mt-3">
            <thead class="table-light">
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
                        <td><?= nl2br(htmlspecialchars($row['resposta'])) ?></td>
                        <td><?= htmlspecialchars($row['nome_respondente']) ?></td>
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

<?php
$stmt->close();
$conn->close();
?>
