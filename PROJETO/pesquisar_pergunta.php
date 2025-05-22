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

$buscar = '';
$resultados = [];

if (isset($_GET['buscar'])) {
    $buscar = trim($_GET['buscar']);

    if (!empty($buscar)) {
        $buscar_esc = $oMysql->real_escape_string($buscar);
        $palavras = explode(' ', $buscar_esc);

        $condicoes = [];
        foreach ($palavras as $palavra) {
            $palavra = trim($palavra);
            if ($palavra !== '') {
                $condicoes[] = "(" .
                    "p.enunciado LIKE '%$palavra%' OR " .
                    "u.nome LIKE '%$palavra%' OR " .
                    "resp.nome LIKE '%$palavra%' OR " .        // <== adiciona busca pelo nome do respondente
                    "d.nome_disciplina LIKE '%$palavra%' OR " .
                    "DATE_FORMAT(p.data_criacao, '%d/%m/%Y') LIKE '%$palavra%'" .
                ")";
            }
        }

        if (count($condicoes) > 0) {
            $where = implode(' AND ', $condicoes);

            // Ajuste na query para buscar nome do respondente
            $query = "
                SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, p.status,
                    u.nome AS nome_aluno,
                    d.nome_disciplina,
                    r.resposta,
                    r.codigo_resposta,
                    resp.nome AS nome_respondente  -- adiciona o nome do respondente
                FROM perguntas p
                JOIN usuarios u ON p.usuario_codigo = u.id
                JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
                LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
                LEFT JOIN usuarios resp ON r.respondente_id = resp.id  -- join para pegar nome do respondente
                WHERE $where
                ORDER BY p.data_criacao DESC
            ";

            $result = $oMysql->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $resultados[] = $row;
                }
            }
        }
    }
}
?>

<div class="container mt-4">
    <h3 class="mb-4 text-center">Resultados</h3>

    <?php if (isset($_GET['buscar'])): ?>
        <?php if (count($resultados) > 0): ?>
            <?php foreach ($resultados as $row):
                // Define classe Bootstrap para o status
                if ($row['status'] === 'respondida') {
                    $statusClass = 'badge bg-success';
                } elseif ($row['status'] === 'aguardando resposta') {
                    $statusClass = 'badge bg-danger';
                } else {
                    $statusClass = 'badge bg-secondary';
                }
            ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-secondary">
                                <strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?>
                            </span>
                            <span class="badge bg-secondary">
                                <strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?>
                            </span>
                            <span class="badge bg-secondary">
                                <strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno']) ?>
                            </span>
                            <?php if (!empty($row['nome_respondente'])): ?>
                                <span class="badge bg-info">
                                    <strong>Respondente:</strong> <?= htmlspecialchars($row['nome_respondente']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="<?= $statusClass ?>">
                                <strong>Status:</strong> <?= ucfirst($row['status']) ?>
                            </span>
                        </div>
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
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">Nenhuma pergunta encontrada.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>
