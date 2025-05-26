<?php
session_start();
require_once 'conecta_db.php';

if (isset($_POST['email'], $_POST['nova_senha'])) {
    $email = $_POST['email'];
    $novaSenha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

    $conn = conecta_db();
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
    $stmt->bind_param("ss", $novaSenha, $email);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $mensagem = "Senha atualizada com sucesso! Redirecionando para o login...";
        header("refresh:3; url=login.php");
    } else {
        $mensagem = "Usuário não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Recuperar Senha | LearnHub</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
  <h2 class="mb-4">Recuperar Senha</h2>

  <?php if (isset($mensagem)) { ?>
    <div class="alert alert-info"><?php echo $mensagem; ?></div>
  <?php } ?>

  <form method="POST" class="mt-3">
    <input type="email" name="email" class="form-control mb-3" placeholder="Seu e-mail cadastrado" required>
    <input type="password" name="nova_senha" class="form-control mb-3" placeholder="Nova senha" required>
    
    <div class="d-flex justify-content-between">
      <button type="submit" class="btn btn-primary">Atualizar Senha</button>
      <a href="login.php" class="btn btn-secondary">Cancelar</a>
    </div>
  </form>
</body>
</html>
