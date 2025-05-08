<?php
include_once 'conecta_db.php'; // Inclui o arquivo de conexão com o banco

$oMysql = conecta_db();

if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

// Buscar disciplinas
$sql = "SELECT codigo_disciplina, nome_disciplina FROM disciplinas";
$result_disciplina = $oMysql->query($sql);

$disciplinas = [];
if ($result_disciplina && $result_disciplina->num_rows > 0) {
    while ($row = $result_disciplina->fetch_assoc()) {
        $disciplinas[] = $row;
    }
}

// Verifica se o formulário foi enviado
if (
    isset($_POST['nome']) &&
    isset($_POST['email']) &&
    isset($_POST['senha']) &&
    isset($_POST['curso']) &&
    isset($_POST['disciplina'])
) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $curso = $_POST['curso'];
    $disciplina_codigo = $_POST['disciplina'];



    // Segundo, verificar se o e-mail já existe no banco
    $stmt_verifica = $oMysql->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_verifica->bind_param("s", $email);
    $stmt_verifica->execute();
    $stmt_verifica->store_result();

    if ($stmt_verifica->num_rows > 0) {
        echo "<script>alert('Este e-mail já está cadastrado. Tente novamente com outro e-mail.'); window.history.back();</script>";
        exit;
    }
    $stmt_verifica->close();

    // Gera o hash da senha para armazenamento seguro
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Define tipo de usuário como monitor
    $tipo_usuario = 'monitor';

    // Prepara a inserção na tabela 'usuarios'
    $stmt = $oMysql->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, curso) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo_usuario, $curso);

    if ($stmt->execute()) {
        $usuario_id = $oMysql->insert_id;

        // Insere na tabela 'monitor'
        $stmt2 = $oMysql->prepare("INSERT INTO monitor(id) VALUES (?)");
        $stmt2->bind_param("i", $usuario_id);

        if ($stmt2->execute()) {
            // Relaciona o monitor à disciplina escolhida
            $stmt3 = $oMysql->prepare("INSERT INTO disciplinas_possuem_monitores (disciplina_codigo, monitor_codigo) VALUES (?, ?)");
            $stmt3->bind_param("ii", $disciplina_codigo, $usuario_id);

            if ($stmt3->execute()) {
                echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='home_professor.php';</script>";
                exit;
            } else {
                echo "Erro ao vincular monitor à disciplina: " . $stmt3->error;
            }
            $stmt3->close();
        } else {
            echo "Erro ao cadastrar monitor: " . $stmt2->error;
        }
        $stmt2->close();
    } else {
        echo "Erro ao cadastrar usuário: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Cadastro de Monitor</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Botão voltar fora do card -->
<div class="container mt-3">
  <a href="home_professor.php" class="btn btn-secondary btn-sm">&larr; Voltar</a>
</div>

<!-- Card centralizado com o formulário -->
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="min-width: 400px; max-width: 500px; width: 100%;">

    <h2 class="mb-3">Cadastrar Monitor</h2>
    <p class="mb-4">Preencha os campos abaixo para cadastrar um monitor:</p> 

    <form method="POST" action="index.php?page=3">
      <input
        type="text"
        name="nome"
        class="form-control mb-2"
        placeholder="Nome"
        required>

      <input
        type="email"
        name="email"
        class="form-control mb-2"
        placeholder="Email (ex: maria@monitor)"
        required>

      <input
        type="password"
        name="senha"
        class="form-control mb-2"
        placeholder="Senha"
        required>

      <select name="curso" class="form-select mb-2" required>
        <option value="" disabled selected>Selecione seu curso</option>
        <option value="Engenharia de Software">Engenharia de Software</option>
        <option value="Sistemas de Informação">Sistemas de Informação</option>
        <option value="Análise e Desenvolvimento de Sistemas">Análise e Desenvolvimento de Sistemas</option>
        <option value="Ciência da Computação">Ciência da Computação</option>
        <option value="Redes de Computadores">Redes de Computadores</option>
      </select>

      <select name="disciplina" class="form-select mb-3" required>
        <option value="" disabled selected>Selecione sua disciplina</option>
        <?php foreach ($disciplinas as $disciplina): ?>
          <option value="<?php echo htmlspecialchars($disciplina['codigo_disciplina']); ?>">
            <?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
    </form>

  </div>
</div>

</body>
</html>
