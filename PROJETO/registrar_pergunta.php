<?php
session_start();

// Incluir o arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; // Isso garante que a função conecta_db() estará disponível

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pergunta']) && isset($_POST['materia'])) {
    $pergunta = $_POST['pergunta'];
    $materia = $_POST['materia'];
    $aluno_id = $_SESSION['codigo_aluno'];

    // Verifique se o aluno existe na tabela 'aluno'
    $oMysql = conecta_db();
    if ($oMysql->connect_error) {
        echo "Erro de conexão: " . $oMysql->connect_error;
        exit;
    }

    $stmt = $oMysql->prepare("SELECT codigo_aluno FROM aluno WHERE codigo_aluno = ?");
    $stmt->bind_param("i", $aluno_id); // Verifica se o aluno existe no banco de dados
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "Erro: Aluno não encontrado!";
        exit;
    }

    $stmt->close();

    // Buscar o código da disciplina pelo nome
    $stmt = $oMysql->prepare("SELECT codigo_disciplina FROM disciplina WHERE nome_disciplina = ?");
    $stmt->bind_param("s", $materia);
    $stmt->execute();
    $stmt->bind_result($disciplina_codigo);
    $stmt->fetch();
    $stmt->close();

    // Verifique se o código da disciplina foi encontrado
    if (empty($disciplina_codigo)) {
        echo "Erro: Disciplina não encontrada!";
        exit;
    }

    // Inserção da pergunta no banco
    $stmt = $oMysql->prepare("INSERT INTO pergunta (aluno_codigo, enunciado, disciplina_codigo) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $aluno_id, $pergunta, $disciplina_codigo);

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
                <option value="Engenharia de Software">Engenharia de Software</option>
                <option value="Sistemas de Informação">Sistemas de Informação</option>
                <option value="Análise e Desenvolvimento de Sistemas">Análise e Desenvolvimento de Sistemas</option>
                <option value="Ciência da Computação">Ciência da Computação</option>
                <option value="Redes de Computadores">Redes de Computadores</option>
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
