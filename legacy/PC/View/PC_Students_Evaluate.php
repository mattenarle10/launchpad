<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

    $student_id = $_GET['id'] ?? null;

    if (!$student_id)
    {
        header("Location: PC_Dashboard.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update']))
    {
        $eval_rank = (int) $_POST['eval_rank'];

        if ($company->saveEvaluation($company_id, $student_id, $eval_rank))
        {
            echo "<script>alert('Evaluation updated successfully!'); window.location.href='PC_Students.php?id={$student_id}';</script>";
            exit();
        }
        
        else
        {
            echo "<script>alert('Failed to update evaluation.'); window.location.href='PC_Students.php?id={$student_id}';</script>";
            exit();
        }
    }

    $student = $company->getStudentDetails($company_id, $student_id);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Evaluate Student </title>

        <link rel = "stylesheet" href = "CSS/Style7.css?v=<php echo time(); ?>">

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
                    <li><a href = "PC_Students.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Students </a></li>
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

                <section class = "Title">

                    <h1> Evaluate Student </h1>

                </section>

                <div class = "Details_Main">

                    <div class = "Details_Cont">
                        
                        <div class = "Profile_Pic" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($student['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position: center;"> </div>
                        
                        <p> <strong> Name: </strong> <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?> </p>
                        <p> <strong> Course: </strong> <?= htmlspecialchars($student['course']); ?> </p>

                        <br>
                        
                        <p> <strong> Email: </strong> <?= htmlspecialchars($student['email']); ?> </p>
                        <p> <strong> Contact Number: </strong> </strong> <?= htmlspecialchars($student['contact_num']); ?> </p>

                    </div>

                    <div class = "Details_Cont2">

                        <p> Current Rank: <strong> <?= $student['evaluation_rank'] !== null ? htmlspecialchars($student['evaluation_rank']) : 'N/A'; ?> </strong> </p>
                        <p> 0-50 = Bad | 51-100 =  Good </p>

                        <form class = "Hours_Cont" method = "POST">

                            <label> New Rank: </label>
                            <input type = "number" name = "eval_rank" value = "0" min = "0">

                            <br>

                            <button type = "submit" name = "update"> Update </button>

                        </form>

                    </div>

                </div>
                
            </div>

        </div>

    </body>

</html>