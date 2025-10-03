<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $_SESSION['job_title'] = $_POST['job_title'];
        $_SESSION['job_location'] = $_POST['job_location'];
        $_SESSION['job_setup'] = $_POST['job_setup'];

        header("Location: PC_Post_Job2.php");
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

                    <h1> Job Opportunities </h1>

                </section>

                <section>

                    <div class = "Form_Cont1">

                        <form id = "Notif_Form" method = "POST">

                            <div class = "Form1">

                                <label> Job Title </label>
                                <input type = "text" name = "job_title" placeholder = "Ex. Web Developer" required>

                            </div>

                            <div class = "Form2">

                                <label for="subject"> Job Location </label>
                                <input type = "text" name = "job_location" placeholder = "Ex. Bacolod City" required>

                            </div>

                            <div class = "Form3">

                                <label> Job Setup </label>
                                <select name = "job_setup" required>

                                    <option value = "On-site"> On-site </option>
                                    <option value = "Remote"> Remote </option>
                                    <option value = "Hybrid"> Hybrid </option>

                                </select>

                            </div>
                            
                            <button type = "submit" class = "Submit_Btn1"> Next </button>

                        </form>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>