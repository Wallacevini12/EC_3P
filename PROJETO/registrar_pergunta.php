<?php
session_start();

// Incluir o arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; // Esse arquivo deve conter a função conecta_db()

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
    <h2>Registrar Pergunta</h2>
    <form method="POST" action="registrar_pergunta.php">
        <div class="mb-3">
            <label for="materia" class="form-label">Matéria</label>
            <select class="form-select" id="materia" name="materia" required>
                <option value="" disabled selected>Selecione uma matéria</option>
                <?php
                foreach ($disciplinas as $disciplina) {

                    echo '<option value="' . htmlspecialchars($disciplina['nome_disciplina']) . '">'
                         . htmlspecialchars($disciplina['nome_disciplina']) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="pergunta" class="form-label">Sua Pergunta</label>
            <textarea class="form-control" id="pergunta" name="pergunta" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Registrar Pergunta</button>
    </form>
</div>

</body>
</html>