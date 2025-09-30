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
        First Name :<br>
        <input type="text" name="firstname" required>
        <br><br>
        Middle Name :<br>
        <input type="text" name="middlename" required>
        <br><br>
        Last Name :<br>
        <input type="text" name="lastname" required>
        <br><br>
        <button type="submit" name="proceed" value="proceed">Proceed</button>
        <br>
    </form>
</body>
</html>

<?php
    //Testing
    //echo $_SESSION["firstname"] . "<br>";
    //echo $_SESSION["middlename"] . "<br>";
    //echo $_SESSION["lastname"] . "<br>";
?>