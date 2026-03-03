<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])) exit();

$sender   = $_SESSION['user_id'];
$receiver = $_POST['receiver_id'];
$message  = mysqli_real_escape_string($conn, $_POST['message']);

mysqli_query($conn,"
INSERT INTO messages(sender_id, receiver_id, message, seen)
VALUES($sender, $receiver, '$message', 0)
");
?>
