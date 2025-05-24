<?php
session_start();

require_once 'conecta_db.php';
include "header.php";

if (!isset($_SESSION['id'])) {
    echo '<div class="container" style="margin-top: 70px;">
            <div class="alert alert-danger" role="alert">
                Você não está logado.
            </div>
          </div>';
    exit();
}

$pagina_voltar = 'index.php';
if ($_SESSION['tipo_usuario'] === 'aluno') $pagina_voltar = 'home_aluno.php';
elseif ($_SESSION['tipo_usuario'] === 'professor') $pagina_voltar = 'home_professor.php';
elseif ($_SESSION['tipo_usuario'] === 'monitor') $pagina_voltar = 'home_monitor.php';

$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$conn = conecta_db();
if ($conn->connect_error) die("Erro na conexão: " . $conn->connect_error);

// Se for monitor, busca o ranking
$meu_ranking = null;
if ($tipo_usuario === 'monitor') {
    $sql_ranking = "
        SELECT AVG(ar.nota) AS media_avaliacao, COUNT(ar.nota) AS total_avaliacoes
        FROM respostas r
        JOIN avaliacoes ar ON r.codigo_resposta = ar.resposta_id
        WHERE r.respondente_id = ?
    ";
    $stmt_ranking = $conn->prepare($sql_ranking);
    $stmt_ranking->bind_param("i", $usuario_id);
    $stmt_ranking->execute();
    $result_ranking = $stmt_ranking->get_result();
    $meu_ranking = $result_ranking->fetch_assoc();
}

// Tabela de vínculo por tipo
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

// Atualiza disciplinas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql_delete = "DELETE FROM $tabela_vinculo WHERE $coluna_usuario = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();

    if ($tipo_usuario === 'monitor' && isset($_POST['disciplina'])) {
        $codigo = (int) $_POST['disciplina'];
        $stmt_insert = $conn->prepare("INSERT INTO $tabela_vinculo ($coluna_usuario, disciplina_codigo) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $usuario_id, $codigo);
        $stmt_insert->execute();
    } elseif (in_array($tipo_usuario, ['aluno', 'professor']) && isset($_POST['disciplinas'])) {
        $stmt_insert = $conn->prepare("INSERT INTO $tabela_vinculo ($coluna_usuario, disciplina_codigo) VALUES (?, ?)");
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
        $stmt = $conn->prepare("DELETE FROM aluno WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM professor WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
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

// Dados do usuário
$stmt = $conn->prepare("SELECT nome, email, curso, tipo_usuario FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) exit("Usuário não encontrado.");
$dados_usuario = $result->fetch_assoc();

// Disciplinas atuais
$disciplinas = [];
$disciplinas_usuario_codigos = [];
$stmt_disc = $conn->prepare("
    SELECT d.nome_disciplina, d.codigo_disciplina
    FROM disciplinas d
    INNER JOIN $tabela_vinculo vd ON d.codigo_disciplina = vd.disciplina_codigo
    WHERE vd.$coluna_usuario = ?
");
$stmt_disc->bind_param("i", $usuario_id);
$stmt_disc->execute();
$res_disc = $stmt_disc->get_result();
while ($row = $res_disc->fetch_assoc()) {
    $disciplinas[] = $row['nome_disciplina'];
    $disciplinas_usuario_codigos[] = $row['codigo_disciplina'];
}

// Todas as disciplinas
$todas_disciplinas = [];
$res = $conn->query("SELECT codigo_disciplina, nome_disciplina FROM disciplinas");
while ($row = $res->fetch_assoc()) {
    $todas_disciplinas[] = $row;
}
?>

<div class="container" style="margin-top: 70px;">
    <h2>Minha Conta</h2>

    <?php if (isset($dados_usuario)): ?>
        <p><strong>Nome:</strong> <?= htmlspecialchars($dados_usuario['nome']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($dados_usuario['email']) ?></p>
        <p><strong>Curso:</strong> <?= htmlspecialchars($dados_usuario['curso']) ?></p>
        <p><strong>Tipo de Usuário:</strong> <?= ucfirst(htmlspecialchars($dados_usuario['tipo_usuario'])) ?></p>

        <?php if ($tipo_usuario === 'monitor'): ?>
            <h4 class="mt-4">Meu Ranking</h4>
            <?php if ($meu_ranking && $meu_ranking['total_avaliacoes'] > 0): ?>
                <p><strong>Total de Avaliações Recebidas:</strong> <?= $meu_ranking['total_avaliacoes'] ?></p>
                <p><strong>Média das Avaliações:</strong> <?= number_format($meu_ranking['media_avaliacao'], 2) ?> ★</p>
            <?php else: ?>
                <p>Você ainda não recebeu avaliações.</p>
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
