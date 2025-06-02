<?php
session_start();
include_once 'conecta_db.php'; // Inclui o arquivo de conexão com o banco
include_once 'header.php';     // Inclui o cabeçalho padrão

// Verifica se o usuário está logado e se é professor
if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'professor') {
    echo '<div class="container" style="margin-top: 70px;">
            <div class="alert alert-danger" role="alert">
                Você não testá logado como professor.
            </div>
          </div>';
    exit();
}

$oMysql = conecta_db();

if ($oMysql->connect_error) {
    die("Erro de conexão: " . $oMysql->connect_error);
}

$professor_id = $_SESSION['id']; // Obtém o ID do professor da sessão

// Buscar disciplinas vinculadas ao professor
$sql = "SELECT d.codigo_disciplina, d.nome_disciplina
        FROM disciplinas d
        JOIN professores_possuem_disciplinas p ON d.codigo_disciplina = p.disciplina_codigo
        WHERE p.professor_codigo = ?";
$stmt_disciplina = $oMysql->prepare($sql);
$stmt_disciplina->bind_param("i", $professor_id);
$stmt_disciplina->execute();
$result_disciplina = $stmt_disciplina->get_result();

$disciplinas = [];
if ($result_disciplina && $result_disciplina->num_rows > 0) {
    while ($row = $result_disciplina->fetch_assoc()) {
        $disciplinas[] = $row;
    }
}
$stmt_disciplina->close();

$mensagem_erro = '';
$mensagem_sucesso = false; // Agora booleano para indicar sucesso

// Função para validar senha forte
function senha_forte($senha) {
    // Pelo menos 8 caracteres, 1 maiúscula, 1 minúscula, 1 número e 1 caractere especial
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $senha);
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se todos os campos obrigatórios foram preenchidos
    if (
        isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['confirmar_senha'], $_POST['curso'], $_POST['disciplina'])
        && !empty($_POST['nome'])
        && !empty($_POST['email'])
        && !empty($_POST['senha'])
        && !empty($_POST['confirmar_senha'])
        && !empty($_POST['curso'])
        && !empty($_POST['disciplina'])
    ) {
        // Limpa os dados do formulário
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        $confirmar_senha = $_POST['confirmar_senha'];
        $curso = trim($_POST['curso']);
        $disciplina_codigo = (int)$_POST['disciplina'];

        // Validação da senha forte
        if (!senha_forte($senha)) {
            $mensagem_erro = "Senha fraca! A senha deve conter no mínimo 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais.";
        } elseif ($senha !== $confirmar_senha) {
            // Verificação se as senhas coincidem
            $mensagem_erro = "As senhas não coincidem. Por favor, tente novamente.";
        } else {
            // Verifica se o e-mail já existe no banco
            $stmt_verifica = $oMysql->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt_verifica->bind_param("s", $email);
            $stmt_verifica->execute();
            $stmt_verifica->store_result();

            if ($stmt_verifica->num_rows > 0) {
                $mensagem_erro = "Este e-mail já está cadastrado. Tente novamente com outro e-mail.";
            } else {
                // Gera o hash da senha para armazenamento seguro
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

                // Define tipo de usuário como monitor
                $tipo_usuario = 'monitor';

                // Prepara a inserção na tabela 'usuarios'
                $stmt = $oMysql->prepare("INSERT INTO usuarios (nome, email, senha, tipo_usuario, curso) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $mensagem_erro = "Erro na preparação da query: " . $oMysql->error;
                } else {
                    $stmt->bind_param("sssss", $nome, $email, $senha_hash, $tipo_usuario, $curso);
                    if ($stmt->execute()) {
                        // Recupera o ID do novo usuário
                        $usuario_id = $oMysql->insert_id;

                        // Insere na tabela 'monitor'
                        $stmt2 = $oMysql->prepare("INSERT INTO monitor (id) VALUES (?)");
                        if (!$stmt2) {
                            $mensagem_erro = "Erro na preparação da query monitor: " . $oMysql->error;
                        } else {
                            $stmt2->bind_param("i", $usuario_id);
                            if ($stmt2->execute()) {

                                // Relaciona o monitor à disciplina escolhida
                                $stmt3 = $oMysql->prepare("INSERT INTO monitores_possuem_disciplinas (disciplina_codigo, monitor_codigo) VALUES (?, ?)");
                                if (!$stmt3) {
                                    $mensagem_erro = "Erro na preparação da query monitores_possuem_disciplinas: " . $oMysql->error;
                                } else {
                                    $stmt3->bind_param("ii", $disciplina_codigo, $usuario_id);
                                    if ($stmt3->execute()) {
                                        $mensagem_sucesso = true; // sucesso confirmado
                                    } else {
                                        $mensagem_erro = "Erro ao vincular monitor à disciplina: " . $stmt3->error;
                                    }
                                    $stmt3->close();
                                }

                            } else {
                                $mensagem_erro = "Erro ao cadastrar monitor: " . $stmt2->error;
                            }
                            $stmt2->close();
                        }
                    } else {
                        $mensagem_erro = "Erro ao cadastrar usuário: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $stmt_verifica->close();
        }
    } else {
        $mensagem_erro = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <title>Cadastro de Monitor</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .mensagem-erro {
      color: red;
      margin-bottom: 15px;
      font-weight: bold;
    }
  </style>
</head>
<body>

<!-- Card centralizado com o formulário -->
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card shadow p-4" style="min-width: 400px; max-width: 500px; width: 100%;">

    <h2 class="mb-3">Cadastrar Monitor</h2>
    <p class="mb-4">Preencha os campos abaixo para cadastrar um monitor:</p> 

    <?php if ($mensagem_erro): ?>
      <div class="mensagem-erro"><?php echo htmlspecialchars($mensagem_erro); ?></div>
    <?php endif; ?>

    <form method="POST" action="">

      <label style="color: red;">*  = Campo obrigatório</label>
      <br>
      <br>

      <label for="nome" class="form-label">Nome <span style="color: red;">*</span></label>
      <input
        type="text"
        name="nome"
        class="form-control mb-2"
        placeholder="Nome"
        required
        value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>"
      />

      <label for="email" class="form-label">Email <span style="color: red;">*</span></label>
      <input
        type="email"
        name="email"
        class="form-control mb-2"
        placeholder="Email (ex: maria@monitor)"
        required
        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
      />

      <label for="senha" class="form-label">Senha <span style="color: red;">*</span></label>
      <input
        type="password"
        name="senha"
        class="form-control mb-2"
        placeholder="Senha"
        required
      />

      <!-- Campo de confirmação de senha adicionado -->
      <label for="confirmar_senha" class="form-label">Confirmar Senha <span style="color: red;">*</span></label>
      <input
        type="password"
        name="confirmar_senha"
        class="form-control mb-2"
        placeholder="Confirme a Senha"
        required
      />

      <!-- Campo curso -->
      <label for="curso" class="form-label">Curso <span style="color: red;">*</span></label>
      <select name="curso" class="form-select mb-2" required>
        <option value="" disabled <?php echo !isset($_POST['curso']) ? 'selected' : ''; ?>>Selecione seu curso</option>
        <?php 
        $cursos = [
          "Engenharia de Software",
          "Sistemas de Informação",
          "Análise e Desenvolvimento de Sistemas",
          "Ciência da Computação",
          "Redes de Computadores"
        ];
        foreach ($cursos as $curso_option): ?>
          <option value="<?php echo $curso_option; ?>"
            <?php echo (isset($_POST['curso']) && $_POST['curso'] === $curso_option) ? 'selected' : ''; ?>>
            <?php echo $curso_option; ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="disciplinas" class="form-label">Disciplinas</label><br>
      <select name="disciplina" class="form-select mb-3" required>
        <option value="" disabled <?php echo !isset($_POST['disciplina']) ? 'selected' : ''; ?>>Selecione sua disciplina</option>
        <?php foreach ($disciplinas as $disciplina): ?>
          <option value="<?php echo htmlspecialchars($disciplina['codigo_disciplina']); ?>"
            <?php echo (isset($_POST['disciplina']) && $_POST['disciplina'] == $disciplina['codigo_disciplina']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($disciplina['nome_disciplina']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <!-- Botão de envio -->
      <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
    </form>

  </div>
</div>

<!-- Alerta de sucesso e redirecionamento -->
<?php if ($mensagem_sucesso): ?>
<script>
  alert("Cadastro realizado com sucesso!");
  window.location.href = 'home_professor.php';
</script>
<?php endif; ?>

</body>
</html>
