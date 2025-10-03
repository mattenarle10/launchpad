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

    $sql = "SELECT sr.report_file, vs.first_name, vs.last_name FROM submission_reports sr INNER JOIN verified_students vs ON sr.student_id = vs.student_id WHERE sr.report_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0)
    {
        die("Report not found.");
    }

    $report = $result->fetch_assoc();

    $filePath = "../Uploads/Reports" . $report['report_file'];
    if (!file_exists($filePath))
    {
        die("File not found on server.");
    }

    header("Content-Disposition: inline; filename=" . basename($filePath));
    header("Content-Type: application/pdf");
    readfile($filePath);
    exit;

?>
