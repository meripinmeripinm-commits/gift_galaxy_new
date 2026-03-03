<?php
session_start();
include 'config.php';

$redirect = $_GET['redirect'] ?? 'index.php';
$message  = "";

if (isset($_POST['login_btn'])) {

    $login    = trim($_POST['login']);
    $password = trim($_POST['password']);

    if ($login == "" || $password == "") {
        $message = "Please fill all fields!";
    } else {

        $login = mysqli_real_escape_string($conn, $login);

        /* ---------- USER LOGIN CHECK ---------- */
        $q = mysqli_query($conn, "
            SELECT * FROM users
            WHERE username='$login' OR email='$login'
            LIMIT 1
        ");

        if ($q && mysqli_num_rows($q) == 1) {

            $user = mysqli_fetch_assoc($q);

            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                header("Location: $redirect");
                exit;

            } else {
                $message = "Invalid username or password!";
            }

        } else {

            /* ---------- ADMIN FALLBACK LOGIN ---------- */
            $qAdmin = mysqli_query($conn,"
                SELECT * FROM admin
                WHERE username='$login'
                LIMIT 1
            ");

            if ($qAdmin && mysqli_num_rows($qAdmin) === 1) {

                $admin = mysqli_fetch_assoc($qAdmin);

                // plain password check (as you requested)
                if ($password === $admin['password']) {

                    $_SESSION['admin_id']   = $admin['id'];
                    $_SESSION['admin_name'] = $admin['username'];

                    header("Location: admin_dashboard.php");
                    exit;
                }
            }

            $message = "Invalid username or password!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Login | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#f5f6fb;
}
.box{
    max-width:420px;
    margin:90px auto;
    background:#fff;
    padding:40px 35px;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,0.1);
    text-align:center;
}
.logo{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
    margin-bottom:15px;
    cursor:pointer;
}
.logo img{height:65px;}
.logo span{font-size:32px;font-weight:bold;color:#50207A;}
.tagline{font-size:16px;color:#888;margin-bottom:25px;}
h2{color:#50207A;margin-bottom:20px;}
input{
    width:100%;
    padding:12px 14px;
    margin-bottom:15px;
    border-radius:12px;
    border:1px solid #ccc;
    font-size:15px;
}
input:focus{border-color:#50207A;outline:none;}
.pw-box{position:relative;}
.eye{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
}
button{
    width:100%;
    padding:14px;
    background:#50207A;
    color:#fff;
    border:none;
    border-radius:50px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
}
.msg{
    color:#a00;
    font-weight:600;
    margin-bottom:15px;
}
.link{
    margin-top:20px;
    font-size:14px;
}
.link a{
    color:#50207A;
    text-decoration:none;
    font-weight:600;
}
</style>
</head>

<body>

<div class="box">
    <div class="logo" onclick="window.location='index.php';">
        <img src="assets/images/logo.png">
        <span>Gift Galaxy</span>
    </div>

    <div class="tagline">Find the perfect surprise 🎁</div>

    <h2>Login</h2>

    <?php if($message!=""){ ?>
        <div class="msg"><?= $message ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="login" placeholder="Username or Email" required>

        <div class="pw-box">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span class="eye" onclick="togglePw()">👁</span>
        </div>

        <button name="login_btn">Login</button>
    </form>

    <div class="link">
        <a href="forgot_password.php">Forgot password?</a><br><br>
        New user? <a href="signup.php">Create account</a>
    </div>
</div>

<script>
function togglePw(){
    const pw=document.getElementById("password");
    pw.type = pw.type==="password" ? "text" : "password";
}
</script>

</body>
</html>
