<?php
session_start();

include_once 'conecta_db.php';
include "header.php";

// Conecta ao banco
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Consulta perguntas com respostas
$query = "
    SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, p.status,
           u.nome AS nome_aluno, 
           d.nome_disciplina, 
           r.resposta,
           r.codigo_resposta
    FROM perguntas p
    JOIN usuarios u ON p.usuario_codigo = u.id
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    INNER JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    ORDER BY p.data_criacao DESC
";

$result = $oMysql->query($query);
?>

<div class="container mt-4">
    <h3 class="mb-4">Perguntas Respondidas</h3>

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
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'])) ?></p>
                    <hr>
                    <p><strong>Resposta:</strong><br><?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
                    <hr>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Nenhuma pergunta respondida encontrada.</div>
    <?php endif; ?>
</div>

<?php $oMysql->close(); ?>
