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
    <style>
        body {
          font-family: Arial, sans-serif;
          text-align: center;
          padding-top: 100px;
          background-color: #f4f4f4;
        }
        h1 {
          color: #333;
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
          background-color: #28a745;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          text-decoration: none;
          transition: background-color 0.3s;
        }
        .btn:hover {
          background-color: #218838;
        }
    </style>
</head>
<body>

    <h1>Bem-vindo, Aluno!</h1>
    <div class="button-container">
        <a href="registrar_pergunta.php" class="btn">Criar Nova Pergunta</a>
    </div>

</body>
</html>