<?php

    session_start();

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (isset($_GET['id']))
    {
        $id = intval($_GET['id']);

        if ($admin->deleteUnverifiedStudent($id))
        {
            header("Location: CDC_UV_Students.php?msg=deleted");
            exit();
        }
        
        else
        {
            echo "Error deleting student.";
        }
    }
    
    else
    {
        echo "Invalid request.";
    }

?>