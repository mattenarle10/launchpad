<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $company->createJobOpportunity(
            $company_id,
            $_SESSION['job_title'],
            $_SESSION['job_location'],
            $_SESSION['job_setup'],
            $_SESSION['job_tags'],
            $_SESSION['job_pay_min'],
            $_SESSION['job_pay_max'],
            $_SESSION['job_requirements'],
            $_SESSION['job_responsibilities']
        );

        unset($_SESSION['job_title'], $_SESSION['job_location'], $_SESSION['job_setup'],
            $_SESSION['job_tags'], $_SESSION['job_pay_min'], $_SESSION['job_pay_max'],
            $_SESSION['job_requirements'], $_SESSION['job_responsibilities']);

        echo "<script>alert('Job opportunity posted successfully!'); window.location.href='PC_JobPostings.php';</script>";
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Job Opportunities </title>

        <link rel = "stylesheet" href = "CSS/Style9.css?v=<php echo time(); ?>">

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

                    <li><a href = "PC_Dashboard.php"> Dashboard </a></li>
                    <li><a href = "PC_Students.php"> Students </a></li>
                    <li><a href = "PC_JobPostings.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Job Opportunities </a></li>
                    
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

                <section class = "Title">

                    <h1> Review Job Opportunity </h1>

                </section>

                <section>

                    <div class = "Form_Cont5">

                        <div class = "Review_Cont">

                            <div class = "Review1">

                                <p><strong> Title </strong> <br> <?= htmlspecialchars($_SESSION['job_title']) ?></p>
                                <br>
                                <p><strong> Location </strong> <br> <?= htmlspecialchars($_SESSION['job_location']) ?></p>
                                <br>
                                <p><strong> Setup </strong> <br> <?= htmlspecialchars($_SESSION['job_setup']) ?></p>
                                <br>
                                <p><strong> Tags </strong> <br> <?= htmlspecialchars($_SESSION['job_tags']) ?></p>

                            </div>  

                            <div class = "Review2">

                                <p><strong> Pay </strong> <br> ₱<?= number_format($_SESSION['job_pay_min']) ?> - ₱<?= number_format($_SESSION['job_pay_max']) ?></p>
                                <br>

                                <strong> Requirements </strong>

                                <br>

                                <textarea readonly><?= nl2br(htmlspecialchars($_SESSION['job_requirements'])) ?></textarea>

                                <br><br>

                                <strong> Responsibilities </strong>
                                
                                <br>

                                <textarea readonly><?= nl2br(htmlspecialchars($_SESSION['job_responsibilities'])) ?></textarea>

                            </div>

                        </div>

                        <form method = "POST">

                            <button type = "submit" class = "Submit_Btn5"> Submit </button>

                        </form>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>