<?php
session_start();
require_once 'conecta_db.php';
include "header.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$conn = conecta_db();

// Consulta para obter o ranking dos monitores
$sql_ranking = "
    SELECT 
        u.id,
        u.nome,
        COALESCE(AVG(ar.nota), 0) AS media_avaliacao,
        COUNT(ar.nota) AS total_avaliacoes
    FROM usuarios u
    LEFT JOIN respostas r ON u.id = r.respondente_id
    LEFT JOIN avaliacoes ar ON r.codigo_resposta = ar.resposta_id
    WHERE u.tipo_usuario = 'monitor'
    GROUP BY u.id, u.nome
    ORDER BY media_avaliacao DESC
    LIMIT 10
";

$result_ranking = $conn->query($sql_ranking);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ranking dos Monitores</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container" style="margin-top: 70px;">
    <h2>Top 10 Monitores</h2>

    <?php if ($result_ranking && $result_ranking->num_rows > 0): ?>
        <ol class="list-group list-group-numbered mt-4">
            <?php while ($row = $result_ranking->fetch_assoc()): ?>
                <?php
                    // Verifica se este monitor é o usuário logado
                    $is_logado = ($row['id'] == $_SESSION['id']);
                    $classe_destque = $is_logado ? 'bg-warning fw-bold' : '';
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center <?= $classe_destque ?>">
                    <?= htmlspecialchars($row['nome']) ?>
                    <span class="badge bg-primary rounded-pill">
                        <?= number_format($row['media_avaliacao'], 2) ?> ★ (<?= $row['total_avaliacoes'] ?> avaliações)
                    </span>
                </li>
            <?php endwhile; ?>
        </ol>
    <?php else: ?>
        <p>Nenhum monitor avaliado ainda.</p>
    <?php endif; ?>

    <br>
    <a href="index.php" class="btn btn-secondary">Voltar</a>
</div>
</body>
</html>