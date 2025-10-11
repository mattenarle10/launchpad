<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Company Profile </title>

        <link rel = "stylesheet" href = "CSS/Style4.css?v=<php echo time(); ?>">

        <script src = "JS/Script.js"></script>

    </head>

    <body>

        <div class = "Body">

            <nav class = "Sidebar">

                <div class = "Logo">

                    <img src = "Images/LP Logo.png" alt = "LaunchPad Logo">

                </div>

                <ul>

                    <li><a href = "PC_Dashboard.php"> Dashboard </a></li>
                    <li><a href = "PC_Students.php"> Students </a></li>
                    <li><a href = "PC_JobPostings.php"> Job Opportunities </a></li>
                    
                </ul>

            </nav>

            <div class = "Main_Content">

                <header>
                    
                    <div class = "Profile">

                        <button class = "Profile_Btn" onclick="toggleDropdown()">

                            <div class = "Profile_Btn_Logo" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($companyDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position: center;"> </div>
                            
                        </button>

                        <div class = "Drop_Menu" id = "profileDropdown">

                            <div class = "Profile_Logo" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($companyDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position: center;"> </div>

                            <p> <?php echo htmlspecialchars($companyDetails['name']); ?> </p>
                            
                            <a href = "PC_Profile.php"> Profile </a>
                            <br>
                            <a href = "PC_Logout.php"> Logout </a>

                        </div>

                    </div>

                </header>

                <section class = "DB_Header">

                    <div class = "Header_Text">

                        <div class = "Profile_Pic" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($companyDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position: center;"> </div>

                        <div class = "Admin_Name">
                            
                            <h1> <?php echo htmlspecialchars($companyDetails['name']); ?> </h1>
                            <h3> Admin </h3>
                            <p> Career Development <br> Centre Portal </p>

                        </div>

                    </div>

                    <div class = "Edit_Btn">

                        <a href = "PC_Profile_Edit.php"> Edit </a>

                    </div>

                </section>

                <section class = "Cont">

                    <div class = "Details">

                        <p> <strong> Username: </strong> <?= $companyDetails['username'] ?> </p>
                        <p> <strong> Password: </strong> <?= str_repeat("•", 5) ?> </p>
                        <p> <strong> Email: </strong> <?= $companyDetails['email'] ?> </p>

                        <br>
                        
                        <p> <strong> Contact Number: </strong> <?= $companyDetails['contact_num'] ?> </p>
                        <p> <strong> Website: </strong> <?= $companyDetails['website'] ?> </p>
                        <p> <strong> Address: </strong> <?= $companyDetails['address'] ?> </p>

                    </div>

                    <div class = "Details2">

                        <p> <strong> Memorandum of Agreement: </strong> </p>

                        <br>

                        <?php if (!empty($companyDetails['moa'])): ?>
                            
                            <a href = "<?php echo htmlspecialchars($companyDetails['moa']); ?>" target = "_blank"> View </a>

                        <?php else: ?>

                            No MOA uploaded

                        <?php endif; ?>

                        <br><br><br>

                        <p> <strong> Company ID: </strong> </p>

                        <br>

                        <?php if (!empty($companyDetails['id_img'])): ?>
                            
                            <a href = "<?php echo htmlspecialchars($companyDetails['id_img']); ?>" target = "_blank"> View </a>

                        <?php else: ?>

                            No MOA uploaded

                        <?php endif; ?>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>