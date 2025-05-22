<?php
session_start();

// Garante que apenas alunos logados acessem
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'aluno') {
    header("Location: login.php");
    exit;
}

include "header.php";
?>

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
            <a href="todas_perguntas.php" class="dashboard-card">
                <i class="bi bi-list-ul dashboard-icon"></i>
                <p class="dashboard-title">Todas as Perguntas</p>
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
