<?php

    session_start();

    include '../Config/Config.php';
    include '../Model/PC.php';

    $pc = new PC($conn);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register']))
    {
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
        $website = trim($_POST['website']);
        $email = trim($_POST['email']);
        $contact_num = trim($_POST['contact_num']);
        $address = trim($_POST['address']);
        $password = $_POST['password'];

        $uploadDir = "../../Uploads/PC_Verification/";

        if (!is_dir($uploadDir))
        {
            mkdir($uploadDir, 0777, true);
        }

        $moaName = uniqid() . "_" . basename($_FILES['moa']['name']);
        $moaPath = $uploadDir . $moaName;
        move_uploaded_file($_FILES['moa']['tmp_name'], $moaPath);

        $idName = uniqid() . "_" . basename($_FILES['id_img']['name']);
        $idPath = $uploadDir . $idName;
        move_uploaded_file($_FILES['id_img']['tmp_name'], $idPath);

        if ($pc->registerCompany($username, $name, $website, $email, $contact_num, $address, $password, $moaPath, $idPath))
        {
            echo "<script>alert('Registration successful! Please wait for CDC verification.'); window.location.href='PC_Login.php';</script>";
        }
        
        else
        {
            echo "<script>alert('Error: Could not register company.');</script>";
        }
    }

?>

<!DOCTYPE html>
<html lang = "en">

    <head>

        <meta charset="UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> PC Login </title>

        <link rel = "stylesheet" href = "CSS/Style2.css?v=<php echo time(); ?>">

    </head>

    <body>

        <div class = "Container">

            <img src = "Images/LP Logo.png" alt = "LP Logo">

        </div>

        <div class = "Container2">

            <h1> Partner Company Portal </h1>

        </div>

        <div class = "Signup_Cont">

            <form action = "PC_SignUp.php" method = "POST" enctype = "multipart/form-data">

                <div class = "Signup_Form">

                    <div class = "Signup_Form1">

                        <h2> Sign Up </h2>
            
                        <label> Username </label>
                        <input type = "text" name = "username" required>

                        <label> Password </label>
                        <input type = "password" name = "password" required>

                        <label> Company Name </label>
                        <input type = "text" name = "name" required>

                        <label> Company Website </label>
                        <input type = "text" name = "website">

                        <label> Email </label>
                        <input type = "email" name = "email" required>

                    </div>

                    <div class = "Signup_Form2">

                        <h2> Sign Up </h2>

                        <label> Contact Number </label>
                        <input type = "text" name = "contact_num" required>

                        <label> Address </label>
                        <textarea name = "address" required></textarea>

                        <label> Memorandum of Agreement (PDF) </label>
                        <input type = "file" name = "moa" accept = "application/pdf" required>

                        <label> Company ID (Image) </label>
                        <input type = "file" name = "id_img" accept = "image/*" required>

                        <button type = "submit" name = "register"> Sign Up </button>

                    </div>

                </div>

            </form>

        </div>

    </body>

</html>