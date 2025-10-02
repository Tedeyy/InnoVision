<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
   <div name="regform">
        <form action="req.php" method="post">
            <h2>Details</h2>
            First Name<br>
            <input type="text" name="firstname" value="<?php echo htmlspecialchars($_SESSION['firstname']); ?>" readonly>
            <br><br>
            Middle Name<br>
            <input type="text" name="middlename" value="<?php echo htmlspecialchars($_SESSION['middlename']); ?>" readonly>
            <br><br>
            Last Name<br>
            <input type="text" name="lastname" value="<?php echo htmlspecialchars($_SESSION['lastname']); ?>" readonly>
            <br><br>
            Birthdate<br>
            <input type="date" name="bdate" value="<?php echo htmlspecialchars($_SESSION['bdate']); ?>" readonly>
            <br><br>
            Contact Number<br>
            <input type="text" name="contact" value="<?php echo htmlspecialchars($_SESSION['contact']); ?>" readonly>
            <br><br>
            Email Address<br>
            <input type="text" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
            <br><br>
            RSBSA Number<br>
            <input type="text" name="rsbsanum" value="<?php echo htmlspecialchars($_SESSION['rsbsanum']); ?>" readonly>
            <br><br>
            Valid ID<br>
            <?php
                if (!empty($_SESSION['valid_id_path'])) {
                    echo "<img src='" . $_SESSION['valid_id_path'] . "' width='200'>";
                } else {
                    echo "No image uploaded.";
                }
                ?>
            <br><br>
            Valid ID Number<br>
            <input type="text" name="idnum" value="<?php echo htmlspecialchars($_SESSION['idnum']); ?>" readonly>
            <br><br>
            <br><br>
            <div id="acc">Login Credentials<br>
                Username<br>
                <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                <br><br>
                Password<br>
                <input type="text" name="password" value="<?php echo htmlspecialchars($_SESSION['password']); ?>" readonly>
                <br>
            </div>
            <br><br>
            <button type="submit" name="proceed" value="proceed">Proceed</button>
            <br>
        </form>
    </div>
</body>
</html>

<?php
    if (isset($_POST["proceed"])){

        $data = [
        'user_fname' => $_SESSION['firstname'],
        'user_mname' => $_SESSION['middlename'],
        'user_lname' => $_SESSION['lastname'],
        'bdate' => $_SESSION['bdate'],
        'contact' => $_SESSION['contact'],
        'email' => $_SESSION['email'],
        'rsbsanum' => $_SESSION['rsbsanum'],
        'idnum' => $_SESSION['idnum'],
        'username' => $_SESSION['username'],
        'password' => $_SESSION['password']
        ];
        
        $json_data = json_encode($data);

        
    }
?>