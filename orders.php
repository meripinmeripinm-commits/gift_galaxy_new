<?php
session_start();
include "config.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Commission Settings */
$admin_percent = 20;
$seller_percent = 80;

/* Fetch User Orders */
$sql = "
SELECT o.*, p.product_name, p.product_image
FROM orders_new o
JOIN products p ON o.product_id = p.id
WHERE o.user_id = $user_id
ORDER BY o.id DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Orders - Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#f5f6fb;
}

.header{
    background:#50207A;
    color:#fff;
    padding:20px;
    text-align:center;
    font-size:26px;
    font-weight:bold;
}

.container{
    max-width:1100px;
    margin:30px auto;
    padding:0 20px;
}

.order-card{
    background:#fff;
    border-radius:18px;
    padding:20px;
    margin-bottom:25px;
    box-shadow:0 12px 30px rgba(0,0,0,.12);
}

.order-top{
    display:flex;
    gap:20px;
    flex-wrap:wrap;
}

.order-img img{
    width:130px;
    height:130px;
    object-fit:cover;
    border-radius:12px;
}

.order-details{
    flex:1;
}

.order-details h3{
    margin:0 0 8px;
}

.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    color:#fff;
    display:inline-block;
    margin-top:8px;
}

.status.Pending{background:orange;}
.status.Shipped{background:#3498db;}
.status.OutforDelivery{background:#9b59b6;}
.status.Delivered{background:green;}
.status.Cancelled{background:red;}

.progress-wrapper{
    margin-top:18px;
}

.progress-bg{
    width:100%;
    height:10px;
    background:#eee;
    border-radius:20px;
    overflow:hidden;
}

.progress-bar{
    height:100%;
    width:0;
    background:#50207A;
    border-radius:20px;
    transition:width 2s ease;
}

.progress-text{
    margin-top:6px;
    font-size:13px;
    color:#555;
}

.earnings{
    margin-top:12px;
    background:#f1ecf8;
    padding:10px;
    border-radius:10px;
    font-size:14px;
}

.no-orders{
    text-align:center;
    margin-top:60px;
    font-size:18px;
    color:#555;
}

</style>
</head>
<body>

<div class="header">🛍 My Orders</div>

<div class="container">

<?php if(mysqli_num_rows($result) > 0): ?>

<?php while($row = mysqli_fetch_assoc($result)): 

$total = $row['total_amount'];
$seller_earn = ($total * $seller_percent) / 100;
$admin_earn = ($total * $admin_percent) / 100;

/* Progress Logic */
$status = $row['payment_method']; // change if you have order_status column
$progress = 25; // default

// If you later add order_status column, use that instead

?>

<div class="order-card">

    <div class="order-top">
        <div class="order-img">
            <img src="uploads/products/<?= $row['product_image'] ?: 'default.png' ?>">
        </div>

        <div class="order-details">
            <h3><?= htmlspecialchars($row['product_name']) ?></h3>

            <p><strong>Total:</strong> ₹<?= number_format($total,2) ?></p>
            <p><strong>Recipient:</strong> <?= htmlspecialchars($row['recipient_name']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($row['city']) ?>, <?= htmlspecialchars($row['state']) ?></p>
            <p><strong>Order Date:</strong> <?= $row['created_at'] ?></p>

            <span class="status Pending">Processing</span>
        </div>
    </div>

    <!-- Animated Progress -->
    <div class="progress-wrapper">
        <div class="progress-bg">
            <div class="progress-bar" data-progress="70"></div>
        </div>
        <div class="progress-text">Delivery in progress...</div>
    </div>

    <!-- Earnings -->
    <div class="earnings">
        💰 Seller Earnings (80%): ₹<?= number_format($seller_earn,2) ?><br>
        🏦 Gift Galaxy Commission (20%): ₹<?= number_format($admin_earn,2) ?>
    </div>

</div>

<?php endwhile; ?>

<?php else: ?>

<div class="no-orders">
    You haven't placed any orders yet.
</div>

<?php endif; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll(".progress-bar").forEach(function(bar){
        let progress = bar.getAttribute("data-progress");
        setTimeout(()=>{
            bar.style.width = progress + "%";
        }, 300);
    });
});
</script>

</body>
</html>
