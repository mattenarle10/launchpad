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

    $result_count = mysqli_query($conn, "SELECT COUNT(*) as total_records FROM verified_students");

    $records = mysqli_fetch_array($result_count);
    $total_records = $records['total_records'];
    $total_no_of_pages = ceil($total_records / $total_records_per_page);

    $sql = "SELECT * FROM verified_students LIMIT $offset, $total_records_per_page";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    // Search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $students = $admin->getStudents($search);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Students </title>

        <link rel = "stylesheet" href = "CSS/Style3.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_Students.php" style = "background-color: #F0F3FA; border-radius: 35px;"> Students </a></li>
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

                <section class = "Title">

                    <h1> Students </h1>

                </section>

                <div class = "Table_Container">

                    <form method = "GET" action = "CDC_Students.php">

                        <input id = "searchInput" type = "text" name = "search" placeholder = "Search ID Number or Name" value="<?= htmlspecialchars($search); ?>">
                        <button id = "searchButton" type = "submit"> Search </button>

                    </form>

                    <table>

                        <thead>

                            <tr>

                                <th> Student ID </th>
                                <th> Name </th>
                                <th> ID Number </th>
                                <th> Course </th>
                                <th> Email </th>
                                <th> Password </th>
                                <th> Contact Number </th>
                                <th> Company </th>
                                <th> Performance Score </th>
                                <th> Evaluation Rank </th>
                                <th> Action </th>

                            </tr>

                        </thead>

                        <?php if ($students->num_rows > 0): ?>

                            <?php while ($row = $students->fetch_assoc()): ?>

                                <tbody>

                                    <tr>

                                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name']). " " . htmlspecialchars($row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['id_num']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class = "Password_Row"><?php echo htmlspecialchars($row['password']); ?></td>
                                        <td><?php echo htmlspecialchars($row['contact_num']); ?></td>
                                        <td><?php echo htmlspecialchars($row['company_name'] ?? 'Not Assigned'); ?></td>
                                        <td style = "font-weight:bold; <?php if (!empty($row['performance_score'])): ?> color: <?= ($row['performance_score'] === 'Good') ? 'green' : 'red'; ?>; <?php else: ?> color: #395886; <?php endif; ?>"> <?= htmlspecialchars($row['performance_score'] ?? 'N/A'); ?></td>
                                        <td style = "font-weight:bold; <?php if (!empty($row['evaluation_rank'])): ?> color: <?= ($row['evaluation_rank'] >= 51) ? 'green' : 'red'; ?>; <?php else: ?> color: #395886; <?php endif; ?>"><?php echo htmlspecialchars($row['evaluation_rank'] ?? 'N/A'); ?></td>
                                        <td>

                                            <div class = "Actions_Btn">

                                                <a class = "View_Btn" href = "CDC_Students_View.php?id=<?= $row['student_id'] ?>"> View </a>
                                                <a class = "Update_Btn" href = "CDC_Students_Update.php?id=<?= $row['student_id'] ?>"> Update </a>
                                                <a class = "Delete_Btn" href = "CDC_Students_Delete.php?id=<?= $row['student_id'] ?>" onclick = "return confirm('Are you sure you want to delete this student?');"> Delete </a>

                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            <?php endwhile; ?>

                        <?php else: ?>

                                <tbody>

                                    <tr>

                                        <td colspan = "11" style = "text-align:center;"> No students found </td>

                                    </tr>

                                </tbody>

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