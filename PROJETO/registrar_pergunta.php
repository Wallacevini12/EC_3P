<?php
session_start();

include "header.php";
include_once 'conecta_db.php';

// Verifica se o usuário está logado como professor
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    echo '<div class="container" style="margin-top: 70px;">
            <div class="alert alert-danger" role="alert">
                Você não está logado como aluno.
            </div>
          </div>';
    exit();
}



$conn = conecta_db();
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$usuario_id = $_SESSION['id'];
$codigo_aluno = $_SESSION['codigo_aluno'] ?? $usuario_id;

// Buscar disciplinas do aluno
$sql = "
    SELECT d.nome_disciplina 
    FROM disciplinas d
    INNER JOIN alunos_possuem_disciplinas apd ON d.codigo_disciplina = apd.disciplina_codigo
    WHERE apd.aluno_codigo = ?
    ORDER BY d.nome_disciplina ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$disciplinas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Inserção da pergunta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pergunta'], $_POST['materia'])) {
    $pergunta = $_POST['pergunta'];
    $materia = $_POST['materia'];

    // Buscar código da disciplina
    $stmt = $conn->prepare("SELECT codigo_disciplina FROM disciplinas WHERE nome_disciplina = ?");
    $stmt->bind_param("s", $materia);
    $stmt->execute();
    $stmt->bind_result($disciplina_codigo);
    $stmt->fetch();
    $stmt->close();

    if (empty($disciplina_codigo)) {
        echo "Erro: Disciplina não encontrada.";
        exit;
    }

    // Inserir pergunta
    $stmt = $conn->prepare("INSERT INTO perguntas (enunciado, usuario_codigo, disciplina_codigo) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $pergunta, $codigo_aluno, $disciplina_codigo);
    
    if ($stmt->execute()) {
        $pergunta_id = $stmt->insert_id;

        // Vincular pergunta ao aluno
        $stmt_v1 = $conn->prepare("INSERT INTO aluno_possui_pergunta (aluno_codigo, pergunta_codigo) VALUES (?, ?)");
        $stmt_v1->bind_param("ii", $codigo_aluno, $pergunta_id);
        $stmt_v1->execute();
        $stmt_v1->close();

        // Vincular pergunta à disciplina
        $stmt_v2 = $conn->prepare("INSERT INTO pergunta_possui_disciplina (pergunta_codigo, disciplina_codigo) VALUES (?, ?)");
        $stmt_v2->bind_param("ii", $pergunta_id, $disciplina_codigo);
        $stmt_v2->execute();
        $stmt_v2->close();

        echo "<script>alert('Pergunta registrada com sucesso!'); window.location.href='registrar_pergunta.php';</script>";
    } else {
        echo "Erro ao registrar pergunta: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow p-5 d-flex flex-column gap-4 w-100 mb-3">
            <h2 class="text-center mb-4">Registrar Pergunta</h2>
            <form method="POST" action="registrar_pergunta.php" class="d-flex flex-column gap-4">
                <div>
                    
                    <label for="materia" class="form-label mb-3">Matéria</label><label style="color: red;">*</label>
                    <select class="form-select mb-3" id="materia" name="materia" required>
                        <option value="" disabled selected>Selecione uma matéria</option>
                        <?php foreach ($disciplinas as $disc): ?>
                            <option value="<?= htmlspecialchars($disc['nome_disciplina']) ?>">
                                <?= htmlspecialchars($disc['nome_disciplina']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="pergunta" class="form-label">Sua Pergunta</label><label style="color: red;">*</label>
                    <textarea class="form-control" id="pergunta" name="pergunta" rows="4" placeholder="Digite sua dúvida aqui..." required></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">Registrar Pergunta</button>
                </div>
                <div class="d-grid mt-3">
                    <a href="home_aluno.php" class="btn btn-secondary btn-lg">Voltar para Home</a>
                </div>
            </form>
        </div>
    </div>
</div>
