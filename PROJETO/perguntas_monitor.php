<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include_once 'conecta_db.php';
include "header.php";

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$id_monitor = $_SESSION['id'];

// Buscar disciplinas do monitor
$sql_disciplina = "SELECT disciplina_codigo FROM monitores_possuem_disciplinas WHERE monitor_codigo = $id_monitor";
$result_disciplina = $oMysql->query($sql_disciplina);

$disciplinas = [];
if ($result_disciplina) {
    while ($row = $result_disciplina->fetch_assoc()) {
        $disciplinas[] = $row['disciplina_codigo'];
    }
}

// Montar consulta principal
if (count($disciplinas) > 0) {
    $lista_disciplinas = implode(',', $disciplinas);
    $query = "
        SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina, p.disciplina_codigo
        FROM perguntas p
        JOIN usuarios u ON p.usuario_codigo = u.id
        JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
        WHERE p.disciplina_codigo IN ($lista_disciplinas)
          AND (p.encaminhada IS NULL OR p.encaminhada = 0)
          AND (p.respondida IS NULL OR p.respondida = 0)
        ORDER BY p.data_criacao DESC
    ";
} else {
    $query = "SELECT * FROM perguntas WHERE 1=0"; // Nenhuma disciplina atribuída
}

$result = $oMysql->query($query);
if (!$result) {
    die("Erro ao buscar perguntas: " . $oMysql->error);
}
?>

<div class="container mt-3">
    <h2 class="mb-4">Lista de Perguntas para Monitor</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success" id="msgAlert"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>

            <?php
            // Verificar se existe professor na disciplina da pergunta
            $disciplina_codigo = $row['disciplina_codigo'];
            $existe_professor = false;
            if ($disciplina_codigo) {
                $sql_professor = "SELECT COUNT(*) as total FROM professores_possuem_disciplinas WHERE disciplina_codigo = $disciplina_codigo";
                $res_professor = $oMysql->query($sql_professor);
                if ($res_professor) {
                    $dados_prof = $res_professor->fetch_assoc();
                    $existe_professor = ($dados_prof['total'] > 0);
                }
            }
            ?>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary"><strong>ID:</strong> <?= $row['codigo_pergunta'] ?></span>
                        <span class="badge bg-secondary"><strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge bg-secondary"><strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge bg-secondary"><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8')) ?></p>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#responderModal<?= $row['codigo_pergunta'] ?>">Responder</button>

                        <?php if ($existe_professor): ?>
                            <form action="encaminhar_pergunta.php" method="POST" class="d-inline ms-2 encaminhar-form">
                                <input type="hidden" name="codigo_pergunta" value="<?= $row['codigo_pergunta'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Encaminhar</button>
                            </form>
                        <?php else: ?>
                            <span class="ms-2 text-danger fw-semibold" title="Nenhum professor cadastrado nesta disciplina">
                                Nenhum professor cadastrado para encaminhar
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Modal de resposta -->
            <div class="modal fade" id="responderModal<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="responderModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="responder_salvar.php">
                            <div class="modal-header">
                                <h5 class="modal-title">Responder à Pergunta</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Pergunta</label>
                                    <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sua Resposta</label>
                                    <textarea class="form-control" name="resposta" rows="4" required></textarea>
                                </div>
                                <input type="hidden" name="codigo_pergunta" value="<?= $row['codigo_pergunta'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Enviar Resposta</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">Nenhuma pergunta registrada para você no momento.</div>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.encaminhar-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            const confirmar = confirm('Tem certeza que deseja encaminhar esta pergunta?');
            if (!confirmar) {
                event.preventDefault();
            }
        });
    });


        // Espera 3 segundos (3000 ms) e depois esconde a mensagem
    setTimeout(() => {
        const msgAlert = document.getElementById('msgAlert');
        if (msgAlert) {
            // Faz um efeito de desvanecimento (fade out) suave
            msgAlert.style.transition = 'opacity 0.5s ease';
            msgAlert.style.opacity = '0';
            setTimeout(() => msgAlert.remove(), 500); // Remove do DOM depois da transição
        }
    }, 3000);
</script>



<?php $oMysql->close(); ?>