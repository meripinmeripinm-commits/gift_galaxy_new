<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

$sql = "SELECT 
            o.*, 
            oi.price, 
            oi.quantity, 
            p.product_name,
            p.image
        FROM orders_new o
        JOIN order_items_gg oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.seller_id = '$seller_id'
        ORDER BY o.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Seller Orders</title>
<style>
body{
    font-family:'Segoe UI',sans-serif;
    background:#f4f6fb;
}

.container{
    max-width:1100px;
    margin:40px auto;
}

.order-card{
    background:#fff;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    margin-bottom:25px;
    overflow:hidden;
    transition:0.3s;
}

.order-header{
    padding:20px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    cursor:pointer;
}

.order-header:hover{
    background:#faf9ff;
}

.badge{
    background:#50207A;
    color:#fff;
    padding:6px 14px;
    border-radius:20px;
    font-size:13px;
}

.order-details{
    display:none;
    padding:25px;
    border-top:1px solid #eee;
    animation:fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn{
    from{opacity:0;}
    to{opacity:1;}
}

.flex{
    display:flex;
    gap:25px;
}

.product-img{
    width:140px;
    height:140px;
    object-fit:cover;
    border-radius:15px;
}

.section{
    margin-bottom:20px;
}

.section h4{
    margin-bottom:10px;
    color:#50207A;
}

button, select{
    padding:10px 15px;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

.btn-purple{
    background:#50207A;
    color:#fff;
}

.btn-outline{
    background:#fff;
    border:1px solid #50207A;
    color:#50207A;
}

.courier-select{
    padding:8px 12px;
    border-radius:8px;
}

.copy-box{
    background:#f8f8f8;
    padding:10px;
    border-radius:8px;
}
</style>

<script>
function toggleDetails(id){
    var x = document.getElementById("details_"+id);
    x.style.display = (x.style.display === "block") ? "none" : "block";
}

function copyDetails(text){
    navigator.clipboard.writeText(text);
    alert("Order details copied!");
}
</script>
</head>
<body>

<div class="container">
<h2>📦 Seller Orders</h2>

<?php while($row = mysqli_fetch_assoc($result)) { ?>

<div class="order-card">

    <!-- HEADER -->
    <div class="order-header" onclick="toggleDetails(<?= $row['id'] ?>)">
        <div>
            <strong>Order #<?= $row['id'] ?></strong><br>
            <?= htmlspecialchars($row['product_name']) ?><br>
            ₹<?= number_format($row['total_amount'],2) ?>
        </div>
        <div>
            <span class="badge"><?= strtoupper($row['payment_method']) ?></span>
        </div>
    </div>

    <!-- DETAILS -->
    <div class="order-details" id="details_<?= $row['id'] ?>">

        <div class="flex section">
            <img src="uploads/<?= $row['image'] ?>" class="product-img">

            <div>
                <h4>Product Details</h4>
                <p><strong>Base Price:</strong> ₹<?= number_format($row['price'],2) ?></p>
                <p><strong>Quantity:</strong> <?= $row['quantity'] ?></p>
                <p><strong>Wrap Price:</strong> ₹<?= number_format($row['wrap_price'],2) ?></p>
                <p><strong>Shipping:</strong> ₹<?= number_format($row['shipping_cost'],2) ?></p>
                <p><strong>COD Fee:</strong> ₹<?= number_format($row['cod_fee'],2) ?></p>
                <p><strong>Total:</strong> ₹<?= number_format($row['total_amount'],2) ?></p>
            </div>
        </div>

        <div class="section">
            <h4>🎁 Gift Details</h4>
            <p><strong>Message:</strong> <?= htmlspecialchars($row['message']) ?></p>
            <p><strong>From:</strong> <?= htmlspecialchars($row['from_name']) ?></p>
            <p><strong>Receiver:</strong> <?= htmlspecialchars($row['recipient_name']) ?></p>
        </div>

        <div class="section">
            <h4>📍 Delivery Details</h4>
            <div class="copy-box">
                <?= htmlspecialchars($row['address']) ?>,
                <?= htmlspecialchars($row['city']) ?>,
                <?= htmlspecialchars($row['state']) ?> -
                <?= htmlspecialchars($row['pincode']) ?><br>
                Phone: <?= htmlspecialchars($row['phone']) ?>
            </div>
        </div>

        <div class="section">
            <h4>🚚 Courier Partner</h4>
            <select class="courier-select">
                <option <?= ($row['delivery_partner']=="Ekart")?"selected":"" ?>>Ekart</option>
                <option <?= ($row['delivery_partner']=="Shiprocket")?"selected":"" ?>>Shiprocket</option>
                <option <?= ($row['delivery_partner']=="Delhivery")?"selected":"" ?>>Delhivery</option>
            </select>
        </div>

        <div class="section">
            <button class="btn-purple" onclick="window.open('print_invoice.php?id=<?= $row['id'] ?>','_blank')">
                🖨 Print Invoice
            </button>

            <button class="btn-outline"
            onclick="copyDetails(`Order #<?= $row['id'] ?> 
            <?= $row['recipient_name'] ?> 
            <?= $row['phone'] ?> 
            <?= $row['address'] ?>`)">
                📋 Copy Details
            </button>
        </div>

    </div>
</div>

<?php } ?>

</div>
</body>
</html>
