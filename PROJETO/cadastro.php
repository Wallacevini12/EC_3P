<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "learnhub"; 

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $curso = $_POST['curso'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($senha !== $confirmar_senha) {
        $mensagem = "As senhas não coincidem.";
    } else {
        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $mensagem = "Este e-mail já está cadastrado.";
        } else {
            $sql = "INSERT INTO usuarios (nome, email, curso, senha) VALUES ('$nome', '$email', '$curso', '$senha')";

            if ($conn->query($sql) === TRUE) {
                $mensagem = "Cadastro realizado com sucesso!";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'inicio.html';
                        }, 3000);
                      </script>";
            } else {
                $mensagem = "Erro ao cadastrar: " . $conn->error;
            }
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Learnhub</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #333;
            font-family: Arial, sans-serif;
        }
        form {
            width: 400px;
            padding: 40px;
            margin: 0 20px;
            background-color: black;
            box-shadow: 4px 4px 8px black;
            border-radius: 8px;
            border: 2px solid greenyellow;
            color: greenyellow;
            position: relative;
        }

        form:hover {
            box-shadow: 4px 4px 8px greenyellow;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        label, input, select, button {
            display: block;
            width: calc(100% - 20px);
            margin-bottom: 15px;
        }

        input, select {
            padding: 10px;
            border: 1px solid greenyellow;
            border-radius: 4px;
            background-color: black;
            color: greenyellow;
        }

        button {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: greenyellow;
            color: black;
            padding: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            color: black;
            font-weight: bold;
            font-size: 1.3em;
        }

        #mensagem {
            background-color: greenyellow;
            color: black;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 18px;
            border-radius: 5px;
            width: calc(100% - 20px);
            position: absolute;
            top: -60px;
            left: 10px;
        }
    </style>
</head>
<body>
    <form action="" method="post">
        <?php if (isset($mensagem)) { echo "<div id='mensagem'>$mensagem</div>"; } ?>
        <h1>Cadastro</h1>
        <label for="nome">Informe seu nome:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="email">Informe seu email:</label>
        <input type="email" id="email" name="email" required>

        <label for="curso">Selecione seu curso:</label>
        <select id="curso" name="curso">
            <option value="Engenharia Civil">Engenharia Civil</option>
            <option value="Engenharia de Computação">Engenharia de Computação</option>
            <option value="Engenharia de Software">Engenharia de Software</option>
            <option value="Engenharia Elétrica">Engenharia Elétrica</option>
            <option value="Engenharia Mecânica">Engenharia Mecânica</option>
            <option value="Administração">Administração</option>
            <option value="Direito">Direito</option>
            <option value="Medicina">Medicina</option>
            <option value="Arquitetura e Urbanismo">Arquitetura e Urbanismo</option>
            <option value="Economia">Economia</option>
            <option value="Psicologia">Psicologia</option>
            <option value="Ciências Contábeis">Ciências Contábeis</option>
            <option value="Design Gráfico">Design Gráfico</option>
            <option value="Nutrição">Nutrição</option>
            <option value="Jornalismo">Jornalismo</option>
            <option value="Publicidade e Propaganda">Publicidade e Propaganda</option>
        </select>

        <label for="senha">Crie sua senha:</label>
        <input type="password" id="senha" name="senha" required>

        <label for="confirmar_senha">Confirme sua senha:</label>
        <input type="password" id="confirmar_senha" name="confirmar_senha" required>

        <button type="submit">CADASTRAR</button>
    </form>
</body>
</html>
