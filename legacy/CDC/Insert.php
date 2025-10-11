<?php

    require_once 'Config/Config.php';

    $first_name = "Anthony";
    $last_name = "Gallego";
    $username = "admin";
    $password = "12345";

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (first_name, last_name, username, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt)
    {
        $stmt->bind_param("ssss", $first_name, $last_name, $username, $hashed_password);
        if ($stmt->execute())
        {
            echo "Admin inserted successfully!";
        }

        else
        {
            echo "Error inserting admin: " . $stmt->error;
        }
        $stmt->close();
    }

    else
    {
        echo "SQL Error: " . $conn->error;
    }

    $conn->close();

?>