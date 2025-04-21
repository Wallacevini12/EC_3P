<?php
// Incluir arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; 

// Conectar ao banco de dados
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Consultar todas as perguntas no banco
$query = "
   SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, u.nome AS nome_aluno, d.nome_disciplina, r.resposta
    FROM perguntas p
    JOIN usuarios u ON p.usuario_codigo = u.id
    JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
    LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
    WHERE r.resposta IS NOT NULL
    ORDER BY p.data_criacao DESC
";

$result = $oMysql->query($query);

if (!$result) {
    die("Erro ao buscar perguntas: " . $oMysql->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Lista de Perguntas</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

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
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['codigo_pergunta'] ?></td>
                        <td><?= htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['nome_aluno'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['nome_disciplina'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $row['data_criacao'] ?></td>
                        <td>
                               <!-- Botão para abrir o modal e mostrar a pergunta e resposta -->
                               <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#respostaModal<?= $row['codigo_pergunta'] ?>">Mostrar Resposta</button>
                        </td>
                    </tr>

                    <!-- Modal para exibir a pergunta e a resposta -->
                    <div class="modal fade" id="respostaModal<?= $row['codigo_pergunta'] ?>" tabindex="-1" aria-labelledby="respostaModalLabel<?= $row['codigo_pergunta'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="respostaModalLabel<?= $row['codigo_pergunta'] ?>">Pergunta e Resposta</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">

                                    <!-- Exibe a pergunta -->
                                    <p><strong>Pergunta:</strong></p>
                                    <p><?= htmlspecialchars($row['enunciado'], ENT_QUOTES, 'UTF-8') ?></p>
                                    
                                    <!-- Exibe a resposta da pergunta -->
                                    <p><strong>Resposta:</strong> <?= htmlspecialchars($row['resposta'], ENT_QUOTES, 'UTF-8') ?></p>
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

</body>
</html>

<?php
// Fechar a conexão com o banco de dados
$oMysql->close();
?>