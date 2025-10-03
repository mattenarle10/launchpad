<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);
    $company_id = $_SESSION['company_id'];

    if (!isset($_GET['id']) || !is_numeric($_GET['id']))
    {
        header("Location: PC_JobPostings.php");
        exit();
    }

    $job_id = intval($_GET['id']);

    if ($company->deleteJob($job_id, $company_id))
    {
        echo "<script>alert('Job posting deleted successfully!'); window.location='PC_JobPostings.php';</script>";
        exit();
    }
    
    else
    {
        echo "<script>alert('Failed to delete job posting or you do not have permission.'); window.location='PC_JobPostings.php';</script>";
        exit();
    }

?>
