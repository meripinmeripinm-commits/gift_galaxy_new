<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Fetch user chats (example query, adjust as per your chat table)
$user_id = $_SESSION['user_id'];
$chats = mysqli_query($conn, "SELECT * FROM chats WHERE sender_id=$user_id OR receiver_id=$user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Chats - Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body{ margin:0; font-family:'Segoe UI',sans-serif; background:#f5f6fb; }
header{ background:#50207A; padding:15px 20px; color:#fff; display:flex; align-items:center; gap:10px; }
header h2{ margin:0; flex:1; font-size:22px; }
.back-btn{ background:#fff; color:#50207A; border:none; padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:bold; }
.container{ max-width:600px; margin:20px auto; background:#fff; border-radius:12px; padding:15px; box-shadow:0 5px 20px rgba(0,0,0,.1); }
.chat-msg{ padding:10px 12px; margin-bottom:10px; border-radius:10px; background:#f2f2f2; }
.chat-msg.self{ background:#D646FF; color:#fff; text-align:right; }
</style>
</head>
<body>
<header>
  <button class="back-btn" onclick="window.location='index.php';"><i class="fa-solid fa-arrow-left"></i> Back</button>
  <h2>Chats</h2>
</header>

<div class="container">
<?php if(mysqli_num_rows($chats) > 0){
    while($c = mysqli_fetch_assoc($chats)){
        $self = ($c['sender_id'] == $user_id) ? 'self' : '';
        echo "<div class='chat-msg $self'>" . htmlspecialchars($c['message']) . "</div>";
    }
} else {
    echo "<div>No chats yet. Start a conversation!</div>";
} ?>
</div>
</body>
</html>
