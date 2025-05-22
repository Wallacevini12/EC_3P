<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header("Location: login.php");
    exit;
}

include "header.php";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Home Aluno</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <style>
        body {
            background-color: #f4f4f4;
        }

        h1 {
            margin-top: 40px;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
            font-weight: 600;
        }

        .dashboard-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            color: #333;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 150px;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .dashboard-icon {
            font-size: 2.5rem;
            color: #28a745; /* Verde */
            margin-bottom: 10px;
        }

        .dashboard-title {
            font-weight: 600;
            font-size: 1rem;
            text-align: center;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1>Bem-vindo, Aluno!</h1>

        <div class="row g-3 justify-content-center">

            <div class="col-6 col-md-4">
                <a href="registrar_pergunta.php" class="dashboard-card">
                    <i class="bi bi-question-circle-fill dashboard-icon"></i>
                    <p class="dashboard-title">Fazer Pergunta</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="minhas_perguntas.php" class="dashboard-card">
                    <i class="bi bi-chat-left-text dashboard-icon"></i>
                    <p class="dashboard-title">Minhas Perguntas</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="perguntas_recentes.php" class="dashboard-card">
                    <i class="bi bi-clock-history dashboard-icon"></i>
                    <p class="dashboard-title">Perguntas Recentes</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="respostas_avaliadas.php" class="dashboard-card">
                    <i class="bi bi-star-fill dashboard-icon"></i>
                    <p class="dashboard-title">Respostas Avaliadas</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="perguntas_respondidas.php" class="dashboard-card">
                    <i class="bi bi-check2-square dashboard-icon"></i>
                    <p class="dashboard-title">Perguntas Respondidas</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="ranking_monitores.php" class="dashboard-card">
                    <i class="bi bi-trophy-fill dashboard-icon"></i>
                    <p class="dashboard-title">Ranking de Monitores</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="pesquisar_pergunta.php" class="dashboard-card">
                    <i class="bi bi-search dashboard-icon"></i>
                    <p class="dashboard-title">Pesquisar Pergunta</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="minha_conta.php" class="dashboard-card">
                    <i class="bi bi-person-fill dashboard-icon"></i>
                    <p class="dashboard-title">Minha Conta</p>
                </a>
            </div>

            <div class="col-6 col-md-4">
                <a href="logout.php" class="dashboard-card">
                    <i class="bi bi-box-arrow-right dashboard-icon"></i>
                    <p class="dashboard-title">Sair</p>
                </a>
            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>