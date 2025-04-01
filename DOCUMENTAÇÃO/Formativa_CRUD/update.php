<?php
if (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['senha'])) {
    $oMysql = conecta_db();

    // Atribuindo os valores das variáveis diretamente
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];


    $query = "UPDATE usuario 
              SET nome = '$nome', email = '$email', senha = '$senha' 
              WHERE id = " . $_GET['id'];

    // Executando a query
    $resultado = $oMysql->query($query);

    // Redirecionando para index.php
    header('location: index.php');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Cadastro Usuário</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
  <h2>CRUD - Update : <?php echo $_GET['id']; ?></h2>
  <p>Preencha para atualizar os dados:</p>    

		<form
			method="POST"
			action="index.php?page=2&id=<?php echo $_GET['id']; ?>">
		
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

    <button type="submit" 
    class="btn btn-primary">Enviar</button>

	    </form>
</div>

</body>
</html>
