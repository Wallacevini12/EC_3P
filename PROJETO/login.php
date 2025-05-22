<?php
session_start();
require_once 'conecta_db.php';

if (isset($_SESSION['id'])) {
    switch ($_SESSION['tipo_usuario']) {
        case 'aluno': header('Location: home_aluno.php'); exit;
        case 'professor': header('Location: home_professor.php'); exit;
        case 'monitor': header('Location: home_monitor.php'); exit;
    }
}

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $oMysql = conecta_db();

    if ($oMysql->connect_error) {
        die("Erro de conexão: " . $oMysql->connect_error);
    }

    $stmt = $oMysql->prepare("SELECT id, nome, email, senha, tipo_usuario FROM usuarios WHERE email = ?");
    if (!$stmt) {
        die("Erro no prepare: " . $oMysql->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user['senha'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

            if ($user['tipo_usuario'] === 'aluno') {
                $stmtAluno = $oMysql->prepare("SELECT a.id AS codigo_aluno FROM aluno a WHERE a.id = ?");
                $stmtAluno->bind_param("i", $user['id']);
                $stmtAluno->execute();
                $stmtAluno->bind_result($codigo_aluno);
                $stmtAluno->fetch();
                $_SESSION['codigo_aluno'] = $codigo_aluno;
                $stmtAluno->close();

                header('Location: home_aluno.php'); exit;
            } elseif ($user['tipo_usuario'] === 'professor') {
                header('Location: home_professor.php'); exit;
            } elseif ($user['tipo_usuario'] === 'monitor') {
                header('Location: home_monitor.php'); exit;
            } else {
                $erro = "Tipo de usuário inválido!";
            }
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }

    $stmt->close();
    $oMysql->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Login | LearnHub</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(to right, #eef2f7, #ffffff);
      font-family: 'Segoe UI', sans-serif;
    }

    .card {
      border-radius: 20px;
      border: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .form-control {
      border-radius: 12px;
    }

    .btn-primary {
      background-color: #0d6efd;
      border-radius: 12px;
      font-weight: 500;
      padding: 10px;
    }

    .top-title {
      text-align: center;
      margin-top: 40px;
      margin-bottom: 20px;
    }

    .top-title h1 {
      font-size: 2rem;
      color: #0d6efd;
    }

    .btn-back {
      position: absolute;
      left: 20px;
      top: 20px;
    }

    @media (max-width: 576px) {
      .top-title h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<a href="index.php" class="btn btn-secondary btn-sm btn-back">&larr; Voltar</a>

<div class="top-title">
  <h1>Login no LearnHub</h1>
</div>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card p-4 w-100" style="max-width: 500px;">
    <h2 class="mb-3 text-center">Bem-vindo de volta!</h2>
    <p class="text-center">Digite seu e-mail e senha para acessar:</p>

    <?php if (isset($erro)) { ?>
      <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php } ?>

    <form method="POST" action="login.php">
      <input type="email" name="email" class="form-control mb-3" placeholder="E-mail cadastrado" required>
      <input type="password" name="senha" class="form-control mb-3" placeholder="Senha" required>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>
  </div>
</div>

</body>
</html>
