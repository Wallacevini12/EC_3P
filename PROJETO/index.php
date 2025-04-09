<?php

include "header.php";
include "conecta_db.php";
include "biblioteca.php";


if (isset($_GET['page'])) {
    if ($_GET['page'] == 1) {
        include 'cadastro.php';  # Colocar link do INSERT

    } elseif ($_GET['page'] == 2) {
        include 'update.php';  # Colocar link do UPDATE

    } elseif ($_GET['page'] == 3) {
        include 'cadastro_monitor.php';  # Colocar link do DELETE

    } else {
        include 'index.html';
    }
} else {
    include 'index.html';
}

?>
