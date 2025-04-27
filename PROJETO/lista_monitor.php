<?php
session_start();

include_once 'conecta_db.php';
include "header.php";

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Buscar monitores com suas disciplinas
$sql = "SELECT u.nome, u.email, u.curso, d.nome_disciplina
        FROM usuarios u
        INNER JOIN monitor m ON u.id = m.id
        LEFT JOIN disciplinas_possuem_monitores dpm ON m.id = dpm.monitor_codigo
        LEFT JOIN disciplinas d ON dpm.disciplina_codigo = d.codigo_disciplina
        ORDER BY u.nome ASC";

$result = $oMysql->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Monitores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-7">

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
                        <td><?php echo htmlspecialchars($monitor['nome']); ?></td>
                        <td><?php echo htmlspecialchars($monitor['email']); ?></td>
                        <td><?php echo htmlspecialchars($monitor['curso']); ?></td>
                        <td>
                            <?php 
                                echo $monitor['nome_disciplina'] 
                                ? htmlspecialchars($monitor['nome_disciplina']) 
                                : '<span class="text-danger">Sem disciplina vinculada</span>'; 
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Nenhum monitor cadastrado ainda.
        </div>
    <?php endif; ?>

</div>

</body>
</html>

<?php
$oMysql->close();
?>
