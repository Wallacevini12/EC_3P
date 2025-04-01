<?php
    if (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['senha'])) {
        $oMysql = conecta_db();
        $query = "INSERT INTO usuario (nome, email, senha)
                  VALUES ('".$_POST['nome']."', '".$_POST['email']."', '".$_POST['senha']."')";
        $resultado = $oMysql->query($query);
        header('location: index.php');
        $resultado = $oMysql->query($query);

if ($resultado) {

    echo "Usuário cadastrado com sucesso!";
} else {
    echo "Erro ao cadastrar: " . $oMysql->error;
}
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
  <p>Preencha os campos abaixo:</p>    

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
      placeholder="Email"
      required>

    <input
      type="password"
      name="senha"
      class="form-control mb-2"
      placeholder="Senha"
      required>

    <button type="submit" class="btn btn-primary">Cadastrar</button>

  </form>
</div>

</body>
</html>