<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])) exit();

$sender   = $_SESSION['user_id'];
$receiver = $_POST['receiver_id'] ?? 0;

if($receiver == 0) exit();

$sql = mysqli_query($conn,"
SELECT * FROM messages
WHERE 
(sender_id = $sender AND receiver_id = $receiver)
OR
(sender_id = $receiver AND receiver_id = $sender)
ORDER BY id ASC
");

while($row = mysqli_fetch_assoc($sql)){

    $class = ($row['sender_id'] == $sender) ? "sent" : "received";

    echo "<div class='msg $class'>";
    echo htmlspecialchars($row['message']);
    echo "</div>";

    // mark as read
    if($row['receiver_id'] == $sender && $row['seen'] == 0){
        mysqli_query($conn,"UPDATE messages SET seen=1 WHERE id=".$row['id']);
    }
}
?>
