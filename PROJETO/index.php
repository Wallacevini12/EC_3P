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
        include 'delete.php';  # Colocar link do DELETE

    } else {
        include 'main.php';
    }
} else {
    include 'main.php';
}

?>
