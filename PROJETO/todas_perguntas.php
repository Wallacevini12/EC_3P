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

$idAlunoLogado = $_SESSION['id'];
$tipoUsuarioLogado = $_SESSION['tipo_usuario'];

// Query que traz todas as perguntas, com nomes do aluno criador, do respondente, e info da avaliação feita pelo aluno logado
$query = "
    SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, p.status,
           p.usuario_codigo,  -- adiciona aqui
           criador.nome AS nome_aluno_criador,
           d.nome_disciplina,
           r.resposta,
           r.codigo_resposta,
           u.tipo_usuario AS tipo_respondente,
           u.nome AS nome_respondente,
           a.id AS codigo_avaliacao,
           a.nota AS nota_avaliacao
    FROM perguntas p
    JOIN usuarios criador ON p.usuario_codigo = criador.id
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    LEFT JOIN usuarios u ON r.respondente_id = u.id
    LEFT JOIN avaliacoes a ON r.codigo_resposta = a.resposta_id AND a.aluno_id = ?
    ORDER BY p.data_criacao DESC
";

$stmt = $oMysql->prepare($query);
$stmt->bind_param("i", $idAlunoLogado);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    body {
        padding-top: 100px;
        background-color: #f4f4f4;
    }
</style>

<div class="container">
    <h1 class="mb-4 text-center">Todas as Perguntas</h1>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
            // Status e classe badge
            $statusClass = '';
            $statusLabel = ucfirst($row['status']);
            if ($row['status'] === 'respondida') {
                $statusClass = 'badge bg-success';
            } elseif ($row['status'] === 'aguardando resposta') {
                $statusClass = 'badge bg-danger';
            } else {
                $statusClass = 'badge bg-secondary';
            }

            // Define se pode avaliar: só quem criou a pergunta e se resposta não é de professor e ainda não avaliou
            $podeAvaliar = (
                $row['codigo_resposta'] !== null && // tem resposta
                $row['nome_aluno_criador'] !== null &&
                $row['nome_respondente'] !== null &&
                $row['tipo_respondente'] !== 'professor' &&
                $row['codigo_avaliacao'] === null &&
                $row['nome_aluno_criador'] === $_SESSION['nome'] // ou comparar pelo ID do criador?
            );

            // Como só temos o nome, para segurança melhor comparar pelo id do criador da pergunta
            // Como não pegamos o id do criador na query, vamos pegar:
            $idCriador = null;
            // Peça para ajustar a query se quiser comparar por id (sugiro adicionar p.usuario_codigo)
        ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-secondary">
                            <strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?>
                        </span>
                        <span class="badge bg-secondary">
                            <strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno_criador']) ?>
                        </span>
                        <?php if (!empty($row['nome_respondente'])): ?>
                            <span class="badge bg-info">
                                <strong>Respondente:</strong> <?= htmlspecialchars($row['nome_respondente']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="<?= $statusClass ?>">
                            <strong>Status:</strong> <?= $statusLabel ?>
                        </span>
                    </div>

                    <?php
                    // Botão Avaliar: só aparece se o aluno logado for o criador da pergunta e ainda não avaliou e resposta não for de professor
                    if (!empty($row['resposta'])): 
                        $isCriador = $row['usuario_codigo'] == $idAlunoLogado;
                        $respondenteTipo = $row['tipo_respondente'];
                        $avaliacaoFeita = !empty($row['codigo_avaliacao']);
                        if ($isCriador) {
                            if ($respondenteTipo === 'professor') {
                                echo '<span class="text-muted small">Resposta feita por Professor - Não é possível avaliar</span>';
                            } elseif ($avaliacaoFeita) {
                                echo '<span class="text-success small">Você já avaliou esta resposta.</span>';
                            } else {
                                ?>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#avaliarModal<?= $row['codigo_resposta'] ?>">
                                    Avaliar
                                </button>
                                <?php
                            }
                        }
                    endif;
                    ?>
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

            <?php if (!empty($row['resposta']) && $row['tipo_respondente'] !== 'professor' && empty($row['codigo_avaliacao']) && $row['usuario_codigo'] == $idAlunoLogado): ?>
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
                                    <input type="hidden" name="aluno_id" value="<?= $idAlunoLogado ?>">
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
        <div class="alert alert-info text-center">Nenhuma pergunta cadastrada.</div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$oMysql->close();
?>