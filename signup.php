<?php
session_start();
include 'config.php';

$message = "";

if (isset($_POST['signup_btn'])) {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($username=="" || $email=="" || $password=="") {
        $message = "Please fill all fields!";
    } else {

        $username = mysqli_real_escape_string($conn, $username);
        $email    = mysqli_real_escape_string($conn, $email);

        // Check if username or email already exists
        $check = mysqli_query($conn,"
            SELECT id FROM users
            WHERE username='$username' OR email='$email'
            LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            $message = "Username or Email already exists!";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            mysqli_query($conn,"
                INSERT INTO users (username, email, password)
                VALUES ('$username', '$email', '$hashed')
            ");

            $_SESSION['user_id']  = mysqli_insert_id($conn);
            $_SESSION['username'] = $username;

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Signup | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    margin:0;
    font-family:Segoe UI, sans-serif;
    background:#f5f6fb;
}
.box{
    max-width:440px;
    margin:80px auto;
    background:#fff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 15px 30px rgba(0,0,0,.1);
}
h2{text-align:center;color:#50207A}
input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
}
.pw-box{position:relative}
.eye{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
}
button{
    width:100%;
    padding:12px;
    background:#50207A;
    color:#fff;
    border:none;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}
.msg{text-align:center;color:red}
.link{text-align:center;margin-top:15px}
.link a{color:#50207A;text-decoration:none;font-weight:600}
</style>
</head>

<body>

<div class="box">
<h2>Create Account</h2>

<?php if($message!=""){ ?>
<p class="msg"><?= $message ?></p>
<?php } ?>

<form method="POST">
<input type="text" name="username" placeholder="Username" required>
<input type="email" name="email" placeholder="Email address">
<input type="phone" name="phone" placeholder="Phone number" required>
<div class="pw-box">
<input type="password" name="password" id="password" placeholder="Password" required>
<span class="eye" onclick="togglePw()">👁</span>
</div>

<button name="signup_btn">Sign Up</button>
</form>

<div class="link">
Already have an account? <a href="login.php">Login</a>
</div>

</div>

<script>
function togglePw(){
    const pw = document.getElementById("password");
    pw.type = pw.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
