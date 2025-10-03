<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

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

    $search = $_GET['search'] ?? "";
    $students = $company->getStudentsByCompany($company_id, $search);

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Students </title>

        <link rel = "stylesheet" href = "CSS/Style6.css?v=<php echo time(); ?>">

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

                    <h1> Students </h1>

                </section>

                <div class = "Table_Container">

                    <form method = "GET" action = "PC_Students.php">

                        <input id = "searchInput" type = "text" name = "search" placeholder = "Search Name or Course" value="<?= htmlspecialchars($search); ?>">
                        <button id = "searchButton" type = "submit"> Search </button>

                    </form>

                    <table>

                        <thead>

                            <tr>

                                <th> Name </th>
                                <th> Course </th>
                                <th> Email </th>
                                <th> Contact Number </th>
                                <th> Performance Score </th>
                                <th> Evaluation Rank </th>
                                <th> Action </th>

                            </tr>

                        </thead>

                        <?php if ($students->num_rows > 0): ?>

                            <?php while ($row = $students->fetch_assoc()): ?>

                                <tbody>

                                    <tr>

                                        <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></td>
                                        <td><?= htmlspecialchars($row['course']); ?></td>
                                        <td><?= htmlspecialchars($row['email']); ?></td>
                                        <td><?= htmlspecialchars($row['contact_num']); ?></td>
                                        <td style = "font-weight:bold; <?php if (!empty($row['performance_score'])): ?> color: <?= ($row['performance_score'] === 'Good') ? 'green' : 'red'; ?>; <?php else: ?> color: #395886; <?php endif; ?>"><?= htmlspecialchars($row['performance_score'] ?? 'N/A' ); ?></td>
                                        <td style = "font-weight:bold; <?php if (!empty($row['evaluation_rank'])): ?> color: <?= ($row['evaluation_rank'] >= 51) ? 'green' : 'red'; ?>; <?php else: ?> color: #395886; <?php endif; ?>"><?= htmlspecialchars($row['evaluation_rank'] ?? 'N/A' ); ?></td>
                                        <td>

                                            <div class = "Actions_Btn">
                                                
                                                <a class = "Eval_Btn" href = "PC_Students_Evaluate.php?id=<?= $row['student_id'] ?>"> Evaluate </a>

                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            <?php endwhile; ?>

                        <?php else: ?>

                                <tbody>

                                    <tr>

                                        <td colspan = "7" style = "text-align:center;"> No students found </td>

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