<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    $id = $_GET['id'];
    
    $student = $admin->getStudentOJTById($id);

    if (isset($_POST['update']))
    {
        $add = intval($_POST['add_hours']);
        $deduct = intval($_POST['deduct_hours']);

        $done = $student['done_hours'] + $add - $deduct;
        if ($done < 0) $done = 0;

        $required = $student['required_hours'];
        if ($done > $required) $done = $required;

        $admin->updateOJTProgress($id, $done);

        echo "<script>alert('OJT hours updated successfully!'); window.location.href='CDC_OJTHours.php';</script>";
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Update OJT Hours </title>

        <link rel = "stylesheet" href = "CSS/Style12.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_PhilSMS.php"> PhilSMS </a></li>
                    <li><a href = "CDC_Reports.php"> Submission Reports </a></li>
                    <li><a href = "CDC_OJTHours.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Students' OJT Hours </a></li>
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

                    <h1> Update OJT Hours </h1>

                </section>

                <div class = "Details_Main">

                    <div class = "Details_Cont">

                        <p> <strong> Name: </strong> <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?> </p>
                        <p> <strong> ID Number: </strong> <?= htmlspecialchars($student['id_num']); ?> </p>
                        <p> <strong> Course: </strong> <?= htmlspecialchars($student['course']); ?> </p>
                        <p> <strong> Company: </strong> <?= htmlspecialchars($student['company'] ?? "Not Assigned"); ?> </p>

                    </div>

                    <div class = "Details_Cont2">

                        <p> <strong> Completed Hours: </strong> <?= $student['done_hours'] . "/" . $student['required_hours']; ?> </p>

                        <form class = "Hours_Cont" method = "POST">

                            <label> Add Hours: </label>
                            <input type = "number" name = "add_hours" value = "0" min = "0">

                            <br>

                            <label> Deduct Hours: </label>
                            <input type = "number" name = "deduct_hours" value = "0" min = "0">

                            <br>

                            <button type = "submit" name = "update"> Update </button>

                        </form>

                    </div>

                </div>
                
            </div>

        </div>

    </body>

</html>