<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

    $courseCounts = $company->getStudentsByCourse($company_id);
    
    $itCount  = $courseCounts['IT'] ?? 0;
    $csCount  = $courseCounts['CS'] ?? 0;
    $emcCount = $courseCounts['EMC'] ?? 0;

    $totalStudents = $company->getTotalCompanyStudents($company_id);
    $totalJobs = $company->getTotalJobOpp($company_id);
    $topStudents = $company->getTopRankedStudents($company_id);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Company Dashboard </title>

        <link rel = "stylesheet" href = "CSS/Style3.css?v=<php echo time(); ?>">

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

                    <li><a href = "PC_Dashboard.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Dashboard </a></li>
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
                            <p> Partner Company Portal </p>

                        </div>

                    </div>     

                </section>

                <section class = "DB_Data">

                    <div class = "OJT_Students">

                        <h3> Students By Course </h3>
                        
                        <div class = "OJT_Students_Cont">

                            <div class = "Chart_Legend">

                                <div>

                                    <span class = "Legend_Color IT"></span> IT: <span class = "Legend_Num"> <?php echo $itCount; ?> </span>

                                </div>

                                <div>

                                    <span class = "Legend_Color CS"></span> CS: <span class = "Legend_Num"> <?php echo $csCount; ?> </span>

                                </div>

                                <div>

                                    <span class = "Legend_Color EMC"></span> EMC: <span class = "Legend_Num"> <?php echo $emcCount; ?> </span>

                                </div>

                            </div>

                            <canvas id = "ojtChart" width = "120" height = "120"></canvas>

                            <script>

                                const ctxOJT = document.getElementById('ojtChart');

                                new Chart(ctxOJT, {
                                    type: 'pie',
                                    data: {
                                        labels: ['IT', 'CS', 'EMC'],
                                        datasets: [{
                                            data: [<?php echo $itCount; ?>, <?php echo $csCount; ?>, <?php echo $emcCount; ?>],
                                            backgroundColor: ['#628ECB', '#B1C9EF', '#395886']
                                        }]
                                    },
                                    options: {
                                        responsive: false,
                                        plugins: {
                                            legend: { display: false }
                                        }
                                    }
                                });

                            </script>
                            
                        </div>

                    </div>

                    <div class = "Total_Students">

                        <h3> Total Students </h3>

                        <div class = "Total_Students_Cont">

                            <h1> <?php echo $totalStudents; ?> </h1>
                            
                        </div>

                    </div>

                    <div class = "Total_Jobs">

                        <h3> Job Opportunities </h3>

                        <div class = "Total_Jobs_Cont">

                            <h1> <?php echo $totalJobs; ?> </h1>

                        </div>

                    </div>

                    <div class = "Top_Students">

                        <h3> Top Students </h3>

                        <div class = "Top_Students_Cont">

                            <?php if (!empty($topStudents)): ?>

                                <ol>

                                    <?php foreach ($topStudents as $student): ?>

                                        <li>

                                            <strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?> </strong> (Rank: <strong> <?php echo $student['evaluation_rank']; ?> </strong>)

                                        </li>

                                    <?php endforeach; ?>

                                </ol>

                            <?php else: ?>

                                <strong> No evaluations yet </strong>

                            <?php endif; ?>

                        </div>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>