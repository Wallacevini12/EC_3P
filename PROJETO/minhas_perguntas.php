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
        a.nota AS nota_avaliacao
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
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Enunciado</th>
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
                        <td><?= htmlspecialchars($row['nome_disciplina'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $row['data_criacao'] ?></td>
                        <td>
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#respostaModal<?= $row['codigo_pergunta'] ?>">
                                Mostrar Resposta
                            </button>
                        </td>
                    </tr>

                    <!-- Modal para exibir a pergunta e a resposta -->
                    <div class="modal fade" id="respostaModal<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="respostaModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
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

                                    <?php if (!empty($row['resposta']) && $row['nota_avaliacao'] === null): ?>
                                        <!-- Mostrar formulário só se houver resposta E não houver avaliação -->
                                        <hr>
                                        <form method="POST" action="avaliar.php" class="mt-3">
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
                                    <?php elseif (!empty($row['resposta']) && $row['nota_avaliacao'] !== null): ?>
                                        <!-- Mensagem que já foi avaliada -->
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