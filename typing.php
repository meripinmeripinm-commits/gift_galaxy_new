<?php
session_start();
include 'config.php';

$convo = intval($_GET['convo']);
$user_id = $_SESSION['user_id'];

$_SESSION['typing_'.$convo] = time();
?>
