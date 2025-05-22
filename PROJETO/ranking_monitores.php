<?php
session_start();
require_once 'conecta_db.php';
include "header.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$conn = conecta_db();

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

<div class="container" style="margin-top: 70px;">
    <h2>Top 10 Monitores</h2>

    <?php if ($result_ranking && $result_ranking->num_rows > 0): ?>
        <ol class="list-group list-group-numbered mt-4">
            <?php while ($row = $result_ranking->fetch_assoc()): ?>
                <?php
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
        <p class="alert alert-info mt-3">Nenhum monitor avaliado ainda.</p>
    <?php endif; ?>

</div>

<?php $conn->close(); ?>
