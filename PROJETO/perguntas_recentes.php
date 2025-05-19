<?php
// Incluir arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; 
include "header.php";

// Conecta ao banco
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$query = "
    SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, p.status,
    u.nome AS nome_aluno, 
    d.nome_disciplina, 
    r.resposta,
    r.codigo_resposta
FROM perguntas p
JOIN usuarios u ON p.usuario_codigo = u.id
JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
ORDER BY p.data_criacao DESC
";

$result = $oMysql->query($query);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Perguntas Recentes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4">Perguntas Recentes</h3>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
            $statusClass = $row['status'] === 'respondida' ? 'status-respondida' : 'status-aguardando';
        ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="tag"><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?></span>
                        <span class="tag"><strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?></span>
                        <span class="tag"><strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno']) ?></span>
                        <span class="tag <?= $statusClass ?>"><strong>Status:</strong> <?= ucfirst($row['status']) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'])) ?></p>
                    <?php if (!empty($row['resposta'])): ?>
                        <hr>
                        <p><strong>Resposta:</strong><br><?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
                        <hr>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Nenhuma pergunta encontrada.</div>
    <?php endif; ?>
</div>

</body>
</html>

<?php $oMysql->close(); ?>
