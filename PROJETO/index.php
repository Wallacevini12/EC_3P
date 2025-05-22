<?php
session_start();

// Redireciona automaticamente para a home correta se já estiver logado
if (isset($_SESSION['id']) && isset($_SESSION['tipo_usuario'])) {
    switch ($_SESSION['tipo_usuario']) {
        case 'aluno':
            header("Location: home_aluno.php");
            exit;
        case 'professor':
            header("Location: home_professor.php");
            exit;
        case 'monitor':
            header("Location: home_monitor.php");
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>LearnHub - Página Inicial</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding-top: 100px;
      background-color: #f4f4f4;
    }

    h1 {
      color: #333;
      font-weight: bold;
    }

    .button-container {
      margin-top: 30px;
    }

    .btn {
      display: inline-block;
      padding: 12px 24px;
      margin: 10px;
      font-size: 16px;
      color: white;
      background-color: #007bff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    footer {
      margin-top: 80px;
      font-size: 0.9rem;
      color: #888;
    }
  </style>
</head>

<body>

  <h1>Bem-vindo ao LearnHub!</h1>

  <div class="button-container">
    <a href="login.php" class="btn">Login</a>
    <a href="cadastro.php?tipo=aluno" class="btn">Cadastrar como Aluno</a>
    <a href="cadastro.php?tipo=professor" class="btn">Cadastrar como Professor</a>
  </div>

  <footer>
    <p>&copy; <?php echo date("Y"); ?> LearnHub. Todos os direitos reservados.</p>
  </footer>

</body>
</html>
