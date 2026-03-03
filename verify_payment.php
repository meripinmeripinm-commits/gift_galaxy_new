<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];

$payment_id = $_POST['payment_id'];
$amount = (float)$_POST['amount'];
$product_id = (int)$_POST['product_id'];

/* Create order */
mysqli_query($conn,"INSERT INTO orders 
(user_id, status, payment_method, total_amount, created_at)
VALUES ($user_id,'Paid','Online',$amount,NOW())");

$order_id = mysqli_insert_id($conn);

/* Insert order item */
mysqli_query($conn,"INSERT INTO order_items_gg
(order_id, product_id, quantity, price)
VALUES ($order_id,$product_id,1,$amount)");

echo "success";
