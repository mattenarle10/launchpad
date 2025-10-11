<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $id_num = $_POST['id_num'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $course = $_POST['course'];
        $email = $_POST['email'];
        $contact_num = $_POST['contact_num'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT student_id FROM unverified_students WHERE id_num = ? UNION SELECT student_id FROM verified_students WHERE id_num = ?");
        $check->bind_param("ss", $id_num, $id_num);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0)
        {
            echo json_encode(["status" => "error", "message" => "ID number already registered."]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO unverified_students (id_num, first_name, last_name, course, email, contact_num, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $id_num, $first_name, $last_name, $course, $email, $contact_num, $password);

        if ($stmt->execute())
        {
            echo json_encode(["status" => "success", "message" => "Registration successful. Awaiting verification."]);
        }
        
        else
        {
            echo json_encode(["status" => "error", "message" => "Registration failed."]);
        }
    }

?>