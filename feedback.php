<?php
include 'config.php';

$msg = '';
if(isset($_POST['submit_feedback'])){
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $message = mysqli_real_escape_string($conn,$_POST['message']);

    if($name && $email && $message){
        mysqli_query($conn,"INSERT INTO feedback (name,email,message) VALUES ('$name','$email','$message')");
        $msg = "Thank you! Your feedback has been submitted.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Feedback | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:'Segoe UI',sans-serif;background:#f5f6fb;margin:0;padding:0;}
.container{max-width:600px;margin:50px auto;background:#fff;padding:30px;border-radius:18px;box-shadow:0 15px 50px rgba(0,0,0,.1);}
h2{text-align:center;color:#50207A;}
input,textarea{width:100%;padding:12px;margin:10px 0;border-radius:10px;border:1px solid #ccc;font-size:14px;}
button{padding:12px 0;width:100%;border:none;border-radius:12px;background:#50207A;color:#fff;font-size:16px;font-weight:700;cursor:pointer;}
.msg{color:green;text-align:center;}
</style>
</head>
<body>

<div class="container">
<h2>Send Us Your Feedback</h2>
<?php if($msg) echo "<p class='msg'>$msg</p>"; ?>
<form method="post">
<input type="text" name="name" placeholder="Your Name" required>
<input type="email" name="email" placeholder="Your Email" required>
<textarea name="message" placeholder="Your Message" rows="5" required></textarea>
<button type="submit" name="submit_feedback">Submit Feedback</button>
</form>
</div>

</body>
</html>
