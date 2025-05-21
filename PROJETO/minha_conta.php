<?php
session_start();
require_once 'conecta_db.php';
include "header.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$pagina_voltar = 'index.php';

if (isset($_SESSION['tipo_usuario'])) {
    if ($_SESSION['tipo_usuario'] === 'aluno') {
        $pagina_voltar = 'home_aluno.php';
    } elseif ($_SESSION['tipo_usuario'] === 'professor') {
        $pagina_voltar = 'home_professor.php';
    } elseif ($_SESSION['tipo_usuario'] === 'monitor') {
        $pagina_voltar = 'home_monitor.php';
    }
}

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

$conn = conecta_db();

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Se for monitor, calcula o ranking

if ($tipo_usuario === 'monitor') {
    $sql_ranking = "
        SELECT 
            u.id,
            u.nome,
            AVG(ar.nota) AS media_avaliacao,
            COUNT(ar.nota) AS total_avaliacoes
        FROM usuarios u
        JOIN respostas r ON u.id = r.monitor_id
        JOIN avaliacoes ar ON r.codigo_resposta = ar.resposta_id
        WHERE u.tipo_usuario = 'monitor'
        GROUP BY u.id, u.nome
        ORDER BY media_avaliacao DESC
        LIMIT 10
    ";

    $result_ranking = $conn->query($sql_ranking);
}



// Identifica tabela e coluna conforme tipo de usuário
$tabela_vinculo = '';
$coluna_usuario = '';

switch ($tipo_usuario) {
    case 'professor':
        $tabela_vinculo = 'professores_possuem_disciplinas';
        $coluna_usuario = 'professor_codigo';
        break;
    case 'aluno':
        $tabela_vinculo = 'alunos_possuem_disciplinas';
        $coluna_usuario = 'aluno_codigo';
        break;
    case 'monitor':
        $tabela_vinculo = 'monitores_possuem_disciplinas';
        $coluna_usuario = 'monitor_codigo';
        break;
}

// Atualiza disciplinas se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove vínculos antigos
    $sql_delete = "DELETE FROM $tabela_vinculo WHERE $coluna_usuario = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();

    // Monitor: aceita apenas uma disciplina
    if ($tipo_usuario === 'monitor' && isset($_POST['disciplina'])) {
        $codigo = (int) $_POST['disciplina'];
        $sql_insert = "INSERT INTO $tabela_vinculo ($coluna_usuario, disciplina_codigo) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $usuario_id, $codigo);
        $stmt_insert->execute();

    // Aluno e professor: múltiplas disciplinas
    } elseif (in_array($tipo_usuario, ['aluno', 'professor']) && isset($_POST['disciplinas'])) {
        $sql_insert = "INSERT INTO $tabela_vinculo ($coluna_usuario, disciplina_codigo) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        foreach ($_POST['disciplinas'] as $codigo) {
            $codigo = (int) $codigo;
            $stmt_insert->bind_param("ii", $usuario_id, $codigo);
            $stmt_insert->execute();
        }
    }
}

// Exclusão de conta
if (isset($_GET['excluir']) && $_GET['excluir'] == 'sim') {
    $conn->begin_transaction();

    try {
        $sql_delete_aluno = "DELETE FROM aluno WHERE id = ?";
        $stmt = $conn->prepare($sql_delete_aluno);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $sql_delete_prof = "DELETE FROM professor WHERE id = ?";
        $stmt = $conn->prepare($sql_delete_prof);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $sql_delete_user = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql_delete_user);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $conn->commit();
        session_destroy();
        header("Location: cadastro.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao excluir o usuário: " . $e->getMessage();
    }
}

// Busca dados do usuário
$sql = "SELECT nome, email, curso, tipo_usuario FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Usuário não encontrado.";
    exit();
}

$dados_usuario = $result->fetch_assoc();

// Pega disciplinas atuais
$disciplinas = [];
$sql_disciplinas = "
    SELECT d.nome_disciplina, d.codigo_disciplina
    FROM disciplinas d
    INNER JOIN $tabela_vinculo vd ON d.codigo_disciplina = vd.disciplina_codigo
    WHERE vd.$coluna_usuario = ?";
$stmt_disc = $conn->prepare($sql_disciplinas);
$stmt_disc->bind_param("i", $usuario_id);
$stmt_disc->execute();
$res_disc = $stmt_disc->get_result();
$disciplinas_usuario_codigos = [];

while ($row = $res_disc->fetch_assoc()) {
    $disciplinas[] = $row['nome_disciplina'];
    $disciplinas_usuario_codigos[] = $row['codigo_disciplina'];
}

// Todas as disciplinas disponíveis
$todas_disciplinas = [];
$res = $conn->query("SELECT codigo_disciplina, nome_disciplina FROM disciplinas");
while ($row = $res->fetch_assoc()) {
    $todas_disciplinas[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Conta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function confirmarExclusao() {
            if (confirm("Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.")) {
                window.location.href = "minha_conta.php?excluir=sim";
            }
        }

        function toggleFormulario() {
            const form = document.getElementById('formEditarDisciplinas');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="container" style="margin-top: 70px;">
    <h2>Minha Conta</h2>

    <?php if (isset($dados_usuario)): ?>
        <p><strong>Nome:</strong> <?= htmlspecialchars($dados_usuario['nome']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($dados_usuario['email']) ?></p>
        <p><strong>Curso:</strong> <?= htmlspecialchars($dados_usuario['curso']) ?></p>
        <p><strong>Tipo de Usuário:</strong> <?= ucfirst(htmlspecialchars($dados_usuario['tipo_usuario'])) ?></p>
        <?php if ($tipo_usuario === 'monitor'): ?>
        <h3>Ranking dos Monitores</h3>
        <?php if ($result_ranking && $result_ranking->num_rows > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Posição</th>
                        <th>Nome do Monitor</th>
                        <th>Média da Avaliação</th>
                        <th>Total de Avaliações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $posicao = 1;
                    while ($row = $result_ranking->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $posicao ?></td>
                        <td><?= htmlspecialchars($row['nome']) ?></td>
                        <td><?= number_format($row['media_avaliacao'], 2) ?></td>
                        <td><?= $row['total_avaliacoes'] ?></td>
                    </tr>
                    <?php 
                    $posicao++;
                    endwhile; 
                    ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum monitor avaliado ainda.</p>
        <?php endif; ?>
    <?php endif; ?>

        <p><strong>Disciplinas Vinculadas:</strong></p>
        <?php if (!empty($disciplinas)): ?>
            <ul>
                <?php foreach ($disciplinas as $disc): ?>
                    <li><?= htmlspecialchars($disc) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nenhuma disciplina vinculada.</p>
        <?php endif; ?>

        <button class="btn btn-warning" onclick="toggleFormulario()">Editar Disciplinas</button>

        <!-- Formulário de edição -->
        <div id="formEditarDisciplinas" style="display:none; margin-top:15px;">
            <form method="POST">
                <?php foreach ($todas_disciplinas as $disc): ?>
                    <div class="form-check">
                        <?php if ($tipo_usuario === 'monitor'): ?>
                            <input class="form-check-input" type="radio" name="disciplina"
                                value="<?= $disc['codigo_disciplina'] ?>"
                                <?= in_array($disc['codigo_disciplina'], $disciplinas_usuario_codigos) ? 'checked' : '' ?>>
                        <?php else: ?>
                            <input class="form-check-input" type="checkbox" name="disciplinas[]"
                                value="<?= $disc['codigo_disciplina'] ?>"
                                <?= in_array($disc['codigo_disciplina'], $disciplinas_usuario_codigos) ? 'checked' : '' ?>>
                        <?php endif; ?>
                        <label class="form-check-label">
                            <?= htmlspecialchars($disc['nome_disciplina']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
                <br>
                <button type="submit" class="btn btn-success">Salvar</button>
                <button type="button" class="btn btn-secondary" onclick="toggleFormulario()">Cancelar</button>
            </form>
        </div>

        <br><br>
        <a href="logout.php" class="btn btn-danger">Sair</a>
        <button class="btn btn-danger" onclick="confirmarExclusao()">Excluir Conta</button>
        <a href="<?= $pagina_voltar ?>" class="btn btn-danger">Voltar</a>
    <?php else: ?>
        <p>Usuário excluído com sucesso. Você será redirecionado para a página de cadastro.</p>
    <?php endif; ?>
</div>

</body>
</html>
