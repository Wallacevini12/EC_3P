<?php

session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header("Location: login.php");
    exit;
}


include "header.php";
// Incluir o arquivo de conexão com o banco de dados
include_once 'conecta_db.php';

// Conecta e seleciona o banco de dados learnhub_ep
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    echo "Erro de conexão: " . $oMysql->connect_error;
    exit;
}
$oMysql->select_db("learnhub_ep");

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}
// Verificar se o usuário logado é do tipo 'aluno'
$usuario_id = $_SESSION['id'];
$stmt = $oMysql->prepare("SELECT tipo_usuario FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($tipo_usuario);
$stmt->fetch();
$stmt->close();

if ($tipo_usuario !== 'aluno') {
    echo "Acesso restrito! Apenas usuários do tipo aluno podem registrar perguntas.";
    exit;
}

// Buscar as disciplinas cadastradas para preencher o select
$sql = "SELECT nome_disciplina FROM disciplinas ORDER BY nome_disciplina ASC";
$result = $oMysql->query($sql);
if (!$result) {
    echo "Erro ao buscar disciplinas: " . $oMysql->error;
    exit;
}
$disciplinas = $result->fetch_all(MYSQLI_ASSOC);
$result->free();

// Processar o formulário de registro de pergunta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pergunta']) && isset($_POST['materia'])) {
    $pergunta = $_POST['pergunta'];
    $materia  = $_POST['materia'];
    $aluno_id = $_SESSION['codigo_aluno'];

    // Verificar se o aluno existe na tabela 'aluno'
    $stmt = $oMysql->prepare("SELECT id FROM aluno WHERE id = ?");
    $stmt->bind_param("i", $aluno_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "Erro: Aluno não encontrado!";
        exit;
    }
    $stmt->close();

    // Buscar o código da disciplina pelo nome na tabela 'disciplinas'
    $stmt = $oMysql->prepare("SELECT codigo_disciplina FROM disciplinas WHERE nome_disciplina = ?");
    $stmt->bind_param("s", $materia);
    $stmt->execute();
    $stmt->bind_result($disciplina_codigo);
    $stmt->fetch();
    $stmt->close();

    if (empty($disciplina_codigo)) {
        echo "Erro: Disciplina não encontrada!";
        exit;
    }

    // Inserir a pergunta na tabela 'perguntas'
    $stmt = $oMysql->prepare("INSERT INTO perguntas (enunciado, usuario_codigo, disciplina_codigo) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $pergunta, $aluno_id, $disciplina_codigo);

    if ($stmt->execute()) {
        echo "Pergunta registrada com sucesso!";
    } else {
        echo "Erro ao registrar pergunta: " . $stmt->error;
    }

    $stmt->close();
    $oMysql->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Registrar Pergunta</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="d-flex justify-content-center align-items-center vh-100" style="margin-top: 80px">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow p-5 d-flex flex-column gap-4 w-100 mb-3">
            <h2 class="text-center mb-4">Registrar Pergunta</h2>
            <form method="POST" action="registrar_pergunta.php" class="d-flex flex-column gap-4">
                <div>
                    <label for="materia" class="form-label mb-3">Matéria</label>
                    <select class="form-select mb-3" id="materia" name="materia" required>
                        <option value="" disabled selected>Selecione uma matéria</option>
                        <?php
                        foreach ($disciplinas as $disciplina) {
                            echo '<option value="' . htmlspecialchars($disciplina['nome_disciplina']) . '">'
                                 . htmlspecialchars($disciplina['nome_disciplina']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="pergunta" class="form-label">Sua Pergunta</label>
                    <textarea class="form-control" id="pergunta" name="pergunta" rows="4" placeholder="Digite sua dúvida aqui..." required></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Registrar Pergunta</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>