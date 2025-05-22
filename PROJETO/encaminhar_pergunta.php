<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'monitor') {
    header("Location: login.php");
    exit;
}

include_once 'conecta_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_pergunta'])) {
    $codigo_pergunta = intval($_POST['codigo_pergunta']);

    $oMysql = conecta_db();
    if ($oMysql->connect_error) {
        die("Erro de conexão: " . $oMysql->connect_error);
    }

    // 1. Buscar disciplina da pergunta
    $sql_disciplina = "SELECT disciplina_codigo FROM perguntas WHERE codigo_pergunta = $codigo_pergunta";
    $res_disciplina = $oMysql->query($sql_disciplina);

    if ($res_disciplina && $res_disciplina->num_rows > 0) {
        $row = $res_disciplina->fetch_assoc();
        $disciplina_codigo = intval($row['disciplina_codigo']);

        // 2. Buscar professores que ministram essa disciplina
        $sql_professores = "
            SELECT professor_codigo 
            FROM professores_possuem_disciplinas 
            WHERE disciplina_codigo = $disciplina_codigo
        ";
        $res_professores = $oMysql->query($sql_professores);

        if ($res_professores && $res_professores->num_rows > 0) {
            while ($prof = $res_professores->fetch_assoc()) {
                $id_prof = intval($prof['professor_codigo']);

                // 3. Inserir encaminhamento
                $oMysql->query("
                    INSERT INTO perguntas_encaminhadas (pergunta_codigo, professor_codigo, data_envio)
                    VALUES ($codigo_pergunta, $id_prof, NOW())
                ");
            }

            // 4. Marcar pergunta como encaminhada
            $sql_update = "UPDATE perguntas SET encaminhada = 1 WHERE codigo_pergunta = $codigo_pergunta";
            $oMysql->query($sql_update);

            header("Location: perguntas_monitor.php?msg=Pergunta encaminhada com sucesso");
            exit;
        } else {
            // Nenhum professor para essa disciplina
            header("Location: perguntas_monitor.php?msg=Nenhum professor ministra essa disciplina");
            exit;
        }
    } else {
        header("Location: perguntas_monitor.php?msg=Disciplina não encontrada para essa pergunta");
        exit;
    }

    $oMysql->close();
} else {
    header("Location: perguntas_monitor.php");
    exit;
}
