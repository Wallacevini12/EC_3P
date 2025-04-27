<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header("Location: login.php");
    exit;
}
include "header.php";
// Incluir o arquivo de conexão com o banco de dados
include_once 'conecta_db.php'; 

// Conectar ao banco de dados
$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Página do Monitor</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-5 text-center">
<br>
<br>
<br>
    <h1>Você está logado como monitor</h1>
</div>

</body>
</html>