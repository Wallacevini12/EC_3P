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
        
            // Redirecionamento conforme o tipo de usuário
            if ($user['tipo_usuario'] === 'aluno') {
                // Buscar o código da tabela 'aluno'
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
        
                header('Location: registrar_pergunta.php');
                exit;
        
            } elseif ($user['tipo_usuario'] === 'professor') {
                header('Location: home_professor.php');
                exit;
        
            } elseif ($user['tipo_usuario'] === 'monitor') {
                header('Location: home_monitor.php');
                exit;
        
            } else {
                echo "Tipo de usuário inválido!";
            }
        }
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

<!-- Botão voltar fora do card -->
<div class="container mt-3">
    <a href="index.php" class="btn btn-secondary btn-sm mb-3">&larr; Voltar</a>
</div>

<!-- Card centralizado com o formulário -->
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="min-width: 350px; max-width: 500px; width: 100%;">
        <h2 class="mb-3">Login de Usuário</h2>
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
                class="form-control mb-3"
                placeholder="Senha"
                required>

            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>

</body>
</html>
