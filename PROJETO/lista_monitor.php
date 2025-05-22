<?php
session_start();

include_once 'conecta_db.php';

// Verifica se o usuário está logado e é professor
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login.php");
    exit();
}

include "header.php";

$professor_id = $_SESSION['id'];

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$sql = "SELECT DISTINCT u.nome, u.email, u.curso, d.nome_disciplina
        FROM usuarios u
        INNER JOIN monitor m ON u.id = m.id
        INNER JOIN monitores_possuem_disciplinas mpd ON m.id = mpd.monitor_codigo
        INNER JOIN disciplinas d ON mpd.disciplina_codigo = d.codigo_disciplina
        INNER JOIN professores_possuem_disciplinas ppd ON d.codigo_disciplina = ppd.disciplina_codigo
        WHERE ppd.professor_codigo = ?
        ORDER BY u.nome ASC";

$stmt = $oMysql->prepare($sql);
if (!$stmt) {
    die("Erro na preparação da consulta: " . $oMysql->error);
}

$stmt->bind_param("i", $professor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-5">
    <h2 class="mb-4">Monitores Vinculados às Suas Disciplinas</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Curso</th>
                    <th>Disciplina</th>
                </tr>
            </thead>
            <tbody>
                <?php while($monitor = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($monitor['nome']) ?></td>
                        <td><?= htmlspecialchars($monitor['email']) ?></td>
                        <td><?= htmlspecialchars($monitor['curso']) ?></td>
                        <td><?= htmlspecialchars($monitor['nome_disciplina']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Nenhum monitor vinculado às suas disciplinas.
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$oMysql->close();
?>
