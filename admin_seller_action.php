<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$seller_id = intval($data['seller_id']);
$action = $data['action'];

if($action==='approve'){
    mysqli_query($conn,"UPDATE sellers SET status='approved' WHERE id='$seller_id'");
    echo json_encode(['success'=>'Seller approved','status'=>'Approved']);
}
elseif($action==='block'){
    mysqli_query($conn,"UPDATE sellers SET status='blocked' WHERE id='$seller_id'");
    echo json_encode(['success'=>'Seller blocked','status'=>'Blocked']);
}
elseif($action==='remove'){
    mysqli_query($conn,"DELETE FROM sellers WHERE id='$seller_id'");
    echo json_encode(['success'=>'Seller removed']);
}
else{
    echo json_encode(['error'=>'Invalid action']);
}
