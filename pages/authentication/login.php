<?php
session_start();

if (isset($_POST["login"])){

    if(!empty($_POST["username"]) && !empty($_POST["password"])){
    
    $_SESSION["username"] = $_POST["username"];
    $_SESSION["password"] = $_POST["password"];
    header("Location: ../index.html");
    }
    else{
        echo "Please fill in all fields.";
    }
}
?>