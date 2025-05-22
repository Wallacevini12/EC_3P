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
      color: greenyellow;
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

    .dropdown-menu.dropdown-menu-dark {
      background-color: #212529;
    }

    .dropdown-menu .dropdown-item,
    .dropdown-menu .dropdown-item i,
    .dropdown-menu .dropdown-item-text {
      color: greenyellow !important;
    }

    .dropdown-menu .dropdown-item:hover {
      background-color: rgba(173, 255, 47, 0.1);
      color: #adff2f !important;
    }

    body {
      padding-top: 100px;
    }

   .search-form input[type="search"] {
    background-color: #212529;      /* mesmo background do header */
    color: greenyellow;             /* texto em greenyellow */
    border: 1.5px solid greenyellow; /* borda greenyellow */
    border-radius: 10px;
  }

  .search-form input[type="search"]::placeholder {
    color: #adff2f; /* cor mais clara para placeholder */
  }

  .search-form button {
    background-color: transparent; /* botão transparente */
    border: 1.5px solid greenyellow; /* borda greenyellow */
    color: greenyellow;              /* ícone greenyellow */
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .search-form button:hover {
    background-color: rgba(173, 255, 47, 0.1); /* leve destaque no hover */
    border-color: #adff2f;
    color: #adff2f;
  }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
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
          <a class="nav-link" href="index.php"><i class="bi bi-house-door-fill me-1"></i> Home</a>
        </li>

        <?php if (isset($_SESSION['id'])): ?>
          <?php if ($_SESSION['tipo_usuario'] === 'aluno'): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="perguntasDropdown" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-question-circle-fill me-1"></i> Perguntas
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="perguntasDropdown">
                <li><a class="dropdown-item" href="registrar_pergunta.php"><i class="bi bi-pencil-square me-1"></i> Fazer Pergunta</a></li>
                <li><a class="dropdown-item" href="minhas_perguntas.php"><i class="bi bi-chat-left-text me-1"></i> Minhas Perguntas</a></li>
                <li><a class="dropdown-item" href="perguntas_recentes.php"><i class="bi bi-clock-history me-1"></i> Perguntas Recentes</a></li>
                <li><a class="dropdown-item" href="respostas_avaliadas.php"><i class="bi bi-star-fill me-1"></i> Respostas Avaliadas</a></li>
                <li><a class="dropdown-item" href="todas_perguntas.php"><i class="bi bi-list-ul me-1"></i> Todas Perguntas</a></li>
                <li><a class="dropdown-item" href="perguntas_respondidas.php"><i class="bi bi-check2-square me-1"></i> Perguntas Respondidas</a></li>
              </ul>
            </li>

          <?php elseif ($_SESSION['tipo_usuario'] === 'professor'): ?>

           <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdownMonitores" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-people-fill me-1"></i> Monitores
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMonitores">
                <li><a class="dropdown-item" href="lista_monitor.php"><i class="bi bi-list-ul me-1"></i> Lista de Monitores</a></li>
                <li><a class="dropdown-item" href="cadastro_monitor.php"><i class="bi bi-person-plus-fill me-1"></i> Cadastrar Monitor</a></li>
              </ul>
           </li>

           <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdownPerguntas" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-question-circle-fill me-1"></i> Perguntas
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownPerguntas">
                <li>
                  <a class="dropdown-item" href="perguntas_encaminhadas.php">
                    <i class="bi bi-send-check-fill me-1"></i> Encaminhadas para mim
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="todas_perguntas.php">
                    <i class="bi bi-list-ul me-1"></i> Todas as Perguntas
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="perguntas_respondidas.php">
                    <i class="bi bi-check2-square me-1"></i> Perguntas Respondidas
                  </a>
                </li>
              </ul>
            </li>

          <?php elseif ($_SESSION['tipo_usuario'] === 'monitor'): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropdownPerguntasMonitor" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-question-circle-fill me-1"></i> Perguntas
              </a>
              <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownPerguntasMonitor">
                <li><a class="dropdown-item" href="perguntas_monitor.php"><i class="bi bi-question-square-fill me-1"></i> Perguntas para mim</a></li>
                <li><a class="dropdown-item" href="todas_perguntas.php"><i class="bi bi-list-ul me-1"></i> Todas Perguntas</a></li>
                <li><a class="dropdown-item" href="minhas_respostas.php"><i class="bi bi-chat-left-text-fill me-1"></i> Minhas Respostas</a></li>
                <li><a class="dropdown-item" href="perguntas_respondidas.php"><i class="bi bi-check2-square me-1"></i> Perguntas Respondidas</a></li>
              </ul>
            </li>
          <?php endif; ?>


          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="listasDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-list-ul me-1"></i> Listas
            </a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="listasDropdown">
              <li>
                <a class="dropdown-item" href="lista_todos_monitores.php">
                  <i class="bi bi-people-fill me-1"></i> Lista de Monitores
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="lista_professores.php">
                  <i class="bi bi-person-badge-fill me-1"></i> Lista de Professores
                </a>
              </li>
            </ul>
          </li>



          <li class="nav-item">
            <a class="nav-link" href="ranking_monitores.php"><i class="bi bi-trophy-fill me-1"></i> Ranking de Monitores</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a>
          </li>
        <?php endif; ?>
      </ul>

      <form class="d-flex me-3 search-form" action="pesquisar_pergunta.php" method="get">
        <input class="form-control me-2" type="search" name="buscar" placeholder="Pesquisar pergunta..." aria-label="Pesquisar">
        <button type="submit"><i class="bi bi-search"></i></button>
      </form>

      <script>
        function getQueryParam(param) {
          const urlParams = new URLSearchParams(window.location.search);
          return urlParams.get(param);
        }

        if (getQueryParam('focarBusca') === '1') {
          const searchInput = document.querySelector('form.search-form input[name="buscar"]');
          if (searchInput) {
            searchInput.focus();
          }
        }
      </script>


      <?php if (isset($_SESSION['id']) && isset($_SESSION['nome']) && isset($_SESSION['tipo_usuario'])): ?>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-fill me-1"></i> <?= htmlspecialchars($_SESSION['nome']) ?> (<?= ucfirst($_SESSION['tipo_usuario']) ?>)
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="usuarioDropdown">
              <li>
                <a class="dropdown-item" href="minha_conta.php">
                  <i class="bi bi-person-circle me-1"></i> Minha Conta
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="logout.php">
                  <i class="bi bi-box-arrow-right me-1"></i> Sair
                </a>
              </li>
            </ul>
          </li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>