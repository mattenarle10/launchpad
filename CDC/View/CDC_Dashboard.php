<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    $courseCounts = $admin->getStudentsByCourse();

    $itCount  = $courseCounts['IT'];
    $csCount  = $courseCounts['CS'];
    $emcCount = $courseCounts['EMC'];

    $unverified = $admin->getUnverifiedCounts();

    $unvStudents = $unverified['students'];
    $unvCompanies = $unverified['companies'];
    
?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> CDC Dashboard </title>

        <link rel = "stylesheet" href = "CSS/Style2.css?v=<php echo time(); ?>">

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

                    <li><a href = "CDC_Dashboard.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Dashboard </a></li>
                    <li><a href = "CDC_Students.php"> Students </a></li>
                    <li><a href = "CDC_Companies.php"> Companies </a></li>
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

                <section class = "DB_Header">

                    <div class = "Header_Text">

                        <div class = "Profile_Pic" style = "background-image: url('../../Uploads/ProfilePics/<?php echo htmlspecialchars($adminDetails['profile_pic'] ?? 'default.png'); ?>'); background-size: cover; background-position:"> </div>

                        <div class = "Admin_Name">
                            
                            <h1> <?php echo htmlspecialchars($adminDetails['first_name']); ?> <?php echo htmlspecialchars($adminDetails['last_name']); ?></h1>
                            <p> Career Development <br> Centre Portal </p>

                        </div>

                    </div>     

                </section>

                <section class = "DB_Data">

                    <div class = "OJT_Students">

                        <h3> OJT Students </h3>
                        
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

                            <h1> <?php echo $admin->getVerifiedStudentsNum(); ?> </h1>
                            
                        </div>

                    </div>

                    <div class = "Total_Companies">

                        <h3> Total Companies </h3>

                        <div class = "Total_Companies_Cont">

                            <h1> <?php echo $admin->getVerifiedCompaniesNum(); ?> </h1>

                        </div>

                    </div>

                    <div class = "Unverified_Users">

                        <h3> Unverified Users </h3>

                        <div class = "Unverified_Users_Cont">

                            <div class = "Chart_Legend2">

                                <div>

                                    <span class = "Legend_Color IT"></span> Students: <span class = "Legend_Num"> <?php echo $unvStudents ?> </span>

                                </div>

                                <div>

                                    <span class = "Legend_Color CS"></span> Companies: <span class = "Legend_Num"> <?php echo $unvCompanies; ?> </span>

                                </div>

                            </div>

                            <canvas id = "unverifiedChart" width = "120" height = "120"></canvas>

                            <script>
                                
                                const ctxUVC = document.getElementById('unverifiedChart');

                                new Chart(ctxUVC, {
                                    type: 'pie',
                                    data: {
                                        labels: ['Students', 'Companies'],
                                        datasets: [{
                                            data: [<?php echo $unvStudents; ?>, <?php echo $unvCompanies; ?>],
                                            backgroundColor: ['#628ECB', '#B1C9EF']
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

                </section>

                <section class = "DB_Data">

                    <div class = "Total_Jobs">

                        <h3> Job Opportunities </h3>

                        <div class = "Total_Jobs_Cont">

                            <h1> <?php echo $admin->getJobOppNum(); ?> </h1>
                            
                        </div>

                    </div>

                    <div class = "IT_Average"> 

                        <h3> Rank Average (IT) </h3> 

                        <div class = "IT_Average_Cont">

                            <h1> <?php echo $admin->getAvgByCourse("Information Technology"); ?> </h1>

                        </div>

                    </div>

                    <div class = "CS_Average"> 

                        <h3> Rank Average (CS) </h3> 

                        <div class = "CS_Average_Cont"> 

                            <h1> <?php echo $admin->getAvgByCourse("Computer Science"); ?> </h1>

                        </div>

                    </div>

                    <div class = "EMC_Average"> 

                        <h3> Rank Average (EMC) </h3> 

                        <div class = "EMC_Average_Cont"> 

                            <h1> <?php echo $admin->getAvgByCourse("Entertainment and Multimedia"); ?> </h1> 

                        </div>

                    </div>

                </section>
                
            </div>

        </div>

    </body>

</html>