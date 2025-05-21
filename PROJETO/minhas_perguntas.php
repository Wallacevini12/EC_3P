<?php
// Incluir arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; 
include "header.php";

// Iniciar sessão, se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e é aluno
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: login.php");
    exit();
}

// Conecta ao banco
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$idAluno = $_SESSION['id'];

// Consulta perguntas feitas pelo aluno logado, incluindo se já existe avaliação da resposta por esse aluno
$query = "
    SELECT 
        p.codigo_pergunta, 
        p.enunciado, 
        p.data_criacao, 
        d.nome_disciplina, 
        r.resposta,
        r.codigo_resposta,
        a.id AS codigo_avaliacao  -- alias corrigido
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

<div class="container mt-3">
    <h2>Minhas Perguntas</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Enunciado</th>
                    <th>Disciplina</th>
                    <th>Data da Pergunta</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['codigo_pergunta'] ?></td>
                        <td><?= htmlspecialchars(substr($row['enunciado'], 0, 100)) . (strlen($row['enunciado']) > 100 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($row['nome_disciplina'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_criacao'])) ?></td>
                        <td>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#respostaModal<?= $row['codigo_pergunta'] ?>">
                                Ver Resposta
                            </button>

                            <?php if (!empty($row['resposta']) && empty($row['codigo_avaliacao'])): ?>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#respostaModal<?= $row['codigo_pergunta'] ?>">
                                    Avaliar Resposta
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Modal -->
                    <div class="modal fade" id="respostaModal<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="respostaModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="respostaModalLabel<?= $row['codigo_pergunta'] ?>">
                                        <?= htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8') ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Resposta:</strong></p>
                                    <p><?= htmlspecialchars($row['resposta'] ?? 'Ainda não respondida.', ENT_QUOTES, 'UTF-8') ?></p>

                                    <?php if (!empty($row['resposta']) && empty($row['codigo_avaliacao'])): ?>
                                        <hr>
                                        <form method="POST" action="avaliar.php">
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
                                    <?php elseif (!empty($row['resposta']) && !empty($row['codigo_avaliacao'])): ?>
                                        <p class="text-success mt-2">Você já avaliou esta resposta.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="alert alert-info">Você ainda não fez nenhuma pergunta.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
// Fecha conexões
$stmt->close();
$oMysql->close();
?>
