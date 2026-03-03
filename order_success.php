<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_id = $_GET['payment_id'] ?? '';

/* GET CART ITEMS AGAIN */
$cart_items = mysqli_query($conn,"
SELECT p.product_price, c.*
FROM cart c
JOIN products p ON c.product_id = p.id
WHERE c.user_id = $user_id
");

$total_amount = 0;

while($item=mysqli_fetch_assoc($cart_items)){
    $total_amount += $item['product_price'] * $item['quantity'];
}

/* INSERT ORDER */
mysqli_query($conn,"
INSERT INTO orders
(user_id,status,payment_method,total_amount,created_at,payment_id)
VALUES
($user_id,'Paid','Razorpay',$total_amount,NOW(),'$payment_id')
");

$order_id = mysqli_insert_id($conn);

/* INSERT ORDER ITEMS */
$cart_items = mysqli_query($conn,"
SELECT p.product_price, c.*
FROM cart c
JOIN products p ON c.product_id = p.id
WHERE c.user_id = $user_id
");

while($item=mysqli_fetch_assoc($cart_items)){
    mysqli_query($conn,"
    INSERT INTO order_items_gg
    (order_id,product_id,quantity,price)
    VALUES
    ($order_id,{$item['product_id']},{$item['quantity']},{$item['product_price']})
    ");
}

/* CLEAR CART */
mysqli_query($conn,"DELETE FROM cart WHERE user_id=$user_id");

header("Location: order_success.php");
exit;
?>
