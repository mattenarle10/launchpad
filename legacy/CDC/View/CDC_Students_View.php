<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    $student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($student_id > 0)
    {
        $student = $admin->getStudentDetails($student_id);
    }
    
    else
    {
        $student = null;
    }
    
?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> CDC Student View </title>

        <link rel = "stylesheet" href = "CSS/Style15.css?v=<php echo time(); ?>">

        <script src = "JS/Script.js"></script>
        <script src = "https://cdn.jsdelivr.net/npm/chart.js"></script>

    </head>

    <body>

        <div class = "Body">

            <nav class = "Sidebar">

                <div class = "Logo">

                    <img src = "Images/LP Logo.png" alt = "LaunchPad Logo">

                </div>

                <ul>

                    <li><a href = "CDC_Dashboard.php"> Dashboard </a></li>
                    <li><a href = "CDC_Students.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Students </a></li>
                    <li><a href = "CDC_Companies.php"> Companies </a></li>
                    <li><a href = "CDC_UV_Students.php"> Verify Students </a></li>
                    <li><a href = "CDC_UV_Companies.php"> Verify Companies </a></li>
                    <li><a href = "CDC_Notifications.php"> Send Notification </a></li>
                    <li><a href = "CDC_PhilSMS.php"> PhilSMS </a></li>
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

                        <div class = "Profile_Pic" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($student['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position:"> </div>

                        <div class = "Admin_Name">
                            
                            <h1> <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?> </h1>
                            <p> <?= htmlspecialchars($student['id_num']); ?> </p>

                        </div>

                    </div>     

                </section>

                <div class = "Details_Main">

                    <div class = "Details_Cont">

                        <div class = "Details1">

                            <p> <strong> Name: </strong> <?= $student['first_name'] . " " . $student['last_name'] ?> </p>
                            <p> <strong> Course: </strong> <?= $student['course'] ?> </p>
                            
                        </div>

                        <div class = "Details2">

                            <p> <strong> Email: </strong> <?= $student['email'] ?> </p>
                            <p> <strong> Contact Number: </strong> <?= $student['contact_num'] ?> </p>
                            <p><strong> Company: </strong> <?= htmlspecialchars($student['company_name'] ?? 'No Company Assigned'); ?></p>

                        </div>

                    </div>

                    <div class = "Details_Cont2">

                        <p> <strong> Certificate of Registration: </strong> </p>

                        <br>
                        
                        <?php if (!empty($student['cor'])): ?>

                            <a href = "<?= htmlspecialchars($student['cor']); ?>" target="_blank"> View </a>

                        <?php else: ?>

                            <p> No COR uploaded </p>

                        <?php endif; ?>

                    </div>

                </div>
                
            </div>

        </div>

    </body>

</html>