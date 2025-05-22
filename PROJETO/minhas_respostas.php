<?php
session_start();

include_once 'conecta_db.php';

// Verifica se o usuário está logado e é monitor
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit();
}

include "header.php";

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$id_monitor = $_SESSION['id'];

$query = "
    SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, p.status,
           u.nome AS nome_aluno, 
           d.nome_disciplina, 
           r.resposta,
           r.codigo_resposta,
           a.nota
    FROM respostas r
    INNER JOIN perguntas p ON r.codigo_pergunta = p.codigo_pergunta
    INNER JOIN usuarios u ON p.usuario_codigo = u.id
    INNER JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    LEFT JOIN avaliacoes a ON r.codigo_resposta = a.resposta_id
    WHERE r.respondente_id = ?
    ORDER BY p.data_criacao DESC
";

$stmt = $oMysql->prepare($query);
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>Minhas Respostas</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="tag"><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?></span>
                        <span class="tag"><strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?></span>
                        <span class="tag"><strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno']) ?></span>
                        <span class="tag status-respondida"><strong>Status:</strong> Respondida</span>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <hr>
                    <p><strong>Minha Resposta:</strong><br><?= nl2br(htmlspecialchars($row['resposta'], ENT_QUOTES, 'UTF-8')) ?></p>

                    <?php if ($row['nota'] !== null): ?>
                        <hr>
                        <p><strong>Avaliação:</strong> 
                            <?= str_repeat('⭐', (int)$row['nota']) ?> 
                            (<?= (int)$row['nota'] ?> estrela<?= $row['nota'] != 1 ? 's' : '' ?>)
                        </p>
                    <?php else: ?>
                        <hr>
                        <p><em>Esta resposta ainda não foi avaliada.</em></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Você ainda não respondeu nenhuma pergunta.</div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$oMysql->close();
?>
