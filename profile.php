<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
/* FETCH USER DATA */
$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* CHECK REQUIRED FIELDS */
$required_fields = ['username', 'email', 'phone', 'address', 'district', 'state', 'country', 'pincode'];

$required_filled = true;
foreach ($required_fields as $field) {
    if (!isset($user[$field]) || trim($user[$field]) === '') {
        $required_filled = false;
        break;
    }
}

/* SUCCESS MESSAGE */
$success = isset($_GET['success']);
?>
<!DOCTYPE html>
<html>
<head>
<title>My Account | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:Segoe UI, sans-serif;
    background:#f2f3f7;
}
.container{
    max-width:1200px;
    margin:40px auto;
    display:grid;
    grid-template-columns:280px 1fr;
    gap:25px;
}
@media(max-width:900px){
    .container{grid-template-columns:1fr}
}
.sidebar,.main{
    background:#fff;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.1);
}
.sidebar{
    padding:25px;
    text-align:center;
}
.avatar{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #50207A;
    cursor:pointer;
}
.menu{
    margin-top:25px;
    text-align:left;
}
.menu a{
    display:flex;
    align-items:center;
    padding:10px 14px;
    border-radius:10px;
    font-weight:600;
    color:#333;
    text-decoration:none;
    margin-bottom:8px;
    transition:0.3s;
}
.menu a i{
    margin-right:8px;
}
.menu a:hover{
    background:#f3e9ff;
    color:#333 !important;
}

/* Start Selling link (no background color) */
.start-selling{
    background: none;
    color: #333 !important;
}
.start-selling:hover{
    background:#f3e9ff;
    color: #333 !important;
}

.main{
    padding:30px 35px;
}
.section{
    margin-bottom:35px;
}
.section h3{
    font-size:20px;
    color:#50207A;
    border-bottom:2px solid #E5CCFF;
    padding-bottom:10px;
}
label{
    display:block;
    font-weight:600;
    margin:15px 0 6px;
}
label span{color:red}
input,textarea{
    width:100%;
    padding:12px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:15px;
}
textarea{height:90px;resize:none}
.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
@media(max-width:600px){
    .row{grid-template-columns:1fr}
}
button{
    background:#50207A;
    color:#fff;
    border:none;
    padding:14px 35px;
    border-radius:50px;
    font-size:16px;
    cursor:pointer;
}
button:hover{background:#D646FF;}
.alert{
    background:#ffe0e0;
    color:#a00;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
    font-weight:600;
}
.success{
    background:#e3ffe8;
    color:#067a1c;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
    font-weight:600;
}
</style>
</head>

<body>

<div class="container">

<!-- SIDEBAR -->
<div class="sidebar">

<form method="POST" action="update_profile.php" enctype="multipart/form-data">
<label>
<img class="avatar"
src="<?= !empty($user['avatar']) ? 'uploads/avatars/'.$user['avatar'] : 'assets/images/user-avatar.png'; ?>">
<input type="file" name="avatar" hidden onchange="this.form.submit()">
</label>
</form>

<h2><?= htmlspecialchars($user['username'] ?? '') ?></h2>
<p><?= htmlspecialchars($user['phone'] ?? '') ?></p>

<!-- Sidebar Menu -->
<div class="menu">
    <a href="orders.php"><i class="fa fa-box"></i> My Orders</a>
    <a href="favorites.php"><i class="fa fa-heart"></i> Wishlist</a>
    <a href="addresses.php"><i class="fa fa-map-marker-alt"></i> Addresses</a>
    <a href="seller.php" class="start-selling" title="Start selling your gifts on GG!">
        <i class="fa fa-store"></i> Start Selling
    </a>
    <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>
</div>

<!-- MAIN -->
<div class="main">

<?php if($success): ?>
<div class="success">✅ Profile updated successfully</div>
<?php endif; ?>

<?php if(!$required_filled): ?>
<div class="alert">⚠ Please complete all required details before continuing.</div>
<?php endif; ?>

<form method="POST" action="update_profile.php">

<div class="section">
<h3>👤 Personal Information</h3>

<label>Username <span>*</span></label>
<input type="text" name="username" required value="<?= htmlspecialchars($user['username'] ?? '') ?>">

<label>Email <span>*</span></label>
<input type="email" name="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">

<label>Phone <span>*</span></label>
<input type="text" name="phone" required value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
</div>

<div class="section">
<h3>📦 Delivery Address</h3>

<label>Full Address <span>*</span></label>
<textarea name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>

<label>Landmark</label>
<input type="text" name="landmark" value="<?= htmlspecialchars($user['landmark'] ?? '') ?>">

<div class="row">
<div>
<label>District <span>*</span></label>
<input type="text" name="district" required value="<?= htmlspecialchars($user['district'] ?? '') ?>">
</div>
<div>
<label>State <span>*</span></label>
<input type="text" name="state" required value="<?= htmlspecialchars($user['state'] ?? '') ?>">
</div>
</div>

<div class="row">
<div>
<label>Country <span>*</span></label>
<input type="text" name="country" required value="<?= htmlspecialchars($user['country'] ?? '') ?>">
</div>
<div>
<label>PIN Code <span>*</span></label>
<input type="text" name="pincode" required value="<?= htmlspecialchars($user['pincode'] ?? '') ?>">
</div>
</div>
</div>

<button type="submit" name="save">Save Profile</button>

</form>
</div>

</div>
</body>
</html>
