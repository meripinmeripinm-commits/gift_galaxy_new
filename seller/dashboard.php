<?php
session_start();
include '../config.php';

/* ONLY SELLER ACCESS */
if(!isset($_SESSION['user_id']) || $_SESSION['is_seller'] != 1){
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

/* STATS */
$total_products = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM products WHERE seller_id='$seller_id'")
)['total'];

$total_orders = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) AS total 
        FROM orders o 
        JOIN products p ON o.product_id=p.id 
        WHERE p.seller_id='$seller_id'
    ")
)['total'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Seller Dashboard | Gift Galaxy</title>
<style>
body{margin:0;font-family:Segoe UI;background:#f4f6fb;}
.sidebar{
    width:220px;height:100vh;position:fixed;
    background:#50207A;color:#fff;padding:20px;
}
.sidebar h2{text-align:center;margin-bottom:30px;}
.sidebar a{
    display:block;color:#D6B9FC;text-decoration:none;
    padding:12px;border-radius:6px;margin-bottom:8px;
}
.sidebar a:hover{background:#6F5BB5;color:#fff;}
.main{margin-left:240px;padding:30px;}
.card{
    background:#fff;padding:25px;border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.card h2{color:#50207A;margin:0;}
.card p{font-size:28px;font-weight:bold;}
</style>
</head>

<body>

<div class="sidebar">
    <h2>Seller Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">My Products</a>
    <a href="orders.php">My Orders</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main">
    <h1>Welcome Seller 👋</h1>

    <div class="card">
        <h2>📦 My Products</h2>
        <p><?php echo $total_products; ?></p>
    </div>

    <div class="card">
        <h2>🧾 My Orders</h2>
        <p><?php echo $total_orders; ?></p>
    </div>
</div>

</body>
</html>
