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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Link para o Font Awesome -->
    <style>
        body {
          font-family: Arial, sans-serif;
          text-align: center;
          padding-top: 50px;
          background-color: #f4f4f4;
        }
        h1 {
          color: #333;
        }
        .card-container {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 20px;
          margin-top: 30px;
          width: 80%; /* Largura de 80% */
          margin-left: auto; /* Centraliza o container */
          margin-right: auto; /* Centraliza o container */
        }
        .card {
          background-color: #fff;
          border: 1px solid #ddd;
          border-radius: 8px;
          padding: 20px;
          text-align: center;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          transition: transform 0.3s ease;
        }
        .card:hover {
          transform: translateY(-5px);
        }
        .card i {
          font-size: 40px;
          color: #28a745;
          margin-bottom: 10px;
        }
        .card a {
          display: block;
          font-size: 16px;
          color: #333;
          text-decoration: none;
          font-weight: bold;
        }
        .card a:hover {
          color: #28a745;
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
          .card-container {
            grid-template-columns: repeat(2, 1fr); /* 2 colunas em telas menores */
          }
        }

        @media (max-width: 480px) {
          .card-container {
            grid-template-columns: 1fr; /* 1 coluna em telas muito pequenas */
          }
        }
    </style>
</head>
<body>

    <h1>Bem-vindo, Aluno!</h1>

    <div class="card-container">
        <div class="card">
            <i class="fas fa-question-circle"></i>
            <a href="registrar_pergunta.php">Fazer Pergunta</a>
        </div>
        <div class="card">
            <i class="fas fa-list-ul"></i>
            <a href="minhas_perguntas.php">Minhas Perguntas</a>
        </div>
        <div class="card">
            <i class="fas fa-clock"></i>
            <a href="perguntas_recentes.php">Perguntas Recentes</a>
        </div>
        <div class="card">
            <i class="fas fa-user"></i>
            <a href="minha_conta.php">Minha Conta</a>
        </div>
        <div class="card">
            <i class="fas fa-sign-out-alt"></i>
            <a href="logout.php">Sair</a>
        </div>
    </div>

</body>
</html>
