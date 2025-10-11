<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    // Pagination
    if (isset($_GET['page_no']) && $_GET['page_no'] !== "")
    {
        $page_no = $_GET['page_no'];
    }
    else
    {
        $page_no = 1;
    }

    $total_records_per_page = 8;
    $offset = ($page_no - 1) * $total_records_per_page; 
    $previous_page = $page_no - 1;
    $next_page = $page_no + 1;

    $result_count = mysqli_query($conn, "SELECT COUNT(*) as total_records FROM verified_students");

    $records = mysqli_fetch_array($result_count);
    $total_records = $records['total_records'];
    $total_no_of_pages = ceil($total_records / $total_records_per_page);

    $sql = "SELECT * FROM verified_students LIMIT $offset, $total_records_per_page";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    // Search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $jobs = $admin->getJobOpportunities($search);

    // Format Peso
    function formatPeso($amount)
    {
        if ($amount >= 1000)
        {
            return "₱" . round($amount / 1000, 1) . "k"; 
        }
        return "₱" . $amount;
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Job Postings </title>

        <link rel = "stylesheet" href = "CSS/Style8.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_Notifications.php"> Send Notification </a></li>
                    <li><a href = "CDC_PhilSMS.php"> PhilSMS </a></li>
                    <li><a href = "CDC_Reports.php"> Submission Reports </a></li>
                    <li><a href = "CDC_OJTHours.php"> Students' OJT Hours </a></li>
                    <li><a href = "CDC_JobPostings.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Job Postings </a></li>
                    
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

                    <h1> Job Postings </h1>

                </section>

                <div class = "Table_Container">

                    <form method = "GET" action = "CDC_JobPostings.php">

                        <input id = "searchInput" type = "text" name = "search" placeholder = "Search" value="<?= htmlspecialchars($search); ?>">
                        <button id = "searchButton" type = "submit"> Search </button>

                    </form>

                    <table>

                        <thead>

                            <tr>

                                <th> Company </th>
                                <th> Job Title </th>
                                <th> Job Location </th>
                                <th> Job Setup </th>
                                <th> Job Tags </th>
                                <th> Job Pay </th>
                                <th> Job Requirements </th>
                                <th> Job Responsibilities </th>
                                <th> Date Sent </th>
                                <th> Actions </th>

                            </tr>

                        </thead>

                        <?php if ($jobs && $jobs->num_rows > 0): ?>

                            <?php while ($row = $jobs->fetch_assoc()): ?>

                                <tbody>

                                    <tr>

                                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                                        <td><?= htmlspecialchars($row['job_title']) ?></td>
                                        <td><?= htmlspecialchars($row['job_location']) ?></td>
                                        <td><?= htmlspecialchars($row['job_setup']) ?></td>
                                        <td><?= htmlspecialchars($row['job_tags']) ?></td>
                                        <td><?= formatPeso($row['job_pay_min']) ?> - <?= formatPeso($row['job_pay_max']) ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['job_requirements'])) ?></td>
                                        <td><?= nl2br(htmlspecialchars($row['job_responsibilities'])) ?></td>
                                        <td><?= htmlspecialchars($row['date_sent']) ?></td>

                                        <td>

                                            <div class = "Actions_Btn">

                                                <a class = "View_Btn" href = "CDC_JobPostings_View.php?id=<?= $row['job_id'] ?>"> View </a>
                                                <a class = "Delete_Btn" href = "CDC_JobPostings_Delete.php?id=<?= (int)$row['job_id'] ?>" onclick = "return confirm('Are you sure you want to delete this post?');"> Delete </a>

                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan = "10"> No job opportunities found </td>

                            </tr>

                        <?php endif; ?>

                    </table>

                    <nav class = "Pagination">

                        <a <?= ($page_no <= 1)? 'disabled' : '';?> <?= ($page_no > 1)? 'href=?page_no=' . $previous_page : ''; ?>> Previous </a>

                        <a <?= ($page_no >= $total_no_of_pages) ? 'disabled' : '';?> <?= ($page_no < $total_no_of_pages)? 'href=?page_no=' . $next_page : ''; ?>> Next </a>

                    </nav>

                </div>
                
            </div>

        </div>

    </body>

</html>