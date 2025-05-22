<?php
session_start();

include_once 'conecta_db.php';
include "header.php";

// Conecta ao banco
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Consulta perguntas com respostas e nome de quem respondeu
$query = "
    SELECT 
        p.codigo_pergunta, 
        p.enunciado, 
        p.data_criacao, 
        p.status,
        u.nome AS nome_aluno, 
        d.nome_disciplina, 
        r.resposta,
        r.codigo_resposta,
        ur.nome AS nome_respondente,
        r.respondente_tipo
    FROM perguntas p
    JOIN usuarios u ON p.usuario_codigo = u.id
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    JOIN usuarios ur ON r.respondente_id = ur.id
    ORDER BY p.data_criacao DESC;
";

$result = $oMysql->query($query);
?>

<div class="container pt-5 bg-light" style="min-height:100vh;">
    <h1 class="mb-4 text-center">Perguntas Respondidas</h1>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary">
                            <strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno']) ?>
                        </span>
                        <span class="badge bg-primary">
                            <strong>Respondido por:</strong> <?= htmlspecialchars($row['nome_respondente']) ?>
                        </span>
                        <span class="badge bg-success">
                            <strong>Status:</strong> Respondida
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'])) ?></p>
                    <hr>
                    <p><strong>Resposta:</strong><br><?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">Nenhuma pergunta respondida encontrada.</div>
    <?php endif; ?>
</div>


<?php $oMysql->close(); ?>