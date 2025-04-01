<?php

if (isset($_GET['id'])) { 
    $OMYSQL = conecta_db();
    $Query = "DELETE FROM usuario WHERE id = ".$_GET['id'];

    $Resultado = $OMYSQL->query($Query);
    header("Location: index.php"); // Correção na sintaxe do header

}

?>
