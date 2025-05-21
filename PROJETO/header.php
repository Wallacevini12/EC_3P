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
      color: #adff2f; /* Tom levemente diferente ao passar o mouse */
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
  padding-top: 100px
  }

  
  
</style>
  
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <!-- Marca -->
    <a class="navbar-brand d-flex align-items-center" href="
    <?php 
      if (isset($_SESSION['tipo_usuario'])) {
        if ($_SESSION['tipo_usuario'] === 'aluno') {
          echo 'home_aluno.php';
        } elseif ($_SESSION['tipo_usuario'] === 'professor') {
          echo 'home_professor.php';
        } elseif ($_SESSION['tipo_usuario'] === 'monitor') {
          echo 'home_monitor.php';
        } else {
          echo 'index.php';
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

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="
            <?php 
              if (isset($_SESSION['tipo_usuario'])) {
                if ($_SESSION['tipo_usuario'] === 'aluno') {
                  echo 'home_aluno.php';
                } elseif ($_SESSION['tipo_usuario'] === 'professor') {
                  echo 'home_professor.php';
                } elseif ($_SESSION['tipo_usuario'] === 'monitor') {
                  echo 'home_monitor.php';
                } else {
                  echo 'index.php';
                }
              } else {
                echo 'index.php';
              }
            ?>
          "><i class="bi bi-house-door-fill"></i> Home</a>
        </li>


        <?php if (isset($_SESSION['id'])): ?>

          <!-- Mostrar "Fazer Pergunta" apenas para aluno -->
          <?php if ($_SESSION['tipo_usuario'] === 'aluno'): ?>
            <li class="nav-item">
                <a class="nav-link" href="registrar_pergunta.php"><i class="bi bi-question-circle-fill"></i> Fazer Pergunta</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="minhas_perguntas.php"><i class="bi bi-chat-left-text-fill"></i> Minhas Perguntas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="perguntas_recentes.php"><i class="bi bi-clock-history"></i> Perguntas Recentes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="respostas_avaliadas.php"><i class="bi bi-star-fill"></i> Respostas Avaliadas</a>
            </li>
          <?php endif; ?>

          <!-- Header de professor-->
          <?php if ($_SESSION['tipo_usuario'] === 'professor'): ?>
            <li class="nav-item">
              <a class="nav-link" href="lista_monitor.php"><i class="bi bi-people-fill"></i> Lista de Monitores</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="perguntas_encaminhadas.php"><i class="bi bi-send-fill"></i> Perguntas Encaminhadas</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="cadastro_monitor.php"><i class="bi bi-person-plus-fill"></i> Cadastrar Monitor</a>
            </li>
          <?php endif; ?>


          <!-- Mostrar "Perguntas" apenas para monitor -->
          <?php if ($_SESSION['tipo_usuario'] === 'monitor'): ?>
            <li class="nav-item">
              <a class="nav-link" href="perguntas_monitor.php"><i class="bi bi-question-square-fill"></i> Perguntas para mim</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="minhas_respostas.php"><i class="bi bi-chat-left-text-fill"></i> Minhas Respostas</a>
            </li>
          <?php endif; ?>



            <!-- Mostrar "Perguntas respondidas" para todos os usuários desde que logados -->
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



      <!-- DADOS DO USUÁRIO LOGADO -->
      <?php if (isset($_SESSION['id']) && isset($_SESSION['nome']) && isset($_SESSION['tipo_usuario'])): ?>
        <li class="nav-item">
          <span class="navbar-text text-white me-3">
            <i class="bi bi-person-fill"></i> <?php echo htmlspecialchars($_SESSION['nome']); ?> (<?php echo ucfirst($_SESSION['tipo_usuario']); ?>)
          </span>
        </li>
      <?php endif; ?>

      <!-- Botão de logout na direita -->
      <?php if (isset($_SESSION['id'])): ?>
        <ul class="navbar-nav ms-auto">

        

          <li class="nav-item">
            <a class="nav-link" href="minha_conta.php"><i class="bi bi-person-circle"></i> Minha Conta</a>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a>
          </li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>