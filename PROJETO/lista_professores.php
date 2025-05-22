<?php
session_start();

include_once 'conecta_db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include "header.php";

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Busca todos os professores e suas disciplinas associadas
$sql = "SELECT u.id, u.nome, u.email, u.curso, GROUP_CONCAT(d.nome_disciplina SEPARATOR ', ') AS disciplinas
        FROM usuarios u
        INNER JOIN professores_possuem_disciplinas ppd ON u.id = ppd.professor_codigo
        INNER JOIN disciplinas d ON ppd.disciplina_codigo = d.codigo_disciplina
        WHERE u.tipo_usuario = 'professor'
        GROUP BY u.id, u.nome, u.email, u.curso
        ORDER BY u.nome ASC";

$result = $oMysql->query($sql);
?>

<div class="container mt-5">
    <h2 class="mb-4">Lista de Professores</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Curso</th>
                    <th>Disciplinas</th>
                </tr>
            </thead>
            <tbody>
                <?php while($professor = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($professor['nome']) ?></td>
                        <td><?= htmlspecialchars($professor['email']) ?></td>
                        <td><?= htmlspecialchars($professor['curso']) ?></td>
                        <td><?= htmlspecialchars($professor['disciplinas']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Nenhum professor encontrado.
        </div>
    <?php endif; ?>
</div>

<?php
$oMysql->close();
?>
