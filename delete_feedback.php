<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin_id'])){
    die('Unauthorized');
}

if(isset($_POST['id'])){
    $id = (int)$_POST['id'];
    if(mysqli_query($conn,"DELETE FROM feedback WHERE id=$id")){
        echo 'success';
    } else {
        echo mysqli_error($conn);
    }
} else {
    echo 'Invalid request';
}
?>
