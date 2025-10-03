<?php

    include '../Controller/PC_Check.php';
    include '../Config/Config.php';
    include '../Model/PC.php';

    $company = new PC($conn);

    $company_id = $_SESSION['company_id'];
    $companyDetails = $company->getCompanyDetails($_SESSION['company_id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update']))
    {
        $id          = $_SESSION['company_id'];
        $name        = $_POST['name'];
        $username    = $_POST['username'];
        $email       = $_POST['email'];
        $contact_num = $_POST['contact_num'];
        $address     = $_POST['address'];
        $password    = !empty($_POST['password']) ? $_POST['password'] : null;

        $moa = null;
        if (!empty($_FILES['moa']['name']))
        {
            $moa = "../../Uploads/PC_Files/" . time() . "_" . basename($_FILES['moa']['name']);
            move_uploaded_file($_FILES['moa']['tmp_name'], $moa);
        }

        $company_id_img = null;
        if (!empty($_FILES['id_img']['name']))
        {
            $company_id_img = "../../Uploads/PC_Files/" . time() . "_" . basename($_FILES['id_img']['name']);
            move_uploaded_file($_FILES['id_img']['tmp_name'], $company_id_img);
        }

        $filename = "companylogo_" . time() . "_" . basename($_FILES['profile_pic']['name']);
        $target   = "../../Uploads/ProfilePics/" . $filename;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
        
        $profile_pic = $filename;

        if ($company->editProfile($id, $name, $username, $email, $contact_num, $address, $password, $moa, $company_id_img, $profile_pic))
        {
            echo "<script>alert('Profile updated successfully!'); window.location.href='PC_Profile.php';</script>";
        }
        
        else
        {
            echo "<script>alert('Error updating profile. Please try again.');</script>";
        }
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> Edit Profile </title>

        <link rel = "stylesheet" href = "CSS/Style5.css?v=<php echo time(); ?>">

        <script src = "JS/Script.js"></script>

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

                    <h1> Edit Profile </h1>

                </section>

                <div class = "Data_Cont">

                    <form method = "POST" enctype = "multipart/form-data">

                        <div class = "Datas">

                            <div class = "Data1">

                                <label> Company Name </label>
                                <input type = "text" name = "name" value = "<?php echo htmlspecialchars($companyDetails['name']); ?>">

                                <label> Username </label>
                                <input type = "text" name = "username" value = "<?php echo htmlspecialchars($companyDetails['username']); ?>">

                                <label> Password </label>
                                <input type = "text" name = "password" value = "">

                                <label> Email </label>
                                <input type = "text" name = "email" value = "<?php echo htmlspecialchars($companyDetails['email']); ?>">

                            </div>

                            <div class = "Data2">

                                <label> Contact Number </label>
                                <input type = "text" name = "contact_num" value = "<?php echo htmlspecialchars($companyDetails['contact_num']); ?>">

                                <label> Address </label>
                                <input type = "text" name = "address" value = "<?php echo htmlspecialchars($companyDetails['address']); ?>">

                                <label> Memorandum of Agreement </label>
                                <input type = "file" name = "moa" accept = "application/pdf">

                                <label> Company ID </label>
                                <input type = "file" name = "id_img" accept = "image/*">

                                <label> Profile Picture </label>
                                <input type = "file" name = "profile_pic" accept = "image/*">

                            </div>

                        </div>

                        <button type = "submit" name = "update"> Update </button>
                            
                    </form>

                </div>

            </div>

        </div>

    </body>

</html>