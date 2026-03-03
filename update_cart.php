<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];
$pid = (int)$_POST['product_id'];
$change = (int)$_POST['change'];

$q = mysqli_fetch_assoc(mysqli_query($conn,"SELECT quantity FROM cart WHERE user_id=$user_id AND product_id=$pid"));
$new_qty = $q['quantity'] + $change;
if($new_qty < 1) $new_qty = 1;

mysqli_query($conn,"UPDATE cart SET quantity=$new_qty WHERE user_id=$user_id AND product_id=$pid");

/* Recalculate total */
$sql = "
SELECT p.product_price, c.quantity
FROM cart c
LEFT JOIN products p ON c.product_id=p.id
WHERE c.user_id=$user_id
";
$res=mysqli_query($conn,$sql);
$total=0;
while($r=mysqli_fetch_assoc($res)){
$total += $r['product_price']*$r['quantity'];
}

echo json_encode([
    "qty"=>$new_qty,
    "total"=>number_format($total,2)
]);
