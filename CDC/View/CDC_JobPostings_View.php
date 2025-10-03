<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (!isset($_GET['id']))
    {
        die("No job selected.");
    }

    $job_id = intval($_GET['id']);
    $job = $admin->getJobById($job_id);

    if (!$job)
    {
        die("Job not found.");
    }

    // Format Peso
    function formatPeso($amount)
    {
        if ($amount >= 1000)
        {
            return "₱" . round($amount / 1000, 1) . "k"; 
        }
        return "₱" . $amount;
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> View Job Opportunity </title>

        <link rel = "stylesheet" href = "CSS/Style17.css?v=<php echo time(); ?>">

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
                    <li><a href = "CDC_Students.php"> Students </a></li>
                    <li><a href = "CDC_Companies.php"> Companies </a></li>
                    <li><a href = "CDC_UV_Students.php"> Verify Students </a></li>
                    <li><a href = "CDC_UV_Companies.php"> Verify Companies </a></li>
                    <li><a href = "CDC_Notifications.php"> Send Notification </a></li>
                    <li><a href = "CDC_PhilSMS.php"> PhilSMS </a></li>
                    <li><a href = "CDC_Reports.php"> Submission Reports </a></li>
                    <li><a href = "CDC_OJTHours.php"> Students' OJT Hours </a></li>
                    <li><a href = "CDC_JobPostings.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Job Postings </a></li>
                    
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

                    <h1> View Job Opportunity </h1>

                </section>

                <section>

                    <div class = "Form_Cont5">

                        <div class = "Review_Cont">

                            <div class = "Review1">

                                <p><strong> Title </strong> <br> <?= htmlspecialchars($job['job_title']) ?></p>
                                <br>
                                <p><strong> Location </strong> <br> <?= htmlspecialchars($job['job_location']) ?></p>
                                <br>
                                <p><strong> Setup </strong> <br> <?= htmlspecialchars($job['job_setup']) ?></p>
                                <br>
                                <p><strong> Tags </strong> <br> <?= htmlspecialchars($job['job_tags']) ?></p>

                            </div>  

                            <div class = "Review2">

                                <p><strong> Pay </strong> <br> ₱<?= number_format($job['job_pay_min']) ?> - ₱<?= number_format($job['job_pay_max']) ?></p>
                                <br>

                                <strong> Requirements </strong>

                                <br>

                                <textarea readonly><?= nl2br(htmlspecialchars($job['job_requirements'])) ?></textarea>

                                <br><br>

                                <strong> Responsibilities </strong>
                                
                                <br>

                                <textarea readonly><?= nl2br(htmlspecialchars($job['job_responsibilities'])) ?></textarea>

                            </div>

                        </div>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>