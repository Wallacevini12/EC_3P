<?php
include_once 'conecta_db.php'; 

session_start();

if (isset($_POST['email']) && isset($_POST['senha'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $oMysql = conecta_db();

    if ($oMysql->connect_error) {
        die("Erro de conexão: " . $oMysql->connect_error);
    }

    $stmt = $oMysql->prepare("SELECT id, nome, email, senha, tipo_usuario FROM usuarios WHERE email = ?");

    if (!$stmt) {
        die("Erro no prepare: " . $oMysql->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user['senha'])) {
            // Login sucesso
            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

            // Se for aluno, buscar o código da tabela 'aluno'
            if ($user['tipo_usuario'] === 'aluno') {
                $stmtAluno = $oMysql->prepare("SELECT a.id AS codigo_aluno
                                                        FROM aluno a
                                                        JOIN usuarios u ON a.id = u.id
                                                        WHERE u.email = ?;");
                $stmtAluno->bind_param("s", $email);
                $stmtAluno->execute();
                $stmtAluno->bind_result($codigo_aluno);
                $stmtAluno->fetch();
                $_SESSION['codigo_aluno'] = $codigo_aluno;
                $stmtAluno->close();
            }

            // Redireciona para a página onde o aluno pode registrar a pergunta
            header('Location: registrar_pergunta.php');
            exit;

        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }

    $stmt->close();
    $oMysql->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Login de Usuários</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-3">
    <h2>Login de Usuário</h2>
    <p>Informe seus dados para acessar:</p>

    <?php if (isset($erro)) { ?>
        <div class="alert alert-danger"><?php echo $erro; ?></div>
    <?php } ?>

    <form method="POST" action="login.php">

        <input
            type="email"
            name="email"
            class="form-control mb-2"
            placeholder="Email cadastrado"
            required>

        <input
            type="password"
            name="senha"
            class="form-control mb-2"
            placeholder="Senha"
            required>

        <button type="submit" class="btn btn-primary">Entrar</button>

    </form>
</div>

</body>
</html>
