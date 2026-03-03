<?php
session_start();
include 'config.php';

if(isset($_POST['signup'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if($password !== $confirm){
        $error = "Passwords do not match!";
    } else {
        // Check if email exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if(mysqli_num_rows($check)){
            $error = "Email already registered!";
        } else {
            // Hash the password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (email,password,is_admin) VALUES ('$email','$hash',1)");
            $success = "Admin account created! You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Signup | Gift Galaxy</title>
</head>
<body>
<h2>Admin Signup</h2>
<?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<?php if(isset($success)) echo "<p style='color:green'>$success</p>"; ?>
<form method="POST">
    <label>Email:</label><br>
    <input type="email" name="email" required><br>
    <label>Password:</label><br>
    <input type="password" name="password" required><br>
    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>
    <button type="submit" name="signup">Sign Up</button>
</form>
<p>Already have an account? <a href="admin_login.php">Login</a></p>
</body>
</html>
