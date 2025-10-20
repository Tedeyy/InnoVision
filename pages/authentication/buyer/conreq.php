<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Registration Confirmation</title>
    <link rel="stylesheet" href="conreq.css">
</head>
<body>
   <div class="regform">
        <form action="conreq.php" method="post">
            <h2>Buyer Registration Details</h2>
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

            <!-- New address fields for buyer -->
            Address<br>
            <input type="text" name="address" value="<?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?>" readonly>
            <br><br>
            Barangay<br>
            <input type="text" name="barangay" value="<?php echo htmlspecialchars($_SESSION['barangay'] ?? ''); ?>" readonly>
            <br><br>
            Municipality<br>
            <input type="text" name="municipality" value="<?php echo htmlspecialchars($_SESSION['municipality'] ?? ''); ?>" readonly>
            <br><br>
            Province<br>
            <input type="text" name="province" value="<?php echo htmlspecialchars($_SESSION['province'] ?? ''); ?>" readonly>
            <br><br>

            Proof of Legitimacy<br>
            <?php
                if (!empty($_SESSION['docs_path'])) {
                    echo "<div style='margin: 10px 0;'>";
                    echo "<img src='" . $_SESSION['docs_path'] . "' width='300' height='200' style='border: 1px solid #ddd; border-radius: 5px; object-fit: cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>";
                    echo "<br><small style='color: #666;'>Uploaded file: " . basename($_SESSION['docs_path']) . "</small>";
                    echo "</div>";
                } else {
                    echo "<div style='color: #999; font-style: italic;'>No image uploaded.</div>";
                }
                
                // Show upload error if any
                if (!empty($_SESSION['upload_error'])) {
                    echo "<div style='color: red; font-size: 12px; margin-top: 5px;'>" . htmlspecialchars($_SESSION['upload_error']) . "</div>";
                    unset($_SESSION['upload_error']);
                }
                ?>
            <br><br>
            Type of Document<br>
            <input type="text" name="supdoctype" value="<?php echo htmlspecialchars($_SESSION['supdoctype']); ?>" readonly>
            <br><br>
            ID Number if not applicable, type N/A<br>
            <input type="text" name="supdocnum" value="<?php echo htmlspecialchars($_SESSION['supdocnum']); ?>" readonly>
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
            <button type="submit" name="proceed" value="proceed">Submit Registration</button>
            <br>
        </form>
    </div>
</body>
</html>

<?php
    if (isset($_POST["proceed"])){
        echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
        echo "<h3>Debug Information:</h3>";
        echo "Form submitted successfully!<br>";
        echo "Session data: <pre>" . print_r($_SESSION, true) . "</pre>";
        
        require_once dirname(__DIR__, 3) . '/config/BuyerRegistrationHandler.php';
        require_once dirname(__DIR__, 3) . '/config/UsernameChecker.php';
        
        $buyerRegistrationHandler = new BuyerRegistrationHandler();
        $usernameChecker = new UsernameChecker();
        
        // Check if username already exists across all user tables
        if ($usernameChecker->usernameExists($_SESSION['username'])) {
            echo "<script>alert('Username already exists. Please choose a different username.'); window.history.back();</script>";
            exit;
        }
        
        // Check if email already exists across all user tables
        if ($usernameChecker->emailExists($_SESSION['email'])) {
            echo "<script>alert('Email already exists. Please use a different email.'); window.history.back();</script>";
            exit;
        }
        
        $data = [
            'user_fname' => $_SESSION['firstname'],
            'user_mname' => $_SESSION['middlename'],
            'user_lname' => $_SESSION['lastname'],
            'bdate' => $_SESSION['bdate'],
            'contact' => $_SESSION['contact'],
            'email' => $_SESSION['email'],
            'supdoctype' => $_SESSION['supdoctype'] ?? '',
            'supdocnum' => $_SESSION['supdocnum'] ?? '',
            'address' => $_SESSION['address'] ?? '',
            'barangay' => $_SESSION['barangay'] ?? '',
            'municipality' => $_SESSION['municipality'] ?? '',
            'province' => $_SESSION['province'] ?? '',
            'username' => $_SESSION['username'],
            'password' => $_SESSION['password'],
            'docs_path' => $_SESSION['docs_path'] ?? ''
        ];
        
        echo "Data to be inserted: <pre>" . print_r($data, true) . "</pre>";

        // Register buyer in database
        $result = $buyerRegistrationHandler->registerBuyer($data);
        echo "Registration result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
        
        if ($result) {
            echo "<script>alert('Registration successful! You can now login.'); window.location.href='../login.php';</script>";
            // Clear session data
            session_destroy();
        } else {
            echo "<script>alert('Registration failed. Please try again.'); window.history.back();</script>";
        }
        echo "</div>";
    }
?>