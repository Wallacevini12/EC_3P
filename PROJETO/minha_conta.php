<?php
session_start();
require_once 'conecta_db.php';
include "header.php";

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Define a página de voltar com base no tipo de usuário
$pagina_voltar = 'index.php'; // valor padrão caso não esteja logado

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
$conn = conecta_db();

// Verifica conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Verifica se foi solicitado excluir o usuário
if (isset($_GET['excluir']) && $_GET['excluir'] == 'sim') {
    $conn->begin_transaction();

    try {
        // Exclui registros nas tabelas relacionadas
        $sql_delete_aluno = "DELETE FROM aluno WHERE id = ?";
        $stmt_delete_aluno = $conn->prepare($sql_delete_aluno);
        if ($stmt_delete_aluno) {
            $stmt_delete_aluno->bind_param("i", $usuario_id);
            $stmt_delete_aluno->execute();
            $stmt_delete_aluno->close();
        }

        $sql_delete_professor = "DELETE FROM professor WHERE id = ?";
        $stmt_delete_professor = $conn->prepare($sql_delete_professor);
        if ($stmt_delete_professor) {
            $stmt_delete_professor->bind_param("i", $usuario_id);
            $stmt_delete_professor->execute();
            $stmt_delete_professor->close();
        }

        // Exclui o usuário
        $sql_delete_usuario = "DELETE FROM usuarios WHERE id = ?";
        $stmt_delete_usuario = $conn->prepare($sql_delete_usuario);
        if ($stmt_delete_usuario) {
            $stmt_delete_usuario->bind_param("i", $usuario_id);
            $stmt_delete_usuario->execute();
            $stmt_delete_usuario->close();
        }

        $conn->commit();

        session_destroy();
        header("Location: cadastro.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao excluir o usuário: " . $e->getMessage();
    } finally {
        $conn->close();
    }
} else {
    // Busca dados do usuário
    $sql = "SELECT nome, email, curso, tipo_usuario FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro na preparação da consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Usuário não encontrado.";
        exit();
    }

    $dados_usuario = $result->fetch_assoc();

    $disciplinas = [];
    $tabela_vinculo = '';
    $coluna_usuario = '';

    switch ($dados_usuario['tipo_usuario']) {
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

    if ($tabela_vinculo && $coluna_usuario) {
        $sql_disciplinas = "
            SELECT d.nome_disciplina 
            FROM disciplinas d
            INNER JOIN $tabela_vinculo vd ON d.codigo_disciplina = vd.disciplina_codigo
            WHERE vd.$coluna_usuario = ?";

        $stmt_disc = $conn->prepare($sql_disciplinas);
        if ($stmt_disc) {
            $stmt_disc->bind_param("i", $usuario_id);
            $stmt_disc->execute();
            $result_disc = $stmt_disc->get_result();

            while ($row = $result_disc->fetch_assoc()) {
                $disciplinas[] = $row['nome_disciplina'];
            }

            $stmt_disc->close();
        }
    }


    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minha Conta</title>
    <script>
        function confirmarExclusao() {
            var resposta = confirm("Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.");
            if (resposta) {
                window.location.href = "minha_conta.php?excluir=sim";
            }
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

        <?php if (!empty($disciplinas)): ?>
            <p><strong>Disciplinas Vinculadas:</strong></p>
            <ul>
                <?php foreach ($disciplinas as $disciplina): ?>
                    <li><?= htmlspecialchars($disciplina) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><strong>Disciplinas Vinculadas:</strong> Nenhuma disciplina vinculada.</p>
        <?php endif; ?>

        
        <a href="logout.php" class="btn btn-danger">Sair</a>
        <button class="btn btn-danger" onclick="confirmarExclusao()">Excluir Conta</button>
        <a href="<?= $pagina_voltar ?>" class="btn btn-danger">Voltar</a>
    <?php else: ?>
        <p>Usuário excluído com sucesso. Você será redirecionado para a página de cadastro.</p>
    <?php endif; ?>
</div>

</body>
</html>
