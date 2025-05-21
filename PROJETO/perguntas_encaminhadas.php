<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login.php");
    exit;
}

include "header.php";
include_once 'conecta_db.php';

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$id_professor = $_SESSION['id'];

$query = "
    SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina
    FROM perguntas p
    JOIN usuarios u ON p.usuario_codigo = u.id
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    WHERE p.encaminhada = 1 AND p.status = 'aguardando resposta'
    ORDER BY p.data_criacao DESC
";

$result = $oMysql->query($query);

if (!$result) {
    die("Erro ao buscar perguntas encaminhadas: " . $oMysql->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Perguntas Encaminhadas</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
    <h2>Perguntas Encaminhadas para Professor</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Enunciado</th>
                    <th>Aluno</th>
                    <th>Disciplina</th>
                    <th>Data da Pergunta</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['codigo_pergunta'] ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($row['enunciado'], 0, 100, '...')) ?></td>
                        <td><?= htmlspecialchars($row['nome_aluno']) ?></td>
                        <td><?= htmlspecialchars($row['nome_disciplina']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalResposta<?= $row['codigo_pergunta'] ?>">
                                Responder
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="modalResposta<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="respostaModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="responder_pergunta_professor.php">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="respostaModalLabel<?= $row['codigo_pergunta'] ?>">Responder Pergunta</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Pergunta:</strong></p>
                                                <p><?= htmlspecialchars($row['enunciado']) ?></p>
                                                <div class="form-group">
                                                    <label for="resposta">Sua resposta:</label>
                                                    <textarea class="form-control" name="resposta" rows="4" required></textarea>
                                                    <input type="hidden" name="codigo_pergunta" value="<?= $row['codigo_pergunta'] ?>">
                                                    <input type="hidden" name="monitor_id" value="<?= $_SESSION['id'] ?>">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success">Enviar Resposta</button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">Nenhuma pergunta encaminhada no momento.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php $oMysql->close(); ?>
