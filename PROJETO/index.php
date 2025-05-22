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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd, #ffffff);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .main-content {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 30px 20px;
      text-align: center;
    }

    .brand-logo {
      width: 120px;
      height: auto;
      margin-bottom: 20px;
    }

    .brand-title {
      font-size: 3rem;
      font-weight: 700;
      color: #0d6efd;
      margin-bottom: 10px;
    }

    .brand-subtitle {
      font-size: 1.25rem;
      color: #555;
      margin-bottom: 40px;
    }

    .btn-home {
      font-size: 1.1rem;
      padding: 12px 24px;
      margin: 10px;
      border-radius: 8px;
      transition: transform 0.2s ease-in-out;
    }

    .btn-home:hover {
      transform: translateY(-3px);
    }

    footer {
      text-align: center;
      padding: 15px;
      background-color: #f1f1f1;
      font-size: 0.9rem;
      color: #666;
    }
  </style>
</head>

<body>

  <div class="main-content">
    <img src="images/logo.png" alt="Logo LearnHub" class="brand-logo">

    <div class="brand-title">
      <i class="bi bi-lightbulb-fill me-2"></i>LearnHub
    </div>
    <div class="brand-subtitle">A sua plataforma de aprendizado e colaboração acadêmica</div>

    <div>
      <a href="login.php" class="btn btn-primary btn-home">
        <i class="bi bi-box-arrow-in-right me-1"></i> Login
      </a>
      <a href="cadastro.php?tipo=aluno" class="btn btn-success btn-home">
        <i class="bi bi-person-plus-fill me-1"></i> Cadastrar como Aluno
      </a>
      <a href="cadastro.php?tipo=professor" class="btn btn-warning btn-home text-white">
        <i class="bi bi-person-workspace me-1"></i> Cadastrar como Professor
      </a>
    </div>
  </div>

  <footer>
    &copy; <?= date("Y") ?> LearnHub. Todos os direitos reservados.
  </footer>

</body>
</html>
