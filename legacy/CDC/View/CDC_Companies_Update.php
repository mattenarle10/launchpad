<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (!isset($_GET['id']))
    {
        die("No company ID provided.");
    }

    $id = intval($_GET['id']);
    $company = $admin->getVerifiedCompanyById($id);

    if (!$company)
    {
        die("Company not found.");
    }

    if (isset($_POST['update']))
    {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $contact_num = trim($_POST['contact_num']);
        $address = trim($_POST['address']);
        $website = trim($_POST['website']);
        $password = trim($_POST['password']);

        $admin->updateVerifiedCompany($id, $name, $username, $email, $contact_num, $address, $website, $password);

        echo "<script>alert('Company updated successfully!'); window.location.href='CDC_Companies.php';</script>";
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Update Company </title>

        <link rel = "stylesheet" href = "CSS/Style11.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_Companies.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Companies </a></li>
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

                <section class = "Title">

                    <h1> Update Company </h1>

                </section>

                <div class = "Data_Cont">

                    <form method = "POST">

                        <div class = "Datas">

                            <div class = "Data1">

                                <label> Name </label>
                                <input type = "text" name = "name" value = "<?php echo htmlspecialchars($company['name']); ?>">

                                <label> Username </label>
                                <input type = "text" name = "username" value = "<?php echo htmlspecialchars($company['username']); ?>">

                                <label> Password </label>
                                <input type = "text" name = "password" value = "<?php echo htmlspecialchars($company['password']); ?>">

                                <label> Email </label>
                                <input type = "text" name = "email" value = "<?php echo htmlspecialchars($company['email']); ?>">

                            </div>

                            <div class = "Data2">

                                <label> Contact Number </label>
                                <input type = "text" name = "contact_num" value = "<?php echo htmlspecialchars($company['contact_num']); ?>">

                                <label> Address </label>
                                <input type = "text" name = "address" value = "<?php echo htmlspecialchars($company['address']); ?>">

                                <label> Website </label>
                                <input type = "text" name = "website" value = "<?php echo htmlspecialchars($company['website']); ?>">

                            </div>

                        </div>

                        <button type = "submit" name = "update"> Update </button>
                            
                    </form>

                </div>
                
            </div>

        </div>

    </body>

</html>