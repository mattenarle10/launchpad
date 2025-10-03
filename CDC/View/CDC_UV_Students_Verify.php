<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (!isset($_GET['id']))
    {
        die("No student selected.");
    }

    $id = intval($_GET['id']);
    $student = $admin->getStudentById($id);

    if (!$student)
    {
        die("Student not found.");
    }

    if (isset($_POST['verify']))
    {
        if ($admin->verifyStudent($id))
        {
            header("Location: CDC_UV_Students.php?msg=verified");
            exit();
        }
        
        else
        {
            echo "<script>alert('Verification failed. Please try again.'); window.history.back();</script>";
            exit();
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Verify Students </title>

        <link rel = "stylesheet" href = "CSS/Style10.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_UV_Students.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Verify Students </a></li>
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

                <section class = "Title">

                    <h1> Verify Student </h1>

                </section>

                <div class = "Details_Main">

                    <div class = "Details_Cont">

                        <div class = "Details1">

                            <p> <strong> Name: </strong> <?= $student['first_name'] . " " . $student['last_name'] ?> </p>
                            <p> <strong> ID Number: </strong> <?= htmlspecialchars($student['id_num']) ?> </p>
                            <p> <strong> Course: </strong> <?= $student['course'] ?> </p>
                            
                        </div>

                        <div class = "Details2">

                            <p> <strong> Email: </strong> <?= $student['email'] ?> </p>
                            <p> <strong> Contact Number: </strong> <?= $student['contact_num'] ?> </p>
                            <p> <strong> Company Name: </strong> <?= $student['company_name'] ?> </p>

                        </div>

                    </div>

                    <div class = "Details_Cont2">

                        <p> <strong> Certificate of Registration </strong> </p>

                        <br>

                        <?php if (!empty($student['cor'])): ?>
                            
                            <a href = "<?php echo htmlspecialchars($student['cor']); ?>" target = "_blank"> View </a>

                        <?php else: ?>

                            No COR uploaded

                        <?php endif; ?>

                        <form class = "Verify_Btn" method = "POST">

                            <button type = "submit" name = "verify" onclick = "return alert('Student Verified!');"> Verify </button>

                        </form>

                    </div>

                </div>
                
            </div>

        </div>

    </body>

</html>