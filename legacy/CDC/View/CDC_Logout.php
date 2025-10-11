<?php

    session_start();
    session_destroy();
    
    header("Location: ../View/CDC_Login.php");
    exit();

?>