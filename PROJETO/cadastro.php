<?php
// Incluindo o arquivo de conexão com o banco de dados
require_once 'conecta_db.php'; 


// Obtém o tipo de usuário via URL do index.php
if (isset($_GET['tipo']) && in_array($_GET['tipo'], ['aluno', 'professor'])) {
  $tipo_usuario = $_GET['tipo'];
} else {
  echo "<script>alert('Tipo de usuário inválido ou não especificado.'); window.location.href = 'index.php';</script>";
  exit;
}

if (
    isset($_POST['nome']) &&
    isset($_POST['email']) &&
    isset($_POST['senha']) &&
    isset($_POST['curso'])
) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $curso = $_POST['curso'];


    // Gera o hash da senha para armazenamento seguro
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Conecta ao banco de dados
    $oMysql = conecta_db();  // Conecta ao banco de dados usando a função conecta_db()

    if ($oMysql->connect_error) {
        echo "Erro de conexão: " . $oMysql->connect_error;
        exit;
    }

    $stmt_check = $oMysql->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('E-mail já cadastrado.'); window.history.back();</script>";
        exit;
    }
    $stmt_check->close();



    // Prepara a inserção na tabela 'usuarios'
    $stmt = $oMysql->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, curso) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo_usuario, $curso);

    if ($stmt->execute()) {
        // Recupera o ID gerado na tabela 'usuarios'
        $usuario_id = $oMysql->insert_id;

        // Insere o ID na tabela específica, conforme o tipo de usuário
        if ($tipo_usuario === 'aluno') {
            $stmt2 = $oMysql->prepare("INSERT INTO aluno (id) VALUES (?)");
        } elseif ($tipo_usuario === 'professor') {
            $stmt2 = $oMysql->prepare("INSERT INTO professor (id) VALUES (?)");
        }

        if ($tipo_usuario === 'aluno' && isset($_POST['disciplinas'])) {
          $disciplinas = $_POST['disciplinas'];
          $stmt = $oMysql->prepare("INSERT INTO alunos_possuem_disciplinas (aluno_codigo, disciplina_codigo) VALUES (?, ?)");
      
          foreach ($disciplinas as $disciplina_id) {
              $disciplina_id = intval($disciplina_id); // Segurança básica
              $stmt->bind_param("ii", $usuario_id, $disciplina_id);
              $stmt->execute();
          }
      
          $stmt->close();
      }

      // Para professores, vamos permitir que escolham disciplinas também
      if ($tipo_usuario === 'professor' && isset($_POST['disciplinas'])) {
        $disciplinas = $_POST['disciplinas'];
        
        // Prepara o statement para inserir as disciplinas do professor
        $stmt = $oMysql->prepare("INSERT INTO professores_possuem_disciplinas (professor_codigo, disciplina_codigo) VALUES (?, ?)");
        
        foreach ($disciplinas as $disciplina_id) {
            $disciplina_id = intval($disciplina_id); // Garantir que o valor da disciplina seja um número inteiro
            $stmt->bind_param("ii", $usuario_id, $disciplina_id);
            $stmt->execute();
        }

        $stmt->close();
      }

        
        $stmt2->bind_param("i", $usuario_id);

        if ($stmt2->execute()) {
          // Preenche tabelas intermediárias
          if ($tipo_usuario === 'aluno') {
              $stmt_curso = $oMysql->prepare("SELECT codigo_curso FROM curso WHERE nome_curso = ?");
              $stmt_curso->bind_param("s", $curso);
              $stmt_curso->execute();
              $stmt_curso->bind_result($codigo_curso);
              if ($stmt_curso->fetch()) {
                  $stmt_curso->close();
      
                  $stmt_intermediaria = $oMysql->prepare("INSERT INTO alunos_possuem_cursos (aluno_codigo, curso_codigo) VALUES (?, ?)");
                  $stmt_intermediaria->bind_param("ii", $usuario_id, $codigo_curso);
                  $stmt_intermediaria->execute();
                  $stmt_intermediaria->close();
              } else {
                  echo "Curso não encontrado.";
                  exit;
              }
      
          } elseif ($tipo_usuario === 'professor') {
              $stmt_curso = $oMysql->prepare("SELECT codigo_curso FROM curso WHERE nome_curso = ?");
              $stmt_curso->bind_param("s", $curso);
              $stmt_curso->execute();
              $stmt_curso->bind_result($codigo_curso);
              if ($stmt_curso->fetch()) {
                  $stmt_curso->close();
      
                  $stmt_intermediaria = $oMysql->prepare("INSERT INTO cursos_possuem_professores (curso_codigo, professor_codigo) VALUES (?, ?)");
                  $stmt_intermediaria->bind_param("ii", $codigo_curso, $usuario_id);
                  $stmt_intermediaria->execute();
                  $stmt_intermediaria->close();
              } else {
                  echo "Curso não encontrado.";
                  exit;
              }
          }
      
          echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = 'index.php';</script>";
          exit;
      
      } else {
          echo "Erro ao cadastrar tipo específico: " . $stmt2->error;
      }
        $stmt2->close();
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }
    $stmt->close();
    $oMysql->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Cadastro de Usuários</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- Botão voltar fora do card -->
<div class="container mt-3">
  <a href="index.php" class="btn btn-secondary btn-sm mb-3">&larr; Voltar</a>
</div>

<!-- Card centralizado com o formulário -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow p-4" style="min-width: 350px; max-width: 500px; width: 100%;">
    <h2 class="mb-3">Cadastro de Usuário</h2>
    <p>Preencha os campos abaixo (e-mail institucional para definir o tipo de usuário):</p>    

    <form method="POST" action="cadastro.php?tipo=<?php echo htmlspecialchars($tipo_usuario); ?>">

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
        placeholder="Email (ex: maria@aluno.edu)"
        required>

      <input
        type="password"
        name="senha"
        class="form-control mb-2"
        placeholder="Senha"
        required>

      <select name="curso" class="form-select mb-3" required>
        <option value="" disabled selected>Selecione seu curso</option>
        <option value="Engenharia de Software">Engenharia de Software</option>
        <option value="Sistemas de Informação">Sistemas de Informação</option>
        <option value="Análise e Desenvolvimento de Sistemas">Análise e Desenvolvimento de Sistemas</option>
        <option value="Ciência da Computação">Ciência da Computação</option>
        <option value="Redes de Computadores">Redes de Computadores</option>
      </select>


      <!-- Carregar disciplinas do banco -->
      <?php
        require_once 'conecta_db.php';
        $oMysql = conecta_db();

        // Carregar disciplinas do banco
        $disciplinas = [];
        $result = $oMysql->query("SELECT codigo_disciplina, nome_disciplina FROM disciplinas");
        while ($row = $result->fetch_assoc()) {
            $disciplinas[] = $row;
        }
      ?>

      <!-- Inserir no formulário de cadastro -->
      <div class="mb-3">
        <label for="disciplinas" class="form-label">Disciplinas</label><br>
        <?php foreach ($disciplinas as $disciplina): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="disciplinas[]" value="<?= $disciplina['codigo_disciplina'] ?>" id="disciplina<?= $disciplina['codigo_disciplina'] ?>">
            <label class="form-check-label" for="disciplina<?= $disciplina['codigo_disciplina'] ?>">
              <?= htmlspecialchars($disciplina['nome_disciplina']) ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <input type="hidden" name="tipo_usuario_url" value="<?php echo htmlspecialchars($tipo_usuario); ?>">


      <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
    </form>
  </div>
</div>

</body>
</html>
