<!DOCTYPE html>
<html lang = "en">

    <head>

        <meta charset="UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">

        <title> CDC Login </title>

        <link rel = "stylesheet" href = "Style.css?v=<php echo time(); ?>">

    </head>

    <body>

        <div class = "Container">

            <img src = "Images/LP Logo.png" alt = "LP Logo">

        </div>

        <div class = "Container2">

            <h1> Web Portal </h1>

        </div>

        <div class = "Form">

            <div class = "CDC" onclick = "window.location.href = '../CDC/View/CDC_Login.php'">

                <img src = "Images/CDC Logo 2.png" alt = "CDC Icon" width = "40">
                <h1> Career Development Centre </h1>

            </div>

            <div class = "PC" onclick = "window.location.href = '../PC/View/PC_Login.php'">

                <img src = "Images/PC.png" alt = "PC Icon" width = "40">
                <h1> Partner Company </h1>

            </div>

        </div>

    </body>

</html>