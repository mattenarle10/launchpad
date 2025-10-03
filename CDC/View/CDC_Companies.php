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

    $total_records_per_page = 4;
    $offset = ($page_no - 1) * $total_records_per_page; 
    $previous_page = $page_no - 1;
    $next_page = $page_no + 1;

    $result_count = mysqli_query($conn, "SELECT COUNT(*) as total_records FROM verified_companies");

    $records = mysqli_fetch_array($result_count);
    $total_records = $records['total_records'];
    $total_no_of_pages = ceil($total_records / $total_records_per_page);

    $sql = "SELECT * FROM verified_companies LIMIT $offset, $total_records_per_page";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    // Search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $companies = $admin->searchCompanies($search);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Companies </title>

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

                    <h1> Companies </h1>

                </section>

                <div class = "Table_Container">

                    <form method = "GET" action = "CDC_Companies.php">

                        <input id = "searchInput" type="text" name = "search" placeholder = "Search Company ID or Name" value="<?= htmlspecialchars($search); ?>">
                        <button id = "searchButton" type = "submit"> Search </button>

                    </form>

                    <table>

                        <thead>

                            <tr>

                                <th> Company ID </th>
                                <th> Name </th>
                                <th> Username </th>
                                <th> Password </th>
                                <th> Email </th>
                                <th> Contact Number </th>
                                <th> Address </th>
                                <th> Website </th>
                                <th> No. of Students </th>
                                <th> Job Postings </th>
                                <th> Action </th>

                            </tr>

                        </thead>

                        <?php if ($companies->num_rows > 0): ?>

                            <?php while ($row = $companies->fetch_assoc()): ?>

                                <tbody>

                                    <tr>

                                        <td><?php echo htmlspecialchars($row['company_id']) ?></td>
                                        <td><?php echo htmlspecialchars($row['name']) ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class = "Password_Row"><?php echo htmlspecialchars($row['password']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_num']); ?></td>
                                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                                        <td><?php echo htmlspecialchars($row['website']); ?></td>
                                        <td><?php echo htmlspecialchars($row['num_students']); ?></td>
                                        <td><?php echo htmlspecialchars($row['num_job_postings']); ?></td>
                                        <td>

                                            <div class = "Actions_Btn">

                                                <a class = "View_Btn" href = "CDC_Companies_View.php?id=<?= $row['company_id'] ?>"> View </a>
                                                <a class = "Update_Btn" href = "CDC_Companies_Update.php?id=<?= $row['company_id'] ?>"> Update </a>
                                                <a class = "Delete_Btn" href = "CDC_Companies_Delete.php?id=<?= $row['company_id'] ?>" onclick = "return confirm('Are you sure you want to delete this company?');"> Delete </a>

                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan = "11" style = "text-align:center;"> No companies found </td>

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