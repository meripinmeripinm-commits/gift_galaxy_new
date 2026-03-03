<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch last 10 chats involving this user
$q = mysqli_query($conn, "SELECT c.*, u.user_name, u.image AS user_image
    FROM chats c
    JOIN users u ON u.id = c.sender_id
    WHERE c.sender_id = $user_id OR c.receiver_id = $user_id
    ORDER BY c.created_at DESC
    LIMIT 10");

$chats = [];
while($row = mysqli_fetch_assoc($q)){
    $chats[] = [
        'id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'receiver_id' => $row['receiver_id'],
        'message' => $row['message'],
        'name' => $row['user_name'],
        'image' => !empty($row['user_image']) ? 'uploads/users/'.$row['user_image'] : 'assets/images/default-user.png'
    ];
}

echo json_encode($chats);
