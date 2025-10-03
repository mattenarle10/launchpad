<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (!isset($_GET['id']))
    {
        die("No report selected.");
    }

    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT report_file FROM submission_reports WHERE report_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0)
    {
        die("Report not found.");
    }

    $report = $result->fetch_assoc();
    $filePath = "../Uploads/Reports/" . $report['report_file'];

    $delete = $conn->prepare("DELETE FROM submission_reports WHERE report_id = ?");
    $delete->bind_param("i", $id);
    if ($delete->execute())
    {
        if (file_exists($filePath))
        {
            unlink($filePath);
        }
        header("Location: CDC_Reports.php?msg=deleted");
        exit();
    }
    
    else
    {
        echo "Error deleting report.";
    }

?>
