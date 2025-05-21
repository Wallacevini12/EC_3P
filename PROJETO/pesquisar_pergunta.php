<?php
session_start();
if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header("Location: login.php");
    exit;
}

include "header.php";
include_once 'conecta_db.php';

$oMysql = conecta_db();
if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$buscar = '';
$resultados = [];

if (isset($_GET['buscar'])) {
    $buscar = trim($_GET['buscar']);

    if (!empty($buscar)) {
        $buscar_esc = $oMysql->real_escape_string($buscar);
        $palavras = explode(' ', $buscar_esc);

        $condicoes = [];
        foreach ($palavras as $palavra) {
            $palavra = trim($palavra);
            if ($palavra !== '') {
                $condicoes[] = "p.enunciado LIKE '%$palavra%'";
            }
        }

        if (count($condicoes) > 0) {
            $where = implode(' AND ', $condicoes);

            $query = "
                SELECT p.codigo_pergunta, p.enunciado, p.data_criacao, p.status,
                       u.nome AS nome_aluno,
                       d.nome_disciplina,
                       r.resposta,
                       r.codigo_resposta
                FROM perguntas p
                JOIN usuarios u ON p.usuario_codigo = u.id
                JOIN disciplinas d ON p.disciplina_codigo = d.codigo_disciplina
                LEFT JOIN respostas r ON p.codigo_pergunta = r.codigo_pergunta
                WHERE $where
                ORDER BY p.data_criacao DESC
            ";

            $result = $oMysql->query($query);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $resultados[] = $row;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Pesquisar Pergunta</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            padding-top: 40px;
            background-color: #f4f4f4;
        }
        .tag {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            color: #495057;
        }
        .status-respondida {
            background-color: #d4edda !important;
            color: #155724 !important;
        }
        .status-aguardando {
            background-color: #f8d7da !important;
            color: #721c24 !important;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-4"></h1>

    <form method="GET" action="pesquisar_pergunta.php" class="mb-5 text-center" style="margin-top: 60px;">
        <input type="text" name="buscar" placeholder="Digite palavras-chave..." value="<?php echo htmlspecialchars($buscar); ?>" class="form-control d-inline-block" style="width: 300px;" />
        <button type="submit" class="btn btn-success ms-2">Pesquisar</button>
    </form>

    <?php if (isset($_GET['buscar'])): ?>

        <?php if (count($resultados) > 0): ?>
            <?php foreach ($resultados as $row): 
                $statusClass = $row['status'] === 'respondida' ? 'status-respondida' : 'status-aguardando';
            ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="tag"><strong>Data:</strong> <?= date('d/m/Y', strtotime($row['data_criacao'])) ?></span>
                            <span class="tag"><strong>Disciplina:</strong> <?= htmlspecialchars($row['nome_disciplina']) ?></span>
                            <span class="tag"><strong>Aluno:</strong> <?= htmlspecialchars($row['nome_aluno']) ?></span>
                            <span class="tag <?= $statusClass ?>"><strong>Status:</strong> <?= ucfirst($row['status']) ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Enunciado:</strong><br><?= nl2br(htmlspecialchars($row['enunciado'])) ?></p>
                        <?php if (!empty($row['resposta'])): ?>
                            <hr>
                            <p><strong>Resposta:</strong><br><?= nl2br(htmlspecialchars($row['resposta'])) ?></p>
                            <hr>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">Nenhuma pergunta encontrada para sua busca.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$oMysql->close();
?>
