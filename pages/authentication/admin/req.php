<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration Form</title>
    <link rel="stylesheet" href="req.css">
</head>
<body>
    <div class="regform">
        <form action="conreq.php" method="post" enctype="multipart/form-data">
            <h2>Admin Registration Details</h2>
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
            <input type="email" name="email" required>
            <br><br>
            Office<br>
            <input type="text" name="office" required>
            <br><br>
            Role<br>
            <input type="text" name="role" required>
            <br><br>
            Supporting Document Type<br>
            <input type="text" name="supdoctype" required>
            <br><br>
            Supporting Document Image<br>
            <input type="file" name="supporting_doc" accept="image/*" required>
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
        </form>
    </div>
</body>
</html>

<?php
if (isset($_POST['next'])) {
    $_SESSION['firstname'] = $_POST['firstname'];
    $_SESSION['middlename'] = $_POST['middlename'];
    $_SESSION['lastname'] = $_POST['lastname'];
    $_SESSION['bdate'] = $_POST['bdate'];
    $_SESSION['contact'] = $_POST['contact'];
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['office'] = $_POST['office'];
    $_SESSION['role'] = $_POST['role'];
    $_SESSION['supdoctype'] = $_POST['supdoctype'];
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['password'] = $_POST['password'];

    if (isset($_FILES['supporting_doc']) && $_FILES['supporting_doc']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/upload/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        $ext = pathinfo($_FILES['supporting_doc']['name'], PATHINFO_EXTENSION);
        $firstname = strtolower(trim($_POST['firstname']));
        $lastname = strtolower(trim($_POST['lastname']));
        $filename = $firstname . '_' . $lastname . '_docs.' . $ext;
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['supporting_doc']['tmp_name'], $targetFile)) {
            $_SESSION['docs_path'] = 'upload/' . $filename;
        } else {
            $_SESSION['upload_error'] = 'Failed to upload image. Please try again.';
        }
    }

    header('Location: conreq.php');
    exit;
}
?>

