

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Lista de registros</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
    <h2>Lista de registros de usuários</h2>
    <p>Os dados que estão registrados na tabela <strong>usuario</strong> são:</p>    
    <p><a class="btn btn-primary" href="index.php?page=1">Adicione um novo registro</a></p>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Ações</th>
                <th>ID</th>
                <th>Nome</th>
                <th>email</th>
                <th>senha</th> 
            </tr>
        </thead>
        <tbody>
            <?php
            $OMYSQL = conecta_db();
            $Query = "SELECT * FROM usuario";
            $Resultado = $OMYSQL->query($Query);

            if ($Resultado) {
                while ($linha = $Resultado->fetch_object()) {
                    echo "<tr>";
                    echo "<td>
                            <a class='btn btn-success btn-sm' href='index.php?page=2&id=" . $linha->id . "'>Alterar</a>  
                            <a class='btn btn-danger btn-sm' href='index.php?page=3&id=" . $linha->id . "'>Excluir</a>
                          </td>";
                    echo "<td>" . $linha->id . "</td>";
                    echo "<td>" . htmlspecialchars($linha->nome, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($linha->email, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td>" . htmlspecialchars($linha->senha, ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='text-center'>Nenhum registro encontrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
