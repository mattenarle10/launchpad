<?php

    session_start();

    if (!isset($_SESSION['company_id']))
    {
        header("Location: ../View/PC_Login.php");
        exit();
    }

?>