<?php

    session_start();
    session_destroy();

    header("Location: PC_Login.php");
    exit();

?>