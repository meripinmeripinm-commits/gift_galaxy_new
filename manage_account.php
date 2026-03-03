<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php?redirect=manage_account.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg_account = $msg_password = "";

$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));

/* ---------- Update Account Info ---------- */
if(isset($_POST['update_account'])){
    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $email    = mysqli_real_escape_string($conn,$_POST['email']);
    $phone    = mysqli_real_escape_string($conn,$_POST['phone']);

    // Check for duplicates
    $check = mysqli_query($conn,"SELECT id FROM users WHERE (username='$username' OR email='$email') AND id!='$user_id'");
    if(mysqli_num_rows($check)>0){
        $msg_account = "Username or email already in use!";
    } else {
        mysqli_query($conn,"UPDATE users SET username='$username', email='$email', phone='$phone' WHERE id='$user_id'");
        $_SESSION['username']=$username;
        $msg_account="Account updated successfully!";
        $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));
    }
}

/* ---------- Change Password ---------- */
if(isset($_POST['change_password'])){
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if(!password_verify($current,$user['password'])){
        $msg_password = "Current password incorrect!";
    } elseif($new !== $confirm){
        $msg_password = "New passwords do not match!";
    } else {
        $hash = password_hash($new,PASSWORD_DEFAULT);
        mysqli_query($conn,"UPDATE users SET password='$hash' WHERE id='$user_id'");
        $msg_password="Password changed successfully!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Manage Account | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{margin:0;font-family:Segoe UI,sans-serif;background:#0b0b1e;color:#fff;}
.container{max-width:900px;margin:40px auto;padding:30px;background:#1a1a2e;border-radius:20px;box-shadow:0 0 50px rgba(255,255,255,0.1);}
h2{color:#f3c623;text-shadow:0 0 10px #f3c623,0 0 20px #ff69b4;margin-bottom:20px;}
form{margin-bottom:40px;}
input{width:100%;padding:12px;margin:8px 0;border-radius:10px;border:none;font-size:15px;}
button{padding:12px 20px;background:#ff69b4;color:#fff;border:none;border-radius:50px;font-weight:600;cursor:pointer;transition:0.3s;}
button:hover{background:#f3c623;color:#0b0b1e;}
.msg{color:#f3c623;font-weight:600;margin-bottom:12px;}
.section{margin-bottom:50px;}
.tc-box{padding:20px;background:#2a2a3e;border-radius:20px;box-shadow:0 0 20px rgba(255,255,255,0.1);line-height:1.5;animation:glow 3s infinite alternate;}
@keyframes glow{
    0%{box-shadow:0 0 10px #f3c623;}
    50%{box-shadow:0 0 20px #ff69b4;}
    100%{box-shadow:0 0 10px #f3c623;}
}
</style>
</head>
<body>
<div class="container">

<h2>Manage Account</h2>

<!-- ACCOUNT INFO -->
<div class="section">
<h3>Account Info</h3>
<?php if($msg_account) echo "<div class='msg'>$msg_account</div>"; ?>
<form method="POST">
<input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" placeholder="Username" required>
<input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="Email">
<input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Phone">
<button name="update_account">Update Account</button>
</form>
</div>

<!-- CHANGE PASSWORD -->
<div class="section">
<h3>Change Password</h3>
<?php if($msg_password) echo "<div class='msg'>$msg_password</div>"; ?>
<form method="POST">
<input type="password" name="current_password" placeholder="Current Password" required>
<input type="password" name="new_password" placeholder="New Password" required>
<input type="password" name="confirm_password" placeholder="Confirm New Password" required>
<button name="change_password">Change Password</button>
</form>
</div>

<!-- TERMS & CONDITIONS -->
<div class="section">
<h3>Terms & Conditions</h3>
<div class="tc-box">
  <h4 style="text-align:center;color:#ff69b4;margin-bottom:15px;">📝 Important Terms & Conditions</h4>
  <p>Welcome to <strong>Gift Galaxy</strong>! Our platform is designed to provide a magical gifting experience with personalized options and 3D animated previews. Before making a purchase, please carefully review the following:</p>
  
  <ul style="margin-left:20px;line-height:1.6;">
    <li>🎁 <strong>Order Review:</strong> Check your product selection, quantity, gift message, and shipping details carefully. Once confirmed, orders are processed immediately.</li>
    <li>🚫 <strong>No Cancellations:</strong> As our service is still in the early stages, we do not accept cancellations once the order is placed.</li>
    <li>💸 <strong>No Refunds:</strong> sorry to say ! All purchases are final. Refunds or exchanges will not be provided for processed orders BECAUSE we in first level (GG) but well provide it verry soon .</li>
    <li>✨ <strong>Gift Customization:</strong> If you choose personalized messages or gift wraps, make sure all inputs are correct. Errors in customization cannot be corrected after submission.</li>
    <li>🔒 <strong>Data & Privacy:</strong> Your personal and shipping information will only be used for order fulfillment and will not be shared with third parties.</li>
    <li>🎨 <strong>Experience:</strong> Enjoy 3D live animations of gifts on the platform. These animations are for preview purposes and may slightly differ from the physical product.</li>
  </ul>

  <p style="margin-top:15px;">By proceeding with your purchase, you acknowledge that you have read, understood, and agreed to these terms. Enjoy a magical gifting journey with <strong>Gift Galaxy</strong>!</p>
</div>
</div>

</div>
</body>
</html>
