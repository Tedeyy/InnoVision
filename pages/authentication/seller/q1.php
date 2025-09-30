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
    <form action="q1.php" method="post">
        <h2>Enter your name</h2>
        <label for="username">First Name</label><br>
        <input type="text" id="username" name="firstname" required>
        <br><br>
        <label for="password">Middle Name</label><br>
        <input type="text" id="username" name="middlename" required>
        <br><br>
        <label for="password">Last Name</label><br>
        <input type="text" id="username" name="lastname" required>
        <br><br>
        <button type="submit" name="Next" value="proceed">Next</button>
        <br>
    </form>
</body>
</html>

<?php
if (isset($_POST["q1"])){

    if(!empty($_POST["firstname"]) && !empty($_POST["middlename"]) && !empty($_POST["lastname"])){
    
    $_SESSION["firstname"] = $_POST["firstname"];
    $_SESSION["middlename"] = $_POST["middlename"];
    $_SESSION["lastname"] = $_POST["lastname"];
   
    header("Location: q2.php");
    exit;
    }
    else{
        echo "Please fill in all fields.";
    }
}
?>