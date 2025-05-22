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
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    .navbar .nav-link,
    .navbar .navbar-brand,
    .navbar .navbar-text {
      color: greenyellow ;
      font-size: 0.8rem;
    }

    .navbar .nav-link:hover,
    .navbar .navbar-brand:hover {
      color: #adff2f;
      transform: scale(1.1);
      transition: transform 0.3s ease;
      text-decoration: underline;
    }

    .navbar-separator {
      border-left: 2px solid greenyellow;
      height: 40px;
      margin-left: 20px;
      margin-right: 10px;
    }

    body {
      padding-top: 100px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <!-- Marca -->
    <a class="navbar-brand d-flex align-items-center" href="<?php 
      if (isset($_SESSION['tipo_usuario'])) {
        switch ($_SESSION['tipo_usuario']) {
          case 'aluno': echo 'home_aluno.php'; break;
          case 'professor': echo 'home_professor.php'; break;
          case 'monitor': echo 'home_monitor.php'; break;
          default: echo 'index.php';
        }
      } else {
        echo 'index.php';
      }
    ?>">
      <img src="images/logo.png" alt="Logo" width="60" height="60" class="d-inline-block align-text-top me-2">
      <span>LearnHub</span>
    </a>

    <div class="navbar-separator"></div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php"><i class="bi bi-house-door-fill"></i> Home</a>
        </li>

        <?php if (isset($_SESSION['id'])): ?>
          <?php if ($_SESSION['tipo_usuario'] === 'aluno'): ?>
            <!-- Dropdown agrupado para Perguntas (Aluno) -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="perguntasDropdown" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-question-circle-fill"></i> Perguntas
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="perguntasDropdown">
                <li><a class="dropdown-item" href="registrar_pergunta.php">Fazer Pergunta</a></li>
                <li><a class="dropdown-item" href="minhas_perguntas.php">Minhas Perguntas</a></li>
                <li><a class="dropdown-item" href="perguntas_recentes.php">Perguntas Recentes</a></li>
                <li><a class="dropdown-item" href="respostas_avaliadas.php">Respostas Avaliadas</a></li>
              </ul>
            </li>

          <?php elseif ($_SESSION['tipo_usuario'] === 'professor'): ?>
            <!-- Dropdown: Monitores -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdownMonitores" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-people-fill"></i> Monitores
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMonitores">
                <li><a class="dropdown-item" href="lista_monitor.php">Lista de Monitores</a></li>
                <li><a class="dropdown-item" href="cadastro_monitor.php">Cadastrar Monitor</a></li>
              </ul>
            </li>

            <!-- Dropdown: Perguntas -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdownPerguntas" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-question-circle-fill"></i> Perguntas
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownPerguntas">
                <li><a class="dropdown-item" href="perguntas_encaminhadas.php">Encaminhadas</a></li>
                <li><a class="dropdown-item" href="perguntas_respondidas.php">Respondidas</a></li>
              </ul>
            </li>

          <?php elseif ($_SESSION['tipo_usuario'] === 'monitor'): ?>
            <li class="nav-item">
              <a class="nav-link" href="perguntas_monitor.php"><i class="bi bi-question-square-fill"></i> Perguntas para mim</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="minhas_respostas.php"><i class="bi bi-chat-left-text-fill"></i> Minhas Respostas</a>
            </li>
          <?php endif; ?>

          <!-- Itens comuns a todos -->
          <li class="nav-item">
            <a class="nav-link" href="perguntas_respondidas.php"><i class="bi bi-check2-square"></i> Perguntas Respondidas</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="ranking_monitores.php"><i class="bi bi-trophy-fill"></i> Ranking de Monitores</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Usuário logado com dropdown -->
      <?php if (isset($_SESSION['id']) && isset($_SESSION['nome']) && isset($_SESSION['tipo_usuario'])): ?>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-white" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-fill"></i> <?= htmlspecialchars($_SESSION['nome']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="usuarioDropdown">
              <li><span class="dropdown-item-text text-muted">(<?= ucfirst($_SESSION['tipo_usuario']) ?>)</span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="minha_conta.php"><i class="bi bi-person-circle"></i> Minha Conta</a></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
            </ul>
          </li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
