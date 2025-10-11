<?php

    session_start();
    
    require_once '../Config/Config.php';

    if (isset($_SESSION['admin_id']))
    {
        header("Location: CDC_Dashboard.php");
        exit();
    }

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST")
    {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $sql = "SELECT id, first_name, last_name, username, password FROM admins WHERE username = ?";
        if ($stmt = $conn->prepare($sql))
        {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && password_verify($password, $admin['password']))
            {
                session_regenerate_id(true);

                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['first_name'] . " " . $admin['last_name'];

                header("Location: CDC_Dashboard.php");
                exit();
            }

            else
            {
                $error = "<p class='error'>Invalid username or password!</p>";
            }

            $stmt->close();
        }

        else
        {
            $error = "<p class='error'>Something went wrong. Please try again later.</p>";
        }
    }

    $conn->close();

?>

<!DOCTYPE html>
<html lang = "en">

    <head>

        <meta charset="UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> CDC Login </title>

        <link rel = "stylesheet" href = "CSS/Style.css?v=<php echo time(); ?>">

    </head>

    <body>

        <div class = "Container">

            <img src = "Images/LP Logo.png" alt = "LP Logo">

        </div>

        <div class = "Container2">

            <img src = "Images/CDC Logo 3.png" alt = "CDC Logo">

            <h1> Career Development <br> Centre Portal </h1>

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

            </div>

        </div>

    </body>

</html>