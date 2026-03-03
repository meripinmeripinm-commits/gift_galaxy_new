<?php
session_start();
include 'config.php';

if (!isset($_SESSION['reset_phone'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if (isset($_POST['reset'])) {

    $pw1 = $_POST['password'];
    $pw2 = $_POST['confirm'];

    if ($pw1 == "" || $pw2 == "") {
        $message = "All fields are required.";
    } elseif (strlen($pw1) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($pw1 !== $pw2) {
        $message = "Passwords do not match.";
    } else {

        $hash = password_hash($pw1, PASSWORD_DEFAULT);
        $phone = $_SESSION['reset_phone'];

        mysqli_query($conn,"
            UPDATE users SET password='$hash'
            WHERE phone='$phone'
        ");

        unset($_SESSION['reset_phone']);
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Password | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    margin:0;
    font-family:Segoe UI, sans-serif;
    background:#f2f3f7;
}
.wrapper{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.card{
    width:100%;
    max-width:420px;
    background:#fff;
    padding:35px;
    border-radius:16px;
    box-shadow:0 15px 35px rgba(0,0,0,.12);
}
h2{
    text-align:center;
    color:#50207A;
    margin-bottom:10px;
}
p.sub{
    text-align:center;
    color:#666;
    font-size:14px;
    margin-bottom:25px;
}
.input-box{
    position:relative;
    margin-bottom:15px;
}
input{
    width:100%;
    padding:13px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:15px;
}
.eye{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    font-size:18px;
}
button{
    width:100%;
    padding:13px;
    background:#50207A;
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}
button:hover{background:#6F5BB5}
.msg{
    background:#ffe0e0;
    color:#a00;
    padding:12px;
    border-radius:8px;
    text-align:center;
    margin-bottom:15px;
    font-weight:600;
}
</style>
</head>

<body>

<div class="wrapper">
<div class="card">

<h2>Reset Password</h2>
<p class="sub">Create a new secure password</p>

<?php if($message!=""){ ?>
<div class="msg"><?= $message ?></div>
<?php } ?>

<form method="POST">

<div class="input-box">
<input type="password" id="pw1" name="password" placeholder="New Password" required>
<span class="eye" onclick="toggle('pw1')">👁</span>
</div>

<div class="input-box">
<input type="password" id="pw2" name="confirm" placeholder="Confirm Password" required>
<span class="eye" onclick="toggle('pw2')">👁</span>
</div>

<button type="submit" name="reset">Change Password</button>
</form>

</div>
</div>

<script>
function toggle(id){
    const x = document.getElementById(id);
    x.type = x.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
