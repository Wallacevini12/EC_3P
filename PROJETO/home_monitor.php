<?php
session_start();

// Garante que apenas monitores logados acessem
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include "header.php";
?>

<style>
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

<div class="container mt-5">
    <h1 class="mb-4 text-center">Bem-vindo, Monitor!</h1>

    <div class="row g-3 justify-content-center">

        <div class="col-6 col-md-4">
            <a href="perguntas_monitor.php" class="dashboard-card">
                <i class="bi bi-question-circle-fill dashboard-icon"></i>
                <p class="dashboard-title">Perguntas para mim</p>
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
