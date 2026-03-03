<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];

mysqli_query($conn,"
    UPDATE users 
    SET last_active=NOW() 
    WHERE id='$user_id'
");
?>
