<?php

    include '../Controller/CDC_Check.php';
    include '../Config/Config.php';
    include '../Model/CDC.php';

    $admin = new CDC($conn);

    $admin_id = $_SESSION['admin_id'];
    $adminDetails = $admin->getAdminDetails($admin_id);

    $responseMessage = "";
    $showAlert = false;

    if ($_SERVER["REQUEST_METHOD"] === "POST")
    {
        $recipient = $_POST["recipient"];
        $message = $_POST["txt_message"];

        $url = "https://app.philsms.com/api/v3/sms/send";
        $apiToken = "1740|yWK3WJ5iGI0YFNXR9mYZwO700y2xcyz5WculBuae ";

        $data = [
            "recipient" => $recipient,
            "sender_id" => "PhilSMS",
            "type" => "plain",
            "message" => $message
        ];

        $headers = [
            "Authorization: Bearer $apiToken",
            "Content-Type: application/json",
            "Accept: application/json"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $responseMessage = "HTTP Status: $http_status<br>Response: $response";
        $showAlert = true;
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> PhilSMS </title>

        <link rel = "stylesheet" href = "CSS/Style16.css?v=<php echo time(); ?>">
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
                    <li><a href = "CDC_PhilSMS.php" style = "background-color: #F0F3FA; border-radius: 35px;"> PhilSMS </a></li>
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

                    <h1> Send SMS powered by </h1>

                    <br>

                    <img src = "Images/PhilSMS.png" style = "width: 100px; height: auto;">

                    <h3> PhilSMS </h3>

                </section>

                <section>

                    <div class = "Form_Cont">

                        <form id = "Notif_Form" method = "POST">

                            <div class = "Form1">

                                <label> Recipient </label>
                                <input type = "text" name = "recipient" placeholder = "Student's Contact Number e.g., 639171234567" value = "+63" required>

                            </div>

                            <div class = "Form2">

                                <label for = "txt_message"> Message </label>
                                <textarea name = "txt_message" placeholder = "Message" required></textarea>
                                
                            </div>
                            
                            <button type = "submit" class = "Submit_Btn"> Submit </button>

                        </form>

                    </div>

                    <?php if ($showAlert): ?>

                        <script> alert("Message has been sent successfully!"); </script>

                    <?php endif; ?>

                </section>
                
            </div>

        </div>

    </body>

</html>