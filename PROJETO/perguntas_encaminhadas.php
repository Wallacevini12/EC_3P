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

// Buscar as disciplinas que o professor ministra
$sql_disciplinas = "SELECT disciplina_codigo FROM professores_possuem_disciplinas WHERE professor_codigo = $id_professor";
$res_disciplinas = $oMysql->query($sql_disciplinas);

$disciplinas = [];
if ($res_disciplinas) {
    while ($row = $res_disciplinas->fetch_assoc()) {
        $disciplinas[] = $row['disciplina_codigo'];
    }
}

$perguntas = [];

if (count($disciplinas) > 0) {
    $lista_disciplinas = implode(',', $disciplinas);

    // Buscar perguntas encaminhadas com disciplina correspondente
    $query = "
        SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina
        FROM perguntas p
        JOIN usuarios u ON p.usuario_codigo = u.id
        JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
        WHERE p.encaminhada = 1
          AND p.status = 'aguardando resposta'
          AND p.disciplina_codigo IN ($lista_disciplinas)
        ORDER BY p.data_criacao DESC
    ";

    $result = $oMysql->query($query);
    if (!$result) {
        die("Erro ao buscar perguntas encaminhadas: " . $oMysql->error);
    }
} else {
    $result = false; // Nenhuma disciplina atribuída ao professor
}
?>

<div class="container mt-4">
    <h3 class="mb-4">Perguntas Encaminhadas para Professor</h3>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary">
                            <strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno']) ?>
                        </span>
                        <span class="badge bg-danger">
                            <strong>Status:</strong> Aguardando Resposta
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'])) ?></p>
                    <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalResposta<?= $row['codigo_pergunta'] ?>">
                        Responder
                    </button>
                </div>
            </div>

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
                                    <input type="hidden" name="professor_id" value="<?= $_SESSION['id'] ?>">
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
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Nenhuma pergunta encaminhada para as suas disciplinas no momento.</div>
    <?php endif; ?>
</div>

<?php $oMysql->close(); ?>
