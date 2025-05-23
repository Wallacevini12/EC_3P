<?php
session_start();

include_once 'conecta_db.php';
include "header.php";

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
        u.tipo_usuario AS tipo_respondente,
        a.id AS codigo_avaliacao,
        p.usuario_codigo AS autor_id,
        u.nome AS nome_respondente,
        ua.nome AS nome_autor
    FROM perguntas p
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    LEFT JOIN usuarios u ON r.respondente_id = u.id
    JOIN usuarios ua ON p.usuario_codigo = ua.id
    LEFT JOIN avaliacoes a ON r.codigo_resposta = a.resposta_id AND a.aluno_id = ?
    WHERE DATE(p.data_criacao) = CURDATE()
    ORDER BY p.data_criacao DESC
";

$stmt = $oMysql->prepare($query);
$stmt->bind_param("i", $idAluno);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Perguntas Recentes</title>
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-4">Perguntas Recentes</h3>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $status = !empty($row['resposta']) ? 'respondida' : 'aguardando resposta';
                $statusClass = ($status === 'respondida') ? 'badge bg-success' : 'badge bg-danger';
                $respondente = $row['tipo_respondente'] ?? null;
                $ehAutor = $_SESSION['id'] == $row['autor_id'];
            ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-secondary"><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?></span>
                        <span class="badge bg-secondary"><strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?></span>
                        <span class="badge bg-secondary"><strong>Aluno:</strong> <?= htmlspecialchars($row['nome_autor']) ?></span>
                        <?php if (!empty($row['nome_respondente'])): ?>
                            <span class="badge bg-info"><strong>Respondente:</strong> <?= htmlspecialchars($row['nome_respondente']) ?></span>
                        <?php endif; ?>
                        <span class="<?= $statusClass ?>"><strong>Status:</strong> <?= ucfirst($status) ?></span>
                    </div>
                    <?php if (!empty($row['resposta']) && $ehAutor): ?>
                        <?php if ($respondente === 'professor'): ?>
                            <span class="text-muted small">Resposta feita por Professor - Não é possível avaliar</span>
                        <?php elseif (!empty($row['codigo_avaliacao'])): ?>
                            <span class="text-success small">Você já avaliou esta resposta.</span>
                        <?php else: ?>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#avaliarModal<?= $row['codigo_resposta'] ?>">
                                Avaliar
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
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

            <?php if (!empty($row['resposta']) && $ehAutor && $respondente !== 'professor' && empty($row['codigo_avaliacao'])): ?>
                <!-- Modal de Avaliação -->
                <div class="modal fade" id="avaliarModal<?= $row['codigo_resposta'] ?>" tabindex="-1" aria-labelledby="avaliarModalLabel<?= $row['codigo_resposta'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="avaliar.php">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="avaliarModalLabel<?= $row['codigo_resposta'] ?>">Avaliar Resposta</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Escolha uma nota para esta resposta:</p>
                                    <select name="nota" class="form-select" required>
                                        <option value="">Selecione</option>
                                        <?php for ($i = 0; $i <= 5; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?> estrela<?= $i != 1 ? 's' : '' ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <input type="hidden" name="resposta_id" value="<?= $row['codigo_resposta'] ?>">
                                    <input type="hidden" name="aluno_id" value="<?= $_SESSION['id'] ?>">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Enviar Avaliação</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Nenhuma pergunta encontrada.</div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$oMysql->close();
?>
