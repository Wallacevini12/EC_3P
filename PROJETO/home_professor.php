<?php
include "header.php";

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1> Logado professor</h1>
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
          background-color: #007bff;
          border: none;
          border-radius: 8px;
          cursor: pointer;
          text-decoration: none;
          transition: background-color 0.3s;
        }
        .btn:hover {
          background-color: #0056b3;
        }
      </style>
    </head>
    <body>
    
      <h1>Bem-vindo!</h1>
      <div class="button-container">
        <a href="cadastro_monitor.php" class="btn">Cadastrar Monitor</a>
      </div>
    
    </body>
</body>
</html>