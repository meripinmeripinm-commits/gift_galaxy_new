<?php
session_start();
include 'config.php';

$message = "";

if (isset($_POST['next'])) {

    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));

    if ($phone == "") {
        $message = "Please enter your phone number.";
    } else {
        $q = mysqli_query($conn, "SELECT id FROM users WHERE phone='$phone'");

        if (mysqli_num_rows($q) === 1) {
            $_SESSION['reset_phone'] = $phone;
            header("Location: reset_password.php");
            exit;
        } else {
            $message = "This phone number is not registered.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password | Gift Galaxy</title>
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
    margin:0 0 10px;
    text-align:center;
    color:#50207A;
}
p.sub{
    text-align:center;
    color:#666;
    font-size:14px;
    margin-bottom:25px;
}
input{
    width:100%;
    padding:13px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:15px;
    margin-bottom:15px;
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
.link{
    text-align:center;
    margin-top:18px;
}
.link a{color:#50207A;font-weight:600}
</style>
</head>

<body>

<div class="wrapper">
<div class="card">

<h2>Forgot Password</h2>
<p class="sub">Enter your registered phone number to reset your password</p>

<?php if($message!=""){ ?>
<div class="msg"><?= $message ?></div>
<?php } ?>

<form method="POST">
<input type="text" name="phone" placeholder="Registered Phone Number" required>
<button type="submit" name="next">Continue</button>
</form>

<div class="link">
<a href="login.php">← Back to Login</a>
</div>

</div>
</div>

</body>
</html>
