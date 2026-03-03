<?php
session_start();
include 'config.php';

$user_id  = $_SESSION['user_id'] ?? null;
if(!$user_id){
    header("Location: login.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $product_id = (int)$_POST['product_id'];
    $type = $_POST['type'] ?? 'self';
    $price = $_POST['price'] ?? 0;
    $receiver_name = mysqli_real_escape_string($conn,$_POST['receiver_name']);
    $receiver_phone = mysqli_real_escape_string($conn,$_POST['receiver_phone']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    /* Insert order */
    mysqli_query($conn,"INSERT INTO orders (user_id, status, payment_method, total_amount, created_at) VALUES ($user_id,'Pending','Online',$price,NOW())");
    $order_id = mysqli_insert_id($conn);

    /* Optionally insert order_items_gg */
    mysqli_query($conn,"INSERT INTO order_items_gg (order_id, product_id, quantity, price) VALUES ($order_id,$product_id,1,$price)");

    /* Redirect to payment page (or show confirmation) */
    echo "Order #$order_id created. Implement payment gateway here.";
}
?>
