<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> CDC Dashboard </title>

        <link rel = "stylesheet" href = "CSS/Style13.css?v=<php echo time(); ?>">

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

                <section class = "DB_Header">

                    <div class = "Header_Text">

                        <div class = "Profile_Pic" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($adminDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position: center;"> </div>

                        <div class = "Admin_Name">
                            
                            <h1> <?php echo htmlspecialchars($adminDetails['first_name']); ?> <?php echo htmlspecialchars($adminDetails['last_name']); ?></h1>
                            <h3> Admin </h3>
                            <p> Career Development <br> Centre Portal </p>

                        </div>

                    </div>

                </section>

                <section>

                    <div class = "Details">

                        <p> <strong> Username: </strong> <?= $adminDetails['username'] ?> </p>

                        <p> <strong> Password: </strong> <?= str_repeat("•", 5) ?> </p>

                    </div>

                    <div class = "Edit_Btn">

                        <a href = "CDC_Profile_Edit.php"> Edit </a>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>