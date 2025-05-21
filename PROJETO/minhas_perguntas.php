<?php 
include_once 'conecta_db.php'; 
include "header.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: login.php");
    exit();
}

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$idAluno = $_SESSION['id'];

$query = "
    SELECT 
        p.codigo_pergunta, 
        p.enunciado, 
        p.data_criacao, 
        d.nome_disciplina, 
        r.resposta,
        r.codigo_resposta,
        a.id AS codigo_avaliacao
    FROM perguntas p
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    LEFT JOIN avaliacoes a ON r.codigo_resposta = a.resposta_id AND a.aluno_id = ? 
    WHERE p.usuario_codigo = ?
    ORDER BY p.data_criacao DESC
";

$stmt = $oMysql->prepare($query);
$stmt->bind_param("ii", $idAluno, $idAluno);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Minhas Perguntas</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4">Minhas Perguntas</h3>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
            $status = !empty($row['resposta']) ? 'respondida' : 'aguardando';
            $statusClass = $status === 'respondida' ? 'status-respondida' : 'status-aguardando';
        ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="tag"><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?></span>
                        <span class="tag"><strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?></span>
                        <span class="tag"><strong>Aluno:</strong> <?= htmlspecialchars($_SESSION['nome']) ?></span>
                        <span class="tag <?= $statusClass ?>"><strong>Status:</strong> <?= ucfirst($status) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'])) ?></p>
                    
                    <?php if (!empty($row['resposta'])): ?>
                        <hr>
                        <p><strong>Resposta:</strong><br><?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
                        <hr>

                        <?php if (empty($row['codigo_avaliacao'])): ?>
                            <form method="POST" action="avaliar.php" class="mt-2">
                                <label for="nota_<?= $row['codigo_pergunta'] ?>" class="form-label">Avalie esta resposta:</label>
                                <select name="nota" id="nota_<?= $row['codigo_pergunta'] ?>" class="form-select mb-2" required>
                                    <option value="">Selecione</option>
                                    <?php for ($i = 0; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> estrela<?= $i != 1 ? 's' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                                <input type="hidden" name="resposta_id" value="<?= $row['codigo_resposta'] ?>">
                                <input type="hidden" name="aluno_id" value="<?= $_SESSION['id'] ?>">
                                <button type="submit" class="btn btn-primary">Avaliar Resposta</button>
                            </form>
                        <?php else: ?>
                            <p class="text-success mt-2">Você já avaliou esta resposta.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Você ainda não fez nenhuma pergunta.</div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$oMysql->close();
?>
