<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? 0;

header('Content-Type: application/json');

if(!$user_id){
    echo json_encode(['count'=>0,'chats'=>[]]);
    exit;
}

// Count unread messages
$res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM messages WHERE to_user=$user_id AND is_read=0");
$total = mysqli_fetch_assoc($res)['total'] ?? 0;

// Get last messages per friend (example schema)
$chatsRes = mysqli_query($conn, "
    SELECT m.*, u.username, u.avatar
    FROM messages m
    JOIN users u ON (u.id = m.from_user)
    WHERE m.to_user=$user_id
    GROUP BY m.from_user
    ORDER BY m.created_at DESC
    LIMIT 5
");
$chats = [];
while($row=mysqli_fetch_assoc($chatsRes)){
    $chats[] = [
        'name'=>$row['username'],
        'avatar'=>$row['avatar'],
        'last_msg'=>$row['message'],
        'link'=>'chat_detail.php?user='.$row['from_user']
    ];
}

echo json_encode(['count'=>$total,'chats'=>$chats]);
