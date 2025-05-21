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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Aluno</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding-top: 50px;
            background-color: #f4f4f4;
        }
        h1 {
            margin-top: 5%;
            color: #333;
        }
        .card-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 30px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
        }
        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card i {
            font-size: 40px;
            color: #28a745;
            margin-bottom: 10px;
        }
        .card span {
            display: block;
            font-size: 16px;
            color: #333;
            font-weight: bold;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            .card-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .card-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <h1>Bem-vindo, Aluno!</h1>

    <div class="card-container">

        <a href="registrar_pergunta.php" class="card-link">
            <div class="card">
                <i class="fas fa-question-circle"></i>
                <span>Fazer Pergunta</span>
            </div>
        </a>

        <a href="minhas_perguntas.php" class="card-link">
            <div class="card">
                <i class="fas fa-list-ul"></i>
                <span>Minhas Perguntas</span>
            </div>
        </a>

        <a href="perguntas_recentes.php" class="card-link">
            <div class="card">
                <i class="fas fa-clock"></i>
                <span>Perguntas Recentes</span>
            </div>
        </a>

        <a href="respostas_avaliadas.php" class="card-link">
            <div class="card">
                <i class="fas fa-star"></i>
                <span>Respostas Avaliadas</span>
            </div>
        </a>

        <a href="perguntas_respondidas.php" class="card-link">
            <div class="card">
                <i class="fas fa-check-circle"></i>
                <span>Perguntas Respondidas</span>
            </div>
        </a>

        <a href="ranking_monitores.php" class="card-link">
            <div class="card">
                <i class="fas fa-trophy"></i>
                <span>Ranking de Monitores</span>
            </div>
        </a>

        <a href="pesquisar_pergunta.php" class="card-link">
            <div class="card">
                <i class="fas fa-search"></i>
                <span>Pesquisar Pergunta</span>
            </div>
        </a>

        <a href="minha_conta.php" class="card-link">
            <div class="card">
                <i class="fas fa-user"></i>
                <span>Minha Conta</span>
            </div>
        </a>

        <a href="logout.php" class="card-link">
            <div class="card">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sair</span>
            </div>
        </a>

    </div>

</body>
</html>
