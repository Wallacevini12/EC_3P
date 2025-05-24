<?php
require_once 'conecta_db.php';

$erro = '';

// Obtém o tipo de usuário via URL do index.php
if (isset($_GET['tipo']) && in_array($_GET['tipo'], ['aluno', 'professor'])) {
  $tipo_usuario = $_GET['tipo'];
} else {
  echo "<script>alert('Tipo de usuário inválido ou não especificado.'); window.location.href = 'index.php';</script>";
  exit;
}

function senha_forte($senha) {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['nome']) &&
        isset($_POST['email']) &&
        isset($_POST['senha']) &&
        isset($_POST['curso'])
    ) {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $curso = $_POST['curso'];

        // Validação da senha no backend
        if (!senha_forte($senha)) {
            $erro = "Senha fraca. Use no mínimo 8 caracteres com letras maiúsculas, minúsculas, números e caracteres especiais.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $oMysql = conecta_db();

            if ($oMysql->connect_error) {
                $erro = "Erro de conexão: " . $oMysql->connect_error;
            } else {
                $stmt_check = $oMysql->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt_check->bind_param("s", $email);
                $stmt_check->execute();
                $stmt_check->store_result();

                if ($stmt_check->num_rows > 0) {
                    $erro = "E-mail já cadastrado.";
                } else {
                    $stmt_check->close();

                    $stmt = $oMysql->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, curso) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo_usuario, $curso);

                    if ($stmt->execute()) {
                        $usuario_id = $oMysql->insert_id;

                        if ($tipo_usuario === 'aluno') {
                            $stmt2 = $oMysql->prepare("INSERT INTO aluno (id) VALUES (?)");
                        } elseif ($tipo_usuario === 'professor') {
                            $stmt2 = $oMysql->prepare("INSERT INTO professor (id) VALUES (?)");
                        }

                        if ($tipo_usuario === 'aluno' && isset($_POST['disciplinas'])) {
                            $disciplinas = $_POST['disciplinas'];
                            $stmt_disc = $oMysql->prepare("INSERT INTO alunos_possuem_disciplinas (aluno_codigo, disciplina_codigo) VALUES (?, ?)");

                            foreach ($disciplinas as $disciplina_id) {
                                $disciplina_id = intval($disciplina_id);
                                $stmt_disc->bind_param("ii", $usuario_id, $disciplina_id);
                                $stmt_disc->execute();
                            }
                            $stmt_disc->close();
                        }

                        if ($tipo_usuario === 'professor' && isset($_POST['disciplinas'])) {
                            $disciplinas = $_POST['disciplinas'];
                            $stmt_disc = $oMysql->prepare("INSERT INTO professores_possuem_disciplinas (professor_codigo, disciplina_codigo) VALUES (?, ?)");

                            foreach ($disciplinas as $disciplina_id) {
                                $disciplina_id = intval($disciplina_id);
                                $stmt_disc->bind_param("ii", $usuario_id, $disciplina_id);
                                $stmt_disc->execute();
                            }
                            $stmt_disc->close();
                        }

                        $stmt2->bind_param("i", $usuario_id);

                        if ($stmt2->execute()) {
                            if ($tipo_usuario === 'aluno') {
                                $stmt_curso = $oMysql->prepare("SELECT codigo_curso FROM curso WHERE nome_curso = ?");
                                $stmt_curso->bind_param("s", $curso);
                                $stmt_curso->execute();
                                $stmt_curso->bind_result($codigo_curso);
                                if ($stmt_curso->fetch()) {
                                    $stmt_curso->close();

                                    $stmt_intermediaria = $oMysql->prepare("INSERT INTO alunos_possuem_cursos (aluno_codigo, curso_codigo) VALUES (?, ?)");
                                    $stmt_intermediaria->bind_param("ii", $usuario_id, $codigo_curso);
                                    $stmt_intermediaria->execute();
                                    $stmt_intermediaria->close();
                                } else {
                                    $erro = "Curso não encontrado.";
                                }
                            } elseif ($tipo_usuario === 'professor') {
                                $stmt_curso = $oMysql->prepare("SELECT codigo_curso FROM curso WHERE nome_curso = ?");
                                $stmt_curso->bind_param("s", $curso);
                                $stmt_curso->execute();
                                $stmt_curso->bind_result($codigo_curso);
                                if ($stmt_curso->fetch()) {
                                    $stmt_curso->close();

                                    $stmt_intermediaria = $oMysql->prepare("INSERT INTO cursos_possuem_professores (curso_codigo, professor_codigo) VALUES (?, ?)");
                                    $stmt_intermediaria->bind_param("ii", $codigo_curso, $usuario_id);
                                    $stmt_intermediaria->execute();
                                    $stmt_intermediaria->close();
                                } else {
                                    $erro = "Curso não encontrado.";
                                }
                            }

                            if (!$erro) {
                                echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href = 'index.php';</script>";
                                exit;
                            }
                        } else {
                            $erro = "Erro ao cadastrar tipo específico: " . $stmt2->error;
                        }
                        $stmt2->close();
                    } else {
                        $erro = "Erro ao cadastrar: " . $stmt->error;
                    }
                    $stmt->close();
                }

                $oMysql->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Cadastro de Usuários</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .msg-erro {
      color: red;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<div class="container mt-3">
  <a href="index.php" class="btn btn-secondary btn-sm mb-3">&larr; Voltar</a>
</div>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow p-4" style="min-width: 350px; max-width: 500px; width: 100%;">
    <h2 class="mb-3">Cadastro de Usuário</h2>
    <p>Preencha os campos abaixo:</p>    

    <label style="color: red;">*  = Campo obrigatório</label>
    <br>

    <?php if ($erro): ?>
      <div class="msg-erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <form id="formCadastro" method="POST" action="cadastro.php?tipo=<?= htmlspecialchars($tipo_usuario) ?>">

      <label for="nome" class="form-label">Nome <span style="color: red;">*</span></label>
      <input
        type="text"
        name="nome"
        id="nome"
        class="form-control mb-2"
        placeholder="Nome"
        required
        value="<?= isset($nome) ? htmlspecialchars($nome) : '' ?>"
      >

      <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
      <input
        type="email"
        name="email"
        id="email"
        class="form-control mb-2"
        placeholder="Email (ex: maria@email.com)"
        required
        value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
      >

      <label for="senha" class="form-label">Senha <span style="color: red;">*</span></label>
      <input
        type="password"
        name="senha"
        id="senha"
        class="form-control mb-2"
        placeholder="Senha"
        required
      >

      <label for="curso" class="form-label">Curso <span style="color: red;">*</span></label>
      <select name="curso" id="curso" class="form-select mb-3" required>
        <option value="" disabled <?= !isset($curso) ? 'selected' : '' ?>>Selecione seu curso</option>
        <option value="Engenharia de Software" <?= (isset($curso) && $curso === 'Engenharia de Software') ? 'selected' : '' ?>>Engenharia de Software</option>
        <option value="Sistemas de Informação" <?= (isset($curso) && $curso === 'Sistemas de Informação') ? 'selected' : '' ?>>Sistemas de Informação</option>
        <option value="Análise e Desenvolvimento de Sistemas" <?= (isset($curso) && $curso === 'Análise e Desenvolvimento de Sistemas') ? 'selected' : '' ?>>Análise e Desenvolvimento de Sistemas</option>
        <option value="Ciência da Computação" <?= (isset($curso) && $curso === 'Ciência da Computação') ? 'selected' : '' ?>>Ciência da Computação</option>
        <option value="Redes de Computadores" <?= (isset($curso) && $curso === 'Redes de Computadores') ? 'selected' : '' ?>>Redes de Computadores</option>
      </select>

      <?php
        require_once 'conecta_db.php';
        $oMysql = conecta_db();
        $disciplinas = [];
        $result = $oMysql->query("SELECT codigo_disciplina, nome_disciplina FROM disciplinas");
        while ($row = $result->fetch_assoc()) {
            $disciplinas[] = $row;
        }
        $oMysql->close();
      ?>

      <div class="mb-3">
        <label for="disciplinas" class="form-label">Disciplinas</label><br>
        <?php foreach ($disciplinas as $disciplina): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="disciplinas[]" value="<?= $disciplina['codigo_disciplina'] ?>" id="disciplina<?= $disciplina['codigo_disciplina'] ?>"
              <?php 
                if (isset($_POST['disciplinas']) && in_array($disciplina['codigo_disciplina'], $_POST['disciplinas'])) {
                    echo "checked";
                }
              ?>
            >
            <label class="form-check-label" for="disciplina<?= $disciplina['codigo_disciplina'] ?>">
              <?= htmlspecialchars($disciplina['nome_disciplina']) ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <input type="hidden" name="tipo_usuario_url" value="<?= htmlspecialchars($tipo_usuario) ?>">

      <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
    </form>


  </div>
</div>

<script>
document.getElementById('formCadastro').addEventListener('submit', function(e) {
    const senha = document.getElementById('senha').value;
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    const erroDiv = document.querySelector('.msg-erro');
    if (erroDiv) erroDiv.remove();

    if (!regex.test(senha)) {
        e.preventDefault();

        const msgErro = document.createElement('div');
        msgErro.classList.add('msg-erro');
        msgErro.textContent = "Senha fraca. Use no mínimo 8 caracteres com letras maiúsculas, minúsculas, números e caracteres especiais.";
        
        const form = document.getElementById('formCadastro');
        form.parentNode.insertBefore(msgErro, form);
        document.getElementById('senha').focus();
    }
});
</script>

</body>
</html>
