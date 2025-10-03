<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if (!isset($_POST['student_id']) || !isset($_FILES['report']))
        {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing data"]);
            exit;
        }

        $student_id = intval($_POST['student_id']);
        $file = $_FILES['report'];


        $uploadDir = "../Uploads/Reports/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // create safe file name
        $fileName = time() . "_" . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $stmt = $conn->prepare("INSERT INTO submission_reports (student_id, report_file) VALUES (?, ?)");
            $stmt->bind_param("is", $student_id, $fileName);
            $stmt->execute();

            echo json_encode(["status" => "success", "file" => $fileName]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "File upload failed"]);
        }
    }

?>