<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (isset($_POST['send_notif']))
    {
        $recipient   = trim($_POST['recipient']);
        $title       = trim($_POST['title']);
        $description = trim($_POST['description']);
        $date_sent   = !empty($_POST['date_sent']) ? $_POST['date_sent'] : null;
        $deadline    = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

        $result = $admin->sendNotification($recipient, $title, $description, $date_sent, $deadline);

        if ($result === true)
        {
            echo "<script>alert('Notification sent successfully!'); window.location.href='CDC_Notifications.php';</script>";
            exit();
        }
        
        elseif ($result === "student_not_found")
        {
            echo "<script>alert('Student not found! Please check the ID number.');</script>";
        }
        
        else
        {
            echo "<script>alert('Failed to send notification. Try again.');</script>";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Send Notifications </title>

        <link rel = "stylesheet" href = "CSS/Style5.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_Notifications.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Send Notification </a></li>
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

                    <h1> Send Notification </h1>

                </section>

                <section>

                    <div class = "Form_Cont">

                        <form id = "Notif_Form" method = "POST" enctype = "multipart/form-data">

                            <div class = "Form1">

                                <label> Recipient </label>
                                <input type = "text" name = "recipient" placeholder = "Student's ID Number" required>

                            </div>

                            <div class = "Form_Grp">

                                <div class = "Form2">

                                    <label for="subject"> Title </label>
                                    <input type = "text" name = "title" placeholder = "Title" required>

                                </div>

                                <div class = "Form3">

                                    <label> Deadline Date </label>
                                    <input type = "date" name = "deadline" required>

                                </div>

                            </div>

                            <div class = "Form4">

                                <label for="message"> Description </label>
                                <textarea name = "description" placeholder = "Description" required></textarea>
                                
                            </div>
                            
                            <button type = "submit" class = "Submit_Btn" name = "send_notif"> Submit </button>

                        </form>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>