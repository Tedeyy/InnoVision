<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to InnoVision</title>
</head>
<body>
    <form action="login.php" method="post">
        <h2>Login</h2>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>
        <button type="submit" name="login" value="login">Login</button>
    </form>
</body>
</html>

<?php
if (isset($_POST["login"])){

    if(!empty($_POST["username"]) && !empty($_POST["password"])){
    
    $_SESSION["username"] = $_POST["username"];
    $_SESSION["password"] = $_POST["password"];
    header("Location: ../../index.html");
    exit;
    }
    else{
        echo "Please fill in all fields.";
    }
}
?>