<?php
include_once 'conecta_db.php'; 
include "header.php";

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$query = "
    SELECT 
    p.codigo_pergunta, 
    p.enunciado, 
    p.data_criacao, 
    p.status,
    u.nome AS nome_aluno, 
    d.nome_disciplina, 
    r.resposta,
    r.codigo_resposta  -- Adicione isto
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
    <style>
        .card {
            margin-bottom: 1rem;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .tag {
            background-color: #f1f1f1;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 0.85rem;
            margin: 2px;
        }
        .tag.status-respondida {
            background-color: #d4edda;
            color: #155724;
        }
        .tag.status-aguardando {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4">Perguntas Recentes</h3>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
            $statusClass = $row['status'] === 'respondida' ? 'status-respondida' : 'status-aguardando';
        ?>
            <div class="card shadow-sm">
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
                        <!-- Formulário de Avaliação -->
                        <form method="post" action="avaliar.php" class="mt-3">
                            <label for="nota_<?= $row['codigo_pergunta'] ?>" class="form-label">Avalie esta resposta:</label>
                            <select name="nota" id="nota_<?= $row['codigo_pergunta'] ?>" class="form-select" required>
                                <option value="">Selecione</option>
                                <?php for ($i = 0; $i <= 5; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> estrela<?= $i != 1 ? 's' : '' ?></option>
                                <?php endfor; ?>
                            </select>
                            <input type="hidden" name="resposta_id" value="<?= $row['codigo_resposta'] ?>">
                            <input type="hidden" name="aluno_id" value="<?= $_SESSION['id'] ?>"> 
                            <button type="submit" class="btn btn-primary mt-2">Enviar Avaliação</button>
                        </form>
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
