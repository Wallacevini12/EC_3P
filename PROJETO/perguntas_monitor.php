<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include "header.php";
include_once 'conecta_db.php';

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$id_monitor = $_SESSION['id'];

// Buscar todas as disciplinas que o monitor possui
$sql_disciplina = "SELECT disciplina_codigo FROM monitores_possuem_disciplinas WHERE monitor_codigo = $id_monitor";
$result_disciplina = $oMysql->query($sql_disciplina);

$disciplinas = [];
if ($result_disciplina) {
    while ($row = $result_disciplina->fetch_assoc()) {
        $disciplinas[] = $row['disciplina_codigo'];
    }
}

if (count($disciplinas) > 0) {
    $lista_disciplinas = implode(',', $disciplinas);

    // Buscar perguntas NÃO encaminhadas e NÃO respondidas nas disciplinas do monitor
    $query = "
        SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina
        FROM perguntas p
        JOIN usuarios u ON p.usuario_codigo = u.id
        JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
        WHERE p.disciplina_codigo IN ($lista_disciplinas)
          AND (p.encaminhada IS NULL OR p.encaminhada = 0)
          AND (p.respondida IS NULL OR p.respondida = 0)
        ORDER BY p.data_criacao DESC
    ";
} else {
    $query = "
        SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina
        FROM perguntas p
        JOIN usuarios u ON p.usuario_codigo = u.id
        JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
        WHERE 1=0
    ";
}

$result = $oMysql->query($query);

if (!$result) {
    die("Erro ao buscar perguntas: " . $oMysql->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Lista de Perguntas para Monitor</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
    <h2>Lista de Perguntas para Monitor</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Enunciado</th>
                    <th>Aluno</th>
                    <th>Disciplina</th>
                    <th>Data da Pergunta</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['codigo_pergunta'] ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth($row['enunciado'], 0, 100, '...')) ?></td>
                        <td><?= htmlspecialchars($row['nome_aluno'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['nome_disciplina'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                        <td>
                            <!-- Botão responder abre modal -->
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#responderModal<?= $row['codigo_pergunta'] ?>">Responder</button>

                            <!-- Botão encaminhar faz POST para encaminhar a pergunta -->
                            <form action="encaminhar_pergunta.php" method="POST" class="d-inline ms-2">
                                <input type="hidden" name="codigo_pergunta" value="<?= $row['codigo_pergunta'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Encaminhar</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal para responder a pergunta -->
                    <div class="modal fade" id="responderModal<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="responderModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="responder_salvar.php">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="responderModalLabel<?= $row['codigo_pergunta'] ?>">Responder à Pergunta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="pergunta<?= $row['codigo_pergunta'] ?>" class="form-label">Pergunta</label>
                                            <textarea class="form-control" id="pergunta<?= $row['codigo_pergunta'] ?>" name="pergunta" rows="4" readonly><?= htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8') ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="resposta<?= $row['codigo_pergunta'] ?>" class="form-label">Sua Resposta</label>
                                            <textarea class="form-control" id="resposta<?= $row['codigo_pergunta'] ?>" name="resposta" rows="4" required></textarea>
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
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">Nenhuma pergunta registrada para você no momento.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$oMysql->close();
?>
