<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    if (!isset($_GET['id']))
    {
        die("No student ID provided.");
    }

    $id = intval($_GET['id']);
    $student = $admin->getVerifiedStudentById($id);

    if (!$student)
    {
        die("Student not found.");
    }

    if (isset($_POST['update']))
    {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $id_num = trim($_POST['id_num']);
        $email = trim($_POST['email']);
        $contact_num = trim($_POST['contact_num']);
        $course = trim($_POST['course']);
        $password = trim($_POST['password']);

        $res = $admin->updateVerifiedStudent($id, $first_name, $last_name, $id_num, $email, $contact_num, $course, $password);

        if ($res === "duplicate")
        {
            echo "<script>alert('ID Number already exists!'); window.history.back();</script>";
            exit();
        }

        if ($res === false)
        {
            echo "<script>alert('Error updating student. Please try again.'); window.history.back();</script>";
            exit();
        }

        $company_id = !empty($_POST['company_id']) ? intval($_POST['company_id']) : null;
        $admin->assignCompanyToStudent($id, $company_id);

        echo "<script>alert('Student updated successfully!'); window.location.href='CDC_Students.php';</script>";
        exit();
    }

    $companies = $admin->getCompanies();

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Update Student </title>

        <link rel = "stylesheet" href = "CSS/Style11.css?v=<php echo time(); ?>">
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

                    <h1> Update Student </h1>

                </section>

                <div class = "Data_Cont">

                    <form method = "POST">

                        <div class = "Datas">

                            <div class = "Data1">

                                <label> First Name </label>
                                <input type = "text" name = "first_name" value = "<?php echo htmlspecialchars($student['first_name']); ?>">

                                <label> Last Name </label>
                                <input type = "text" name = "last_name" value = "<?php echo htmlspecialchars($student['last_name']); ?>">

                                <label> ID Number </label>
                                <input type = "text" name = "id_num" value = "<?php echo htmlspecialchars($student['id_num']); ?>">

                                <label> Course </label>
                                <select name = "course" value = "<?php echo htmlspecialchars($student['course']); ?>">

                                    <option value = ""> Select Course </option>
                                    <option value = "Information Technology"> Information Technology </option>
                                    <option value = "Computer Science"> Computer Science </option>
                                    <option value = "Entertainment and Multimedia"> Entertainment and Multimedia </option>

                                </select>

                            </div>

                            <div class = "Data2">

                                <label> Email </label>
                                <input type = "text" name = "email" value = "<?php echo htmlspecialchars($student['email']); ?>">

                                <label> Contact Number </label>
                                <input type = "text" name = "contact_num" value = "<?php echo htmlspecialchars($student['contact_num']); ?>">

                                <label> Password </label>
                                <input type = "text" name = "password" value = "<?php echo htmlspecialchars($student['password']); ?>">

                                <label> Company </label>
                                <select name = "company_id" id = "company_id">

                                    <option value = ""> Select Company </option>

                                    <?php while ($company = $companies->fetch_assoc()): ?>

                                        <option value="<?= $company['company_id']; ?>"

                                            <?= ($student['company_id'] == $company['company_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($company['name']); ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>

                        </div>

                        <button type = "submit" name = "update"> Update </button>
                            
                    </form>

                </div>
                
            </div>

        </div>

    </body>

</html>