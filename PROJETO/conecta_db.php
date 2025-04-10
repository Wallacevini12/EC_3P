<?php
    function conecta_db(){
       
        $DB_NAME = "learnhub_ep";
        $USER = "root";
        $PASS = "";
        $SERVER = "localhost";

        $CONEXAO = new mysqli($SERVER, $USER, $PASS, $DB_NAME);

        return $CONEXAO;
    }


?>