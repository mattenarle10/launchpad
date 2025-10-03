<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

    if (!isset($_GET['id']) || !is_numeric($_GET['id']))
    {
        header("Location: PC_JobPostings.php");
        exit();
    }

    $job_id = intval($_GET['id']);
    $job = $company->getJobById($job_id, $company_id);

    if (!$job)
    {
        die("Invalid job ID or you don't have access to this job.");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update']))
    {
        $job_title = $_POST['job_title'];
        $job_location = $_POST['job_location'];
        $job_setup = $_POST['job_setup'];
        $job_tags = $_POST['job_tags'];
        $job_pay_min = $_POST['job_pay_min'];
        $job_pay_max = $_POST['job_pay_max'];
        $job_requirements = $_POST['job_requirements'];
        $job_responsibilities = $_POST['job_responsibilities'];

        if ($company->updateJob($job_id, $company_id, $job_title, $job_location, $job_setup, $job_tags, $job_pay_min, $job_pay_max, $job_requirements, $job_responsibilities))
        {
            echo "<script>alert('Job updated successfully!'); window.location='PC_JobPostings.php';</script>";
            exit();
        }
        
        else
        {
            echo "<script>alert('Failed to update job.');</script>";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Job Opportunities </title>

        <link rel = "stylesheet" href = "CSS/Style10.css?v=<php echo time(); ?>">

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

                    <h1> Edit Job Opportunity </h1>

                </section>

                <section>

                    <div class = "Form_Cont">

                        <form method = "POST">

                            <div class = "Edit_Cont">

                                <div class = "Edit1">

                                    <label> Job Title </label>
                                    <input type="text" name="job_title" value="<?= htmlspecialchars($job['job_title']); ?>" required>

                                    <label> Job Location </label>
                                    <input type="text" name="job_location" value="<?= htmlspecialchars($job['job_location']); ?>" required>

                                    <label> Job Setup </label>
                                    <select name = "job_setup" required>

                                        <option value = "On-site"> On-site </option>
                                        <option value = "Remote"> Remote </option>
                                        <option value = "Hybrid"> Hybrid </option>

                                    </select>

                                    <label> Job Tags </label>
                                    <input type="text" name="job_tags" value="<?= htmlspecialchars($job['job_tags']); ?>" required>

                                    <label> Job Pay (Min) </label>
                                    <input type="number" name="job_pay_min" value="<?= htmlspecialchars($job['job_pay_min']); ?>" required>

                                    <label> Job Pay (Max) </label>
                                    <input type="number" name="job_pay_max" value="<?= htmlspecialchars($job['job_pay_max']); ?>" required>

                                </div>

                                <div class = "Edit2">

                                    <label> Job Requirements </label>
                                    <textarea name="job_requirements" required><?= htmlspecialchars($job['job_requirements']); ?></textarea>

                                    <label> Job Responsibilities </label>
                                    <textarea name="job_responsibilities" required><?= htmlspecialchars($job['job_responsibilities']); ?></textarea>

                                    <button type = "submit" class = "Submit_Btn" name = "update"> Update Job </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>