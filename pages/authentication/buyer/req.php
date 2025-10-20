<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Registration Form</title>
    <link rel="stylesheet" href="req.css">
</head>
<body>
    <div class="regform">
        <form action="req.php" method="post" enctype="multipart/form-data">
            <h2>Buyer Registration Details</h2>
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

            <!-- New address fields for buyer -->
            Address<br>
            <input type="text" name="address" required>
            <br><br>
            Barangay<br>
            <input type="text" name="barangay" required>
            <br><br>
            Municipality<br>
            <input type="text" name="municipality" required>
            <br><br>
            Province<br>
            <input type="text" name="province" required>
            <br><br>

            
            <br><br>
            Proof of Legitimacy<br>
            <input type="file" name="supporting_doc" accept="image/*" required>
            <br><br>
            Type of Document<br>
            <select name="supdoctype" required>
                <option value="">Select Document Type</option>
                <option value="DTI Certificate">DTI Certificate</option>
                <option value="Business Permit">Business Permit</option>
                <option value="Other">Other</option>
            </select>
            <br><br>
            ID Number if not applicable, type N/A<br>
            <input type="text" name="supdocnum" required>
            <br><br>

            <div id="acc">Login Credentials<br>
                Username<br>
                <input type="text" name="username" required>
                <br><br>
                Password<br>
                <input type="password" name="password" required>
                <br>
            </div>
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
            $_SESSION["supdoctype"] = $_POST["supdoctype"];
            $_SESSION["supdocnum"] = $_POST["supdocnum"];
            $_SESSION["username"] = $_POST["username"];
            $_SESSION["password"] = $_POST["password"];

            // New session fields for address
            $_SESSION["address"] = $_POST["address"];
            $_SESSION["barangay"] = $_POST["barangay"];
            $_SESSION["municipality"] = $_POST["municipality"];
            $_SESSION["province"] = $_POST["province"];

            if (isset($_FILES['supporting_doc']) && $_FILES['supporting_doc']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Get file extension
                $ext = pathinfo($_FILES['supporting_doc']['name'], PATHINFO_EXTENSION);
                
                // Create filename as firstname_lastname_docs
                $firstname = strtolower(trim($_POST['firstname']));
                $lastname = strtolower(trim($_POST['lastname']));
                $filename = $firstname . '_' . $lastname . '_docs.' . $ext;
                
                $targetFile = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['supporting_doc']['tmp_name'], $targetFile)) {
                    $_SESSION['docs_path'] = 'upload/' . $filename;
                } else {
                    $_SESSION['upload_error'] = 'Failed to upload image. Please try again.';
                }
            }

            header("Location: conreq.php");
    }
?>