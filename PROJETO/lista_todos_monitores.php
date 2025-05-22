<?php
session_start();

include_once 'conecta_db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include "header.php";

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$sql = "SELECT DISTINCT u.nome, u.email, u.curso, d.nome_disciplina
        FROM usuarios u
        INNER JOIN monitor m ON u.id = m.id
        INNER JOIN monitores_possuem_disciplinas mpd ON m.id = mpd.monitor_codigo
        INNER JOIN disciplinas d ON mpd.disciplina_codigo = d.codigo_disciplina
        ORDER BY u.nome ASC";

$result = $oMysql->query($sql);
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Monitores</h2>

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
            Nenhum monitor encontrado.
        </div>
    <?php endif; ?>
</div>

<?php
$oMysql->close();
?>