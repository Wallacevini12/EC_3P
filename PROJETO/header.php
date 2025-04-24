<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>LearnHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons (via CDN) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <!-- Marca -->
    <a class="navbar-brand" href="index.php"><i class="bi bi-lightning-fill"></i> LearnHub</a>

<!-- Menu à esquerda -->
<div class="collapse navbar-collapse" id="navbarNav">
  <ul class="navbar-nav">
    <li class="nav-item active">
      <a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
    </li>
    
    <?php if (isset($_SESSION['id'])): ?>
      <li class="nav-item">
        <a class="nav-link" href="minha_conta.php"><i class="bi bi-person-circle"></i> Minha Conta</a>
      </li>

      <!-- Mostrar "Fazer Pergunta" apenas se o usuário for aluno -->
      <?php if ($_SESSION['tipo_usuario'] === 'aluno'): ?>
        <li class="nav-item">
          <a class="nav-link" href="registrar_pergunta.php"><i class="bi bi-question-circle-fill"></i> Fazer Pergunta</a>
        </li>
      <?php endif; ?>
    
    <?php else: ?>
      <li class="nav-item">
        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
      </li>
    <?php endif; ?>
  </ul>
</div>

    <!-- Menu à direita (ex: logout) -->
    <?php if (isset($_SESSION['id'])): ?>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </li>
      </ul>
    <?php endif; ?>

  </div>
</nav>

<!-- Scripts Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>