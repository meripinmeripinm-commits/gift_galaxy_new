<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['status'=>'error','msg'=>'Invalid data']);
    exit;
}

$product_id = (int)$data['product_id'];
$wrap_id = (int)$data['wrap_id'];
$message = mysqli_real_escape_string($conn, $data['message']);
$from_name = mysqli_real_escape_string($conn, $data['from_name']);

$delivery_name = mysqli_real_escape_string($conn, $data['delivery_name']);
$delivery_phone = mysqli_real_escape_string($conn, $data['delivery_phone']);
$delivery_address = mysqli_real_escape_string($conn, $data['delivery_address']);
$delivery_landmark = mysqli_real_escape_string($conn, $data['delivery_landmark']);
$delivery_city = mysqli_real_escape_string($conn, $data['delivery_city']);
$delivery_state = mysqli_real_escape_string($conn, $data['delivery_state']);
$delivery_pincode = mysqli_real_escape_string($conn, $data['delivery_pincode']);

$payment_method = $data['payment_method'];
$total_price = (float)$data['total_price'];

$sql = "
INSERT INTO orders
(product_id, wrap_id, message, from_name,
 delivery_name, delivery_phone, delivery_address,
 delivery_landmark, delivery_city, delivery_state,
 delivery_pincode, payment_method, total_price)
VALUES
('$product_id','$wrap_id','$message','$from_name',
 '$delivery_name','$delivery_phone','$delivery_address',
 '$delivery_landmark','$delivery_city','$delivery_state',
 '$delivery_pincode','$payment_method','$total_price')
";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','msg'=>mysqli_error($conn)]);
}
