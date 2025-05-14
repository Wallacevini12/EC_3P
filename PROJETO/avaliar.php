<?php
include_once 'conecta_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nota = (int)$_POST['nota'];
    $resposta_id = (int)$_POST['resposta_id'];
    $aluno_id = (int)$_POST['aluno_id'];

    if ($nota >= 0 && $nota <= 5) {
        $oMysql = conecta_db();

        // Verifica se a avaliação já existe
        $stmt = $oMysql->prepare("SELECT id FROM avaliacoes WHERE aluno_id = ? AND resposta_id = ?");
        $stmt->bind_param("ii", $aluno_id, $resposta_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Insere nova avaliação
            $stmt = $oMysql->prepare("INSERT INTO avaliacoes (aluno_id, resposta_id, nota) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $aluno_id, $resposta_id, $nota);
            $stmt->execute();
        } else {
            // Atualiza avaliação existente
            $stmt = $oMysql->prepare("UPDATE avaliacoes SET nota = ?, data_avaliacao = NOW() WHERE aluno_id = ? AND resposta_id = ?");
            $stmt->bind_param("iii", $nota, $aluno_id, $resposta_id);
            $stmt->execute();
        }

        $stmt->close();
        $oMysql->close();
    }
}

header("Location: index.php"); // ou para a página de perguntas
exit;
