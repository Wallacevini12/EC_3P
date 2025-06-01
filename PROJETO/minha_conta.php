<?php
// Inicia a sessão para acessar variáveis de sessão
session_start();

// Inclui o arquivo que conecta ao banco de dados
require_once 'conecta_db.php';

// Inclui o cabeçalho da página (ex: navbar, CSS, etc)
include "header.php";

// Verifica se o usuário está logado, se não estiver exibe mensagem e encerra o script
if (!isset($_SESSION['id'])) {
    echo '<div class="container" style="margin-top: 70px;">
            <div class="alert alert-danger" role="alert">
                Você não está logado.
            </div>
          </div>';
    exit();
}

// Define a página para voltar baseado no tipo de usuário
$pagina_voltar = 'index.php';
if ($_SESSION['tipo_usuario'] === 'aluno') $pagina_voltar = 'home_aluno.php';
elseif ($_SESSION['tipo_usuario'] === 'professor') $pagina_voltar = 'home_professor.php';
elseif ($_SESSION['tipo_usuario'] === 'monitor') $pagina_voltar = 'home_monitor.php';

// Pega o ID e o tipo do usuário da sessão para usar nas consultas
$usuario_id = $_SESSION['id'];
$tipo_usuario = $_SESSION['tipo_usuario'];

// Estabelece conexão com o banco de dados
$conn = conecta_db();
if ($conn->connect_error) die("Erro na conexão: " . $conn->connect_error);

// Se for monitor, busca a média e total de avaliações que ele recebeu (ranking)
$meu_ranking = null;
if ($tipo_usuario === 'monitor') {
    // Query para pegar a média e quantidade de avaliações das respostas do monitor
    $sql_ranking = "
        SELECT AVG(ar.nota) AS media_avaliacao, COUNT(ar.nota) AS total_avaliacoes
        FROM respostas r
        JOIN avaliacoes ar ON r.codigo_resposta = ar.resposta_id
        WHERE r.respondente_id = ?
    ";
    // Prepara a consulta para evitar SQL Injection
    $stmt_ranking = $conn->prepare($sql_ranking);
    $stmt_ranking->bind_param("i", $usuario_id);
    $stmt_ranking->execute();
    $result_ranking = $stmt_ranking->get_result();
    // Busca os dados do ranking
    $meu_ranking = $result_ranking->fetch_assoc();
}

// Define tabela e coluna de vínculo de disciplinas dependendo do tipo de usuário
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

// Se o formulário de atualização de disciplinas for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Apaga todas as disciplinas vinculadas atualmente ao usuário
    $sql_delete = "DELETE FROM $tabela_vinculo WHERE $coluna_usuario = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();

    // Para monitor, apenas uma disciplina pode ser selecionada (radio button)
    if ($tipo_usuario === 'monitor' && isset($_POST['disciplina'])) {
        $codigo = (int) $_POST['disciplina'];
        // Insere a nova disciplina selecionada para o monitor
        $stmt_insert = $conn->prepare("INSERT INTO $tabela_vinculo ($coluna_usuario, disciplina_codigo) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $usuario_id, $codigo);
        $stmt_insert->execute();

    // Para aluno e professor pode ter várias disciplinas (checkboxes)
    } elseif (in_array($tipo_usuario, ['aluno', 'professor']) && isset($_POST['disciplinas'])) {
        $stmt_insert = $conn->prepare("INSERT INTO $tabela_vinculo ($coluna_usuario, disciplina_codigo) VALUES (?, ?)");
        // Para cada disciplina selecionada, insere na tabela de vínculo
        foreach ($_POST['disciplinas'] as $codigo) {
            $codigo = (int) $codigo;
            $stmt_insert->bind_param("ii", $usuario_id, $codigo);
            $stmt_insert->execute();
        }
    }
}

// Verifica se a exclusão da conta foi solicitada pela URL (?excluir=sim)
if (isset($_GET['excluir']) && $_GET['excluir'] == 'sim') {
    // Inicia uma transação para garantir exclusão completa ou nenhuma exclusão
    $conn->begin_transaction();
    try {
        // Tenta excluir o usuário das tabelas específicas primeiro
        $stmt = $conn->prepare("DELETE FROM aluno WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM professor WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        // Depois exclui da tabela geral de usuários
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();

        // Confirma a transação
        $conn->commit();

        // Destrói a sessão e redireciona para cadastro
        session_destroy();
        header("Location: cadastro.php");
        exit();

    } catch (Exception $e) {
        // Se der erro, desfaz as exclusões
        $conn->rollback();
        echo "Erro ao excluir o usuário: " . $e->getMessage();
    }
}

// Busca os dados básicos do usuário para exibir
$stmt = $conn->prepare("SELECT nome, email, curso, tipo_usuario FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Se usuário não for encontrado, encerra o script
if ($result->num_rows === 0) exit("Usuário não encontrado.");
$dados_usuario = $result->fetch_assoc();

// Busca as disciplinas vinculadas ao usuário
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
// Salva os nomes e códigos das disciplinas em arrays para exibir e marcar checkbox/radio
while ($row = $res_disc->fetch_assoc()) {
    $disciplinas[] = $row['nome_disciplina'];
    $disciplinas_usuario_codigos[] = $row['codigo_disciplina'];
}

// Busca todas as disciplinas disponíveis no sistema para mostrar no formulário
$todas_disciplinas = [];
$res = $conn->query("SELECT codigo_disciplina, nome_disciplina FROM disciplinas");
while ($row = $res->fetch_assoc()) {
    $todas_disciplinas[] = $row;
}
?>

<!-- Início do HTML da página -->
<div class="container" style="margin-top: 70px;">
    <h2>Minha Conta</h2>

    <!-- Verifica se os dados do usuário foram carregados -->
    <?php if (isset($dados_usuario)): ?>
        <!-- Exibe as informações do usuário -->
        <p><strong>Nome:</strong> <?= htmlspecialchars($dados_usuario['nome']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($dados_usuario['email']) ?></p>
        <p><strong>Curso:</strong> <?= htmlspecialchars($dados_usuario['curso']) ?></p>
        <p><strong>Tipo de Usuário:</strong> <?= ucfirst(htmlspecialchars($dados_usuario['tipo_usuario'])) ?></p>

        <!-- Se for monitor, exibe o ranking de avaliações -->
        <?php if ($tipo_usuario === 'monitor'): ?>
            <h4 class="mt-4">Meu Ranking</h4>
            <?php if ($meu_ranking && $meu_ranking['total_avaliacoes'] > 0): ?>
                <p><strong>Total de Avaliações Recebidas:</strong> <?= $meu_ranking['total_avaliacoes'] ?></p>
                <p><strong>Média das Avaliações:</strong> <?= number_format($meu_ranking['media_avaliacao'], 2) ?> ★</p>
            <?php else: ?>
                <p>Você ainda não recebeu avaliações.</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Lista as disciplinas vinculadas -->
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

        <!-- Botão para mostrar/esconder o formulário de edição -->
        <button class="btn btn-warning" onclick="toggleFormulario()">Editar Disciplinas</button>

        <!-- Formulário para editar disciplinas -->
        <div id="formEditarDisciplinas" style="display:none; margin-top:15px;">
            <form method="POST">
                <?php foreach ($todas_disciplinas as $disc): ?>
                    <div class="form-check">
                        <!-- Se for monitor, usa radio para escolher uma disciplina -->
                        <?php if ($tipo_usuario === 'monitor'): ?>
                            <input class="form-check-input" type="radio" name="disciplina"
                                value="<?= $disc['codigo_disciplina'] ?>"
                                <?= in_array($disc['codigo_disciplina'], $disciplinas_usuario_codigos) ? 'checked' : '' ?>>
                        <!-- Senão, usa checkbox para múltiplas disciplinas -->
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
                <!-- Botões para salvar ou cancelar edição -->
                <button type="submit" class="btn btn-success">Salvar</button>
                <button type="button" class="btn btn-secondary" onclick="toggleFormulario()">Cancelar</button>
            </form>
        </div>

        <br><br>
        <!-- Botões para logout, excluir conta e voltar para a página inicial -->
        <a href="logout.php" class="btn btn-danger">Sair</a>
        <button class="btn btn-danger" onclick="confirmarExclusao()">Excluir Conta</button>
        <a href="<?= $pagina_voltar ?>" class="btn btn-danger">Voltar</a>

    <?php else: ?>
        <!-- Mensagem caso o usuário tenha sido excluído -->
        <p>Usuário excluído com sucesso. Você será redirecionado para a página de cadastro.</p>
    <?php endif; ?>
</div>

<!-- Scripts JavaScript -->
<script>
    // Confirma exclusão da conta com um alerta e redireciona para exclusão
    function confirmarExclusao() {
        if (confirm("Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.")) {
            window.location.href = "minha_conta.php?excluir=sim";
        }
    }

    // Mostra ou esconde o formulário de editar disciplinas
    function toggleFormulario() {
        const form = document.getElementById('formEditarDisciplinas');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>
