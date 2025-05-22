<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header("Location: login.php");
    exit;
}

include "header.php";
include_once 'conecta_db.php';

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Buscar todas as perguntas, sem filtro
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
$resultados = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $resultados[] = $row;
    }
}
?>

<style>
    body {
        padding-top: 100px;
        background-color: #f4f4f4;
    }
</style>

<div class="container">
    <h1 class="mb-4 text-center">Todas as Perguntas</h1>

    <?php if (count($resultados) > 0): ?>
        <?php foreach ($resultados as $row): 
            // Define classes Bootstrap para o status
            $statusClass = '';
            $statusLabel = ucfirst($row['status']);
            if ($row['status'] === 'respondida') {
                $statusClass = 'badge bg-success';
            } elseif ($row['status'] === 'aguardando resposta') {
                $statusClass = 'badge bg-danger';
            } else {
                $statusClass = 'badge bg-secondary';
            }
        ?>
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
                        <span class="<?= $statusClass ?>">
                            <strong>Status:</strong> <?= $statusLabel ?>
                        </span>
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
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">Nenhuma pergunta cadastrada.</div>
    <?php endif; ?>
</div>

<?php $oMysql->close(); ?>