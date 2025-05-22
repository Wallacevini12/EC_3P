<?php
session_start();

include_once 'conecta_db.php';
include "header.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$query = "
    SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina, r.resposta
    FROM perguntas p
    JOIN usuarios u ON p.usuario_codigo = u.id
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    ORDER BY p.data_criacao DESC
";

$result = $oMysql->query($query);

if (!$result) {
    die("Erro ao buscar perguntas: " . $oMysql->error);
}
?>

<div class="container mt-3">
    <h2>Lista de Perguntas</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
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
                        <td><?= htmlspecialchars(substr($row['enunciado'], 0, 100)) . (strlen($row['enunciado']) > 100 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($row['nome_aluno'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['nome_disciplina'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $row['data_criacao'] ?></td>
                        <td>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#respostaModal<?= $row['codigo_pergunta'] ?>">Mostrar Resposta</button>
                        </td>
                    </tr>

                    <!-- Modal -->
                    <div class="modal fade" id="respostaModal<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="respostaModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="respostaModalLabel<?= $row['codigo_pergunta'] ?>">
                                        <?= htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8') ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Resposta:</strong></p>
                                    <p><?= htmlspecialchars($row['resposta'] ?? 'Ainda não respondida.', ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">Nenhuma pergunta registrada.</p>
    <?php endif; ?>
</div>

<?php
$oMysql->close();
?>
