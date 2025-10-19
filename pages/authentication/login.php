<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to InnoVision</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="post">
            <h2>Login to InnoVision</h2>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" name="login" value="login">Login</button>
            
            <div class="register-link">
                <p>Don't have an account? <a href="userregister.php">Register here</a></p>
            </div>
        </form>
    </div>
</body>
</html>

<?php
if (isset($_POST["login"])){

    if(!empty($_POST["username"]) && !empty($_POST["password"])){
     
    $_SESSION["username"] = $_POST["username"];
    $_SESSION["password"] = $_POST["password"];

    exit;
    }
    else{
        echo "Please fill in all fields.";
    }
}
?>