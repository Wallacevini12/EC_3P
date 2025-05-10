<?php
session_start();

include_once 'conecta_db.php';
include "header.php";

// Verifica se o usuário está logado e é professor
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    header("Location: login.php");
    exit();
}

$professor_id = $_SESSION['id'];

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Exibe a ID do professor para verificação
// echo "ID do professor: " . $professor_id;

// Consulta: monitores que compartilham disciplinas com este professor
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

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Meus Monitores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <br>
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
                        <td><?php echo htmlspecialchars($monitor['nome']); ?></td>
                        <td><?php echo htmlspecialchars($monitor['email']); ?></td>
                        <td><?php echo htmlspecialchars($monitor['curso']); ?></td>
                        <td><?php echo htmlspecialchars($monitor['nome_disciplina']); ?></td>
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

</body>
</html>

<?php
$stmt->close();
$oMysql->close();
?>
