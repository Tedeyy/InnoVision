<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration Form</title>
    <link rel="stylesheet" href="req.css">
</head>
<body>
    <div name="regform">
        <form action="req.php" method="post">
            <h2>Details</h2>
            First Name<br>
            <input type="text" name="firstname" required>
            <br><br>
            Middle Name<br>
            <input type="text" name="middlename" required>
            <br><br>
            Last Name<br>
            <input type="text" name="lastname" required>
            <br><br>
            Birthdate<br>
            <input type="date" name="bdate" required>
            <br><br>
            Contact Number<br>
            <input type="text" name="contact" required>
            <br><br>
            Email Address<br>
            <input type="text" name="email" required>
            <br><br>
            RSBSA Number<br>
            <input type="text" name="rsbsanum" required>
            <br><br>
            Valid ID<br>
            <input type="image" src="/icons/upload_icon.png" alt="Upload" width="100" height="100" name="img" required>
            <br><br>
            Valid ID Number<br>
            <input type="text" name="idnum" required>
            <br><br>
            <button type="submit" name="next" value="next">Proceed</button>
            <br>
        </form>
    </div>
</body>
</html>

<?php
    if (isset($_POST["next"])){

            $_SESSION["firstname"] = $_POST["firstname"];
            $_SESSION["middlename"] = $_POST["middlename"];
            $_SESSION["lastname"] = $_POST["lastname"];
            $_SESSION["bdate"] = $_POST["bdate"];
            $_SESSION["contact"] = $_POST["contact"];
            $_SESSION["email"] = $_POST["email"];
            $_SESSION["rsbsanum"] = $_POST["rsbsanum"];
            $_SESSION["idnum"] = $_POST["idnum"];
            

            header("Location: conreq.php");
    }
?>