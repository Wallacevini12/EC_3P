<?php
    function conecta_db(){
       
        $DB_NAME = "learnhub_ep";
        $USER = "root";
        $PASS = "PUC@1234";
        $SERVER = "localhost";

        $CONEXAO = new mysqli($SERVER, $USER, $PASS, $DB_NAME);

        return $CONEXAO;
    }


?>