<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php?redirect=chat.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Update last seen for online status */
mysqli_query($conn,"UPDATE users SET last_seen=NOW() WHERE id=$user_id");

/* Escape function */
function e($str){
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>GG Chats</title>

<style>

/* ===========================
   GLOBAL
=========================== */
body{
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#141e30,#243b55);
    color:white;
}

/* ===========================
   GLASS HEADER SECTION
=========================== */

.ggc-wrapper{
    padding:40px 20px;
    display:flex;
    justify-content:center;
}

.ggc-glass{
    width:100%;
    max-width:800px;
    padding:40px 20px;
    border-radius:20px;
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border:1px solid rgba(255,255,255,0.15);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    text-align:center;
}

/* Glowing Title */
.ggc-title{
    font-size:40px;
    font-weight:700;
    letter-spacing:2px;
    margin-bottom:15px;
    text-transform:uppercase;
    color:#fff;
    text-shadow:
        0 0 5px #00f7ff,
        0 0 10px #00f7ff,
        0 0 20px #00f7ff,
        0 0 40px #00f7ff;
    animation: glowPulse 2s infinite alternate;
}

@keyframes glowPulse{
    from{ text-shadow:0 0 10px #00f7ff;}
    to{ text-shadow:0 0 30px #00f7ff;}
}

/* Typing Animation */
.typing-container{
    display:inline-block;
    font-size:18px;
    letter-spacing:1px;
    border-right:2px solid #00f7ff;
    white-space:nowrap;
    overflow:hidden;
    animation: typing 4s steps(40,end) infinite alternate,
               blink 0.8s infinite;
}

@keyframes typing{
    from{ width:0 }
    to{ width:100% }
}

@keyframes blink{
    50%{ border-color:transparent }
}

/* ===========================
   OLD CHAT CONTAINER (UNCHANGED)
=========================== */

.chat-container{
    max-width:1000px;
    margin:20px auto;
    background:white;
    color:black;
    border-radius:15px;
    padding:20px;
}

/* Keep your old design untouched below */

</style>
</head>

<body>

<!-- ===========================
     GG CHATS GLASS SECTION
=========================== -->
<div class="ggc-wrapper">
    <div class="ggc-glass">
        <div class="ggc-title">💬 GG Chats</div>
        <div class="typing-container">
            Very Soon Chat World Will Appear...
        </div>
    </div>
</div>

<!-- ===========================
     YOUR OLD CHAT SYSTEM BELOW
     (NO LOGIC CHANGED)
=========================== -->

<div class="chat-container">

<?php
/* ===========================
   YOUR EXISTING CHAT CODE STARTS HERE
   KEEP EVERYTHING SAME
=========================== */

// Example placeholder — keep your original chat layout here

echo "<h3>Your Chat System Continues Here...</h3>";

?>

</div>

</body>
</html>
