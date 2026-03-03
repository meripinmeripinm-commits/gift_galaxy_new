<?php
session_start();
include 'config.php';

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

/* ================= ORDER SUPPORT ================= */
$order = null;
$success = "";
$error = "";

if(isset($_GET['order_id']) && is_numeric($_GET['order_id'])){
    $order_id = (int)$_GET['order_id'];
    $order_query = mysqli_query($conn,"SELECT * FROM orders_new WHERE id='$order_id'");
    if(mysqli_num_rows($order_query)>0){
        $order = mysqli_fetch_assoc($order_query);

        $full_address = $order['recipient_name']."\n".
                        $order['phone']."\n".
                        $order['address'].", ".
                        $order['city'].", ".
                        $order['state']." - ".
                        $order['pincode'];
    }
}

/* ================= CONFIRM SHIPMENT ================= */
if(isset($_POST['confirm_shipment']) && $order){

    $partner = mysqli_real_escape_string($conn,$_POST['partner']);
    $awb = trim(mysqli_real_escape_string($conn,$_POST['awb']));

    $valid = false;

    // AWB format validation
    if($partner == "Shiprocket" && preg_match('/^[A-Za-z0-9]{10,12}$/',$awb)) $valid = true;
    if($partner == "Delhivery" && preg_match('/^[A-Za-z0-9]{10,12}$/',$awb)) $valid = true;
    if($partner == "DTDC" && preg_match('/^[0-9]{9,12}$/',$awb)) $valid = true;
    if($partner == "Ekart" && preg_match('/^[A-Za-z0-9]{8,12}$/',$awb)) $valid = true;

    if($valid){

        $shipping_date = date("Y-m-d H:i:s");

        mysqli_query($conn,"UPDATE orders_new SET 
            assigned_partner='$partner',
            tracking_id='$awb',
            delivery_status='Shipped',
            shipping_date='$shipping_date'
            WHERE id='".$order['id']."'
        ");

        $success = "Shipment confirmed successfully.";
        
        // Refresh order data after update
        $order_query = mysqli_query($conn,"SELECT * FROM orders_new WHERE id='".$order['id']."'");
        $order = mysqli_fetch_assoc($order_query);

    } else {
        $error = "⚠ Invalid Tracking ID for $partner. Please enter a valid AWB number.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Delivery Partners - Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(120deg,#89f7fe,#66a6ff);
}
.container{
    max-width:1000px;
    margin:50px auto;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0 15px 40px rgba(0,0,0,0.2);
}
h2{
    text-align:center;
    margin-bottom:30px;
}
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}
.card{
    background:#f7f9ff;
    padding:25px;
    border-radius:12px;
    text-align:center;
    transition:0.3s;
    cursor:pointer;
}
.card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}
.card h3{
    margin-bottom:10px;
}
.card p{
    font-size:14px;
    color:#555;
}
.btn{
    margin-top:15px;
    padding:10px 15px;
    border:none;
    border-radius:8px;
    font-weight:bold;
    background:#007bff;
    color:white;
    cursor:pointer;
}
.section{
    background:#f7f9ff;
    padding:20px;
    border-radius:12px;
    margin-top:30px;
}
input,select{
    width:100%;
    padding:10px;
    margin:8px 0 15px;
    border-radius:8px;
    border:1px solid #ddd;
}
.success{
    background:#d4edda;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
}
.error{
    background:#f8d7da;
    color:#721c24;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    font-weight:600;
}
.note-box{
    background:#fff3cd;
    padding:12px;
    border-radius:10px;
    font-size:14px;
    line-height:1.6;
    margin-top:10px;
}
.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    font-weight:bold;
    color:#333;
}
</style>

<script>
function openPartner(url){
    window.open(url,"_blank");
}
function copyAddress(){
    navigator.clipboard.writeText(`<?php echo isset($full_address)?$full_address:''; ?>`);
    alert("Address Copied!");
}
</script>

</head>
<body>

<div class="container">

<a href="seller.php" class="back">← Back to Seller Panel</a>

<h2>🚚 Delivery Partners Panel</h2>

<?php if(!empty($success)) echo "<div class='success'>$success</div>"; ?>
<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

<div class="grid">

    <div class="card" onclick="openPartner('https://app.shiprocket.in/')">
        <h3>🚀 Shiprocket</h3>
        <p>Generate AWB and manage shipments.</p>
        <button class="btn">Open Dashboard</button>
    </div>

    <div class="card" onclick="openPartner('https://www.delhivery.com/')">
        <h3>📦 Delhivery</h3>
        <p>Create shipment and generate tracking ID.</p>
        <button class="btn">Open Dashboard</button>
    </div>

    <div class="card" onclick="openPartner('https://www.dtdc.in/')">
        <h3>📬 DTDC</h3>
        <p>Create consignment and track delivery.</p>
        <button class="btn">Open Dashboard</button>
    </div>

    <div class="card" onclick="openPartner('https://ekartlogistics.com/')">
        <h3>🚛 Ekart</h3>
        <p>Schedule pickup and generate shipment ID.</p>
        <button class="btn">Open Dashboard</button>
    </div>

</div>

<?php if($order){ ?>

<div class="section">
<h3>📦 Customer Address (Order #<?php echo $order['id']; ?>)</h3>
<p><?php echo nl2br(htmlspecialchars($full_address)); ?></p>

<?php 
$wrap_price = $order['wrap_price'] ?? 0;
$shipping_cost = $order['shipping_cost'] ?? 0;
$total_amount = $order['total_amount'] ?? 0;

$product_price = $total_amount - $wrap_price - $shipping_cost;
$shipping_display = $shipping_cost > 0 ? "₹".number_format($shipping_cost,2) : "Free";
?>

<p><strong>🛒 Product Price:</strong> ₹<?php echo number_format($product_price,2); ?></p>
<p><strong>🚚 Shipping:</strong> <?php echo $shipping_display; ?></p>
<p><strong>🎁 Wrapping:</strong> ₹<?php echo number_format($wrap_price,2); ?></p>
<p><strong>💰 Total Amount:</strong> ₹<?php echo number_format($total_amount,2); ?></p>

<button class="btn" onclick="copyAddress()">Copy Address</button>
</div>

<div class="section">
<h3>🚀 Confirm Shipment (Enter AWB)</h3>
<form method="POST">

<select name="partner" required>
<option value="">Select Partner</option>
<option>Shiprocket</option>
<option>Delhivery</option>
<option>DTDC</option>
<option>Ekart</option>
</select>

<input type="text" name="awb" placeholder="Enter AWB / Tracking ID" required>

<button type="submit" name="confirm_shipment" class="btn">
Confirm & Mark as Shipped
</button>

</form>
</div>

<div class="section">
<h3>💡 Tips for Sellers</h3>

<div class="note-box">
<b>🚀 Shiprocket:</b> Login → Open Order → Click "Ship Now" → Select Courier → Confirm → AWB Generated → Enter above.<br><br>
<b>📦 Delhivery:</b> Login → Create Shipment → Enter address → Confirm pickup → AWB generated → Enter above.<br><br>
<b>📬 DTDC:</b> Login → Create consignment → Generate number → Enter above.<br><br>
<b>🚛 Ekart:</b> Login → Create shipment request → Pickup scheduled → Shipment ID generated → Enter above.
</div>

</div>

<?php } ?>

</div>
</body>
</html>
