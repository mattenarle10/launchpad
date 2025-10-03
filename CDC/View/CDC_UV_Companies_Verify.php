<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (!isset($_GET['id']))
    {
        die("No company selected.");
    }

    $id = intval($_GET['id']);
    $company = $admin->getCompanyById($id);

    if (!$company)
    {
        die("Company not found.");
    }

    if (isset($_POST['verify']))
    {
        $admin->verifyCompany($id);
        header("Location: CDC_UV_Companies.php");
        exit();
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
                    <li><a href = "CDC_UV_Students.php"> Verify Students </a></li>
                    <li><a href = "CDC_UV_Companies.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Verify Companies </a></li>
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

                    <h1> Verify Company </h1>

                </section>

                <div class = "Details_Main">

                    <div class = "Details_Cont">

                        <div class = "Details1">

                            <p> <strong> Company Name: </strong> <?= $company['name'] ?> </p>
                            <p> <strong> Username: </strong> <?= $company['username'] ?> </p>
                            <p> <strong> Website: </strong> <?= $company['website'] ?> </p>
                            
                        </div>

                        <div class = "Details2">

                            <p> <strong> Email: </strong> <?= $company['email'] ?> </p>
                            <p> <strong> Contact Number: </strong> <?= $company['contact_num'] ?> </p>
                            <p> <strong> Address: </strong> <?= $company['address'] ?> </p>

                        </div>

                    </div>

                    <div class = "Details_Cont2">

                        <p> <strong> Memorandum of Agreement </strong> </p>

                        <br>

                        <?php if (!empty($company['moa'])): ?>
                            
                            <a href = "<?php echo htmlspecialchars($company['moa']); ?>" target = "_blank"> View </a>

                        <?php else: ?>

                            No MOA uploaded

                        <?php endif; ?>

                        <br><br><br>

                        <p> <strong> Company ID </strong> </p>

                        <br>

                        <?php if (!empty($company['id_img'])): ?>
                            
                            <a href = "<?php echo htmlspecialchars($company['id_img']); ?>" target = "_blank"> View </a>

                        <?php else: ?>

                            No ID Image uploaded

                        <?php endif; ?>

                        <form class = "Verify_Btn" method = "POST">

                            <button type = "submit" name = "verify" onclick = "return alert('Company Verified!');"> Verify </button>

                        </form>

                    </div>

                </div>
                
            </div>

        </div>

    </body>

</html>