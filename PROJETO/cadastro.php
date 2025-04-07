<?php
include_once 'conecta_db.php';

if (
  isset($_POST['nome']) &&
  isset($_POST['email']) &&
  isset($_POST['senha']) &&
  isset($_POST['curso']) &&
  isset($_POST['periodo'])
) {
  $nome = $_POST['nome'];
  $email = $_POST['email'];
  $senha = $_POST['senha'];
  $curso = $_POST['curso'];
  $periodo = $_POST['periodo'];

  // Verifica o tipo de usuário pelo e-mail
  if (strpos($email, '@aluno') !== false) {
      $tipo_usuario = 'aluno';
  } elseif (strpos($email, '@professor') !== false) {
      $tipo_usuario = 'professor';
  } elseif (strpos($email, '@monitor') !== false) {
      $tipo_usuario = 'monitor';
  } else {
      echo "E-mail inválido. Use @aluno, @professor ou @monitor.";
      exit;
  }

  $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
  $oMysql = conecta_db();

  if ($oMysql->connect_error) {
      echo "Erro de conexão: " . $oMysql->connect_error;
      exit;
  }

  // Inserção na tabela usuarios
  $stmt = $oMysql->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, curso) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo_usuario, $curso);

  if ($stmt->execute()) {
      // Se for aluno, insere também na tabela aluno
      if ($tipo_usuario === 'aluno') {
          $stmtAluno = $oMysql->prepare("INSERT INTO aluno (nome_aluno, email_aluno, periodo_numero) VALUES (?, ?, ?)");
          $stmtAluno->bind_param("ssi", $nome, $email, $periodo);
          $stmtAluno->execute();
          $stmtAluno->close();
      }

      header('Location: index.html');
      exit;
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

<div class="container mt-3">
  <h2>CRUD - Inserir Usuário</h2>
  <p>Preencha os campos abaixo (e-mail institucional para definir o tipo de usuário):</p>    

  <form method="POST" action="index.php?page=1">

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

  <select name="periodo" class="form-select mb-3" required>
    <option value="" disabled selected>Selecione seu período</option>
    <option value="1">1º Período</option>
    <option value="2">2º Período</option>
    <option value="3">3º Período</option>
    <option value="4">4º Período</option>
    <option value="5">5º Período</option>
    <option value="6">6º Período</option>
    <option value="7">7º Período</option>
    <option value="8">8º Período</option>
  </select>

  <button type="submit" class="btn btn-primary">Cadastrar</button>

</form>
</div>

</body>
</html>