<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];

    $adminDetails = $admin->getAdminDetails($admin_id);
    $success = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST")
    {
        $first_name = trim($_POST['first_name']);
        $last_name  = trim($_POST['last_name']);
        $username   = trim($_POST['username']);
        $password   = trim($_POST['password']);
        $profile_pic = null;

        if (!empty($_FILES['profile_pic']['name']))
        {
            $target_dir = "../../Uploads/ProfilePics/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file))
            {
                $profile_pic = basename($_FILES['profile_pic']['name']);
            }

            else
            {
                $error = "Failed to upload profile picture.";
            }
        }

        if ($admin->updateAdmin($admin_id, $first_name, $last_name, $username, $password ?: null, $profile_pic))
        {
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_name'] = $first_name . " " . $last_name;

            echo "<script>alert('Profile updated successfully!'); window.location.href='CDC_Profile.php';</script>";
            exit();
        }
        
        else
        {
            echo "<script>alert('Error updating profile.'); window.location.href='CDC_Profile.php';</script>";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> CDC Dashboard </title>

        <link rel = "stylesheet" href = "CSS/Style14.css?v=<php echo time(); ?>">

        <script src = "JS/Script.js"></script>

    </head>

    <body>

        <div class = "Body">

            <nav class = "Sidebar">

                <div class = "Logo">

                    <img src = "Images/LP Logo.png" alt = "LaunchPad Logo">

                </div>

                <ul>

                    <li><a href = "CDC_Dashboard.php"> Dashboard </a></li>
                    <li><a href = "CDC_Students.php"> Students </a></li>
                    <li><a href = "CDC_Companies.php"> Companies </a></li>
                    <li><a href = "CDC_UV_Students.php"> Verify Students </a></li>
                    <li><a href = "CDC_UV_Companies.php"> Verify Companies </a></li>
                    <li><a href = "CDC_Notifications.php"> Send Notification </a></li>
                    <li><a href = "CDC_Reports.php"> Submission Reports </a></li>
                    <li><a href = "CDC_OJTHours.php"> Students' OJT Hours </a></li>
                    <li><a href = "CDC_JobPostings.php"> Job Postings </a></li>
                    
                </ul>

            </nav>

            <div class = "Main_Content">

                <header>
                    
                    <div class = "Profile">

                        <button class = "Profile_Btn" onclick="toggleDropdown()">

                            <div class = "Profile_Btn_Logo" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($adminDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position:"> </div>
                            
                        </button>

                        <div class = "Drop_Menu" id = "profileDropdown">

                            <div class = "Profile_Logo" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($adminDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position: center;"> </div>

                            <p> <?php echo htmlspecialchars($adminDetails['first_name']); ?> <?php echo htmlspecialchars($adminDetails['last_name']); ?></p>
                            
                            <a href = "CDC_Profile.php"> Profile </a>
                            <br>
                            <a href = "CDC_Logout.php"> Logout </a>

                        </div>

                    </div>

                </header>

                <section class = "Title">

                    <h1> Edit Profile </h1>

                </section>

                <div class = "Form_Cont">

                    <form method = "POST" enctype = "multipart/form-data">

                        <div class = "Form1">

                            <label> Profile Picture </label>
                            <input type = "file" name = "profile_pic" accept = "image/*">

                        </div>

                        <div class = "Form2">

                            <label> First Name </label>
                            <input type = "text" name = "first_name" value = "<?php echo htmlspecialchars($adminDetails['first_name']); ?>">

                        </div>

                        <div class = "Form3">

                            <label> Last Name </label>
                            <input type = "text" name = "last_name" value = "<?php echo htmlspecialchars($adminDetails['last_name']); ?>">

                        </div>

                        <div class = "Form4">

                            <label> Username </label>
                            <input type = "text" name = "username" value = "<?php echo htmlspecialchars($adminDetails['username']); ?>">

                        </div>

                        <div class = "Form5">

                            <label> Password </label>
                            <input type = "password" name = "password" value = "">

                        </div>
                        
                        <button type = "submit" class = "Submit_Btn"> Save </button>

                    </form>

                </div>
                
            </div>

        </div>

    </body>

</html>