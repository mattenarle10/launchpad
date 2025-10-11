<?php

    session_start();
    include '../Config/Config.php';

    if (isset($_SESSION['company_id']))
    {
        header("Location: PC_Dashboard.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM verified_companies WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $company = $result->fetch_assoc();

        if ($company && password_verify($password, $company['password']))
        {
            $_SESSION['company_id'] = $company['company_id'];
            $_SESSION['name'] = $company['name'];
            $_SESSION['username'] = $company['username'];

            header("Location: PC_Dashboard.php");
            exit();
        }
        
        else
        {
            $error = "<p class='error'>Invalid username or password!</p>";
        }
    }
?>


<!DOCTYPE html>
<html lang = "en">

    <head>

        <meta charset="UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> PC Login </title>

        <link rel = "stylesheet" href = "CSS/Style.css?v=<php echo time(); ?>">

    </head>

    <body>

        <div class = "Container">

            <img src = "Images/LP Logo.png" alt = "LP Logo">

        </div>

        <div class = "Container2">

            <h1> Partner Company Portal </h1>

        </div>

        <div class = "Login_Form">

            <div class = "Box">

                <form action = "" method = "POST">

                    <h2> Login </h2>

                    <?php if (!empty($error)) echo $error; ?>

                    <label for = "username"> Username </label>
                    <input type = "text" name = "username" placeholder = "Username" required>

                    <label for = "password"> Password </label>
                    <input type = "password" id = "password" name = "password" placeholder = "Password" required>

                    <button type = "submit" onclick = "window.location.href = 'CDC_Dashboard.php'"> Login </button>

                </form>

                <a href = "PC_SignUp.php"> Sign Up </a>

            </div>

        </div>

    </body>

</html>