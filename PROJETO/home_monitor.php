<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include "header.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Home Monitor</title>
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
        a.card {
            text-decoration: none;
            color: inherit;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            display: block;
        }
        a.card:hover {
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

    <h1>Bem-vindo, Monitor!</h1>

    <div class="card-container">

        <a href="perguntas_monitor.php" class="card">
            <i class="fas fa-question-circle"></i>
            <span>Perguntas para mim</span>
        </a>

        <a href="perguntas_respondidas.php" class="card">
            <i class="fas fa-check-circle"></i>
            <span>Perguntas Respondidas</span>
        </a>

        <a href="ranking_monitores.php" class="card">
            <i class="fas fa-trophy"></i>
            <span>Ranking de Monitores</span>
        </a>

        <a href="minha_conta.php" class="card">
            <i class="fas fa-user"></i>
            <span>Minha Conta</span>
        </a>

        <a href="sair.php" class="card">
            <i class="fas fa-sign-out-alt"></i>
            <span>Sair</span>
        </a>

    </div>

</body>
</html>