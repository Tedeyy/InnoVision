<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Confirmation</title>
    <link rel="stylesheet" href="conreq.css">
</head>
<body>
    <div class="regform">
        <form action="conreq.php" method="post">
            <h2>Confirm Admin Details</h2>
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
            Office<br>
            <input type="text" name="office" value="<?php echo htmlspecialchars($_SESSION['office']); ?>" readonly>
            <br><br>
            Role<br>
            <input type="text" name="role" value="<?php echo htmlspecialchars($_SESSION['role']); ?>" readonly>
            <br><br>
            Supporting Document Type<br>
            <input type="text" name="supdoctype" value="<?php echo htmlspecialchars($_SESSION['supdoctype']); ?>" readonly>
            <br><br>
            Document Image<br>
            <?php if (!empty($_SESSION['docs_path'])): ?>
                <img src="<?php echo $_SESSION['docs_path']; ?>" width="300" height="200">
            <?php else: ?>
                <div>No image uploaded.</div>
            <?php endif; ?>
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
            <button type="submit" name="proceed" value="proceed">Submit Registration</button>
        </form>
    </div>
</body>
</html>

<?php
if (isset($_POST['proceed'])) {
    require_once dirname(__DIR__, 2) . '/config/AdminRegistrationHandler.php';
    $handler = new AdminRegistrationHandler();

    if ($handler->usernameExists($_SESSION['username'])) {
        echo "<script>alert('Username already exists.'); window.history.back();</script>"; exit;
    }
    if ($handler->emailExists($_SESSION['email'])) {
        echo "<script>alert('Email already exists.'); window.history.back();</script>"; exit;
    }

    $data = [
        'user_fname' => $_SESSION['firstname'],
        'user_mname' => $_SESSION['middlename'],
        'user_lname' => $_SESSION['lastname'],
        'bdate' => $_SESSION['bdate'],
        'contact' => $_SESSION['contact'],
        'email' => $_SESSION['email'],
        'office' => $_SESSION['office'],
        'role' => $_SESSION['role'],
        'supdoctype' => $_SESSION['supdoctype'],
        'username' => $_SESSION['username'],
        'password' => $_SESSION['password'],
        'docs_path' => $_SESSION['docs_path'] ?? ''
    ];

    $ok = $handler->registerAdmin($data);
    if ($ok) {
        echo "<script>alert('Registration successful.'); window.location.href='../login.php';</script>";
        session_destroy();
    } else {
        echo "<script>alert('Registration failed.'); window.history.back();</script>";
    }
}
?>

