<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAT Registration Form</title>
    <link rel="stylesheet" href="req.css">
</head>
<body>
    <div class="regform">
        <form action="conreq.php" method="post" enctype="multipart/form-data">
            <h2>BAT Registration Details</h2>
            Full Name<br>
            <input type="text" name="name" required>
            <br><br>
            Email Address<br>
            <input type="email" name="email" required>
            <br><br>
            Assigned Barangay<br>
            <input type="text" name="assigned_barangay">
            <br><br>
            Supporting Document Image<br>
            <input type="file" name="supporting_doc" accept="image/*" required>
            <br><br>
            <div id="acc">Login Credentials<br>
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
    $_SESSION['name'] = $_POST['name'];
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['assigned_barangay'] = $_POST['assigned_barangay'];
    $_SESSION['password'] = $_POST['password'];

    if (isset($_FILES['supporting_doc']) && $_FILES['supporting_doc']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/upload/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        $ext = pathinfo($_FILES['supporting_doc']['name'], PATHINFO_EXTENSION);
        $nameSlug = strtolower(trim(preg_replace('/\s+/', '_', $_POST['name'])));
        $filename = $nameSlug . '_docs.' . $ext;
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

