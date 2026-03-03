<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['payment_id'])){
    die("Invalid access");
}

$payment_id = mysqli_real_escape_string($conn,$_GET['payment_id']);
$name = mysqli_real_escape_string($conn,$_GET['name']);
$phone = mysqli_real_escape_string($conn,$_GET['phone']);
$address = mysqli_real_escape_string($conn,$_GET['address']);
$city = mysqli_real_escape_string($conn,$_GET['city']);
$state = mysqli_real_escape_string($conn,$_GET['state']);
$pincode = mysqli_real_escape_string($conn,$_GET['pincode']);

/* Get cart items */
$cart_query = mysqli_query($conn,"
SELECT c.product_id, c.quantity,
       p.product_price, p.shipping_charge,
       p.seller_id
FROM cart c
JOIN products p ON c.product_id=p.id
WHERE c.user_id='$user_id'
");

if(mysqli_num_rows($cart_query)==0){
    die("Cart empty");
}

$subtotal=0;
$total_shipping=0;

while($row=mysqli_fetch_assoc($cart_query)){
    $subtotal += $row['product_price'] * $row['quantity'];
    $total_shipping += $row['shipping_charge'] * $row['quantity'];
}

$grand_total = $subtotal + $total_shipping;

/* Insert order */
mysqli_query($conn,"
INSERT INTO orders_new
(user_id, phone, address, city, state, pincode, payment_method, payment_status, total_amount, created_at)
VALUES
('$user_id','$phone','$address','$city','$state','$pincode','Razorpay','Paid','$grand_total',NOW())
");

$order_id = mysqli_insert_id($conn);

/* Insert order items */
$cart_query = mysqli_query($conn,"
SELECT c.product_id, c.quantity,
       p.product_price, p.seller_id
FROM cart c
JOIN products p ON c.product_id=p.id
WHERE c.user_id='$user_id'
");

while($item=mysqli_fetch_assoc($cart_query)){

    mysqli_query($conn,"
    INSERT INTO order_items_gg
    (order_id, product_id, seller_id, quantity, price)
    VALUES
    ('$order_id',
     '".$item['product_id']."',
     '".$item['seller_id']."',
     '".$item['quantity']."',
     '".$item['product_price']."')
    ");
}

/* Clear cart */
mysqli_query($conn,"DELETE FROM cart WHERE user_id='$user_id'");

echo "<script>
alert('Order placed successfully!');
window.location.href='index.php';
</script>";
?>
