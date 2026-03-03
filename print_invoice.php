<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Fetch seller info */
$seller = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sellers WHERE user_id='$user_id'"));
if(!$seller){
    die("Seller not found.");
}

$seller_id = $seller['id'];

/* Get order_id from URL */
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if(!$order_id){
    die("Order not found.");
}

/* Fetch order items for this seller */
$order_items_query = mysqli_query($conn, "
    SELECT oi.*, p.product_name, o.wrap_type, o.message AS order_message, o.from_name, o.recipient_name,
           o.phone, o.address, o.city, o.state, o.pincode, o.payment_method, o.delivery_partner, o.created_at
    FROM order_items_gg oi
    JOIN orders_new o ON oi.order_id=o.id
    JOIN products p ON oi.product_id=p.id
    WHERE oi.seller_id='$seller_id' AND oi.order_id='$order_id'
");

if(mysqli_num_rows($order_items_query)==0){
    die("Order not found.");
}

$order_items = [];
while($row = mysqli_fetch_assoc($order_items_query)){
    $order_items[] = $row;
}

/* Get main order info from first item */
$order = $order_items[0];
?>
<!DOCTYPE html>
<html>
<head>
<title>Invoice | Order #<?= $order_id ?> | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Segoe UI,sans-serif;background:#f2f3f7;padding:20px;}
.invoice{max-width:800px;margin:auto;background:#fff;padding:20px;border-radius:15px;box-shadow:0 0 15px rgba(0,0,0,.1);}
.invoice h2{color:#50207A;margin-bottom:10px;}
.invoice table{width:100%;border-collapse:collapse;margin-top:20px;}
.invoice table, th, td{border:1px solid #ccc;}
th, td{padding:10px;text-align:left;}
th{background:#50207A;color:#fff;}
.text-right{text-align:right;}
.print-btn{background:#50207A;color:#fff;border:none;padding:10px 20px;border-radius:50px;font-size:14px;cursor:pointer;margin-bottom:20px;}
.print-btn:hover{background:#D646FF;}
</style>
<script>
function printInvoice(){
    window.print();
}
</script>
</head>
<body>
<div class="invoice">
<h2>Gift Galaxy - Invoice</h2>
<p><strong>Order ID:</strong> #<?= $order_id ?></p>
<p><strong>Customer:</strong> <?= htmlspecialchars($order['from_name']) ?> → <?= htmlspecialchars($order['recipient_name']) ?></p>
<p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
<p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?>, <?= htmlspecialchars($order['city']) ?>, <?= htmlspecialchars($order['state']) ?> - <?= htmlspecialchars($order['pincode']) ?></p>
<p><strong>Delivery Partner:</strong> <?= htmlspecialchars($order['delivery_partner'] ?? '-') ?></p>
<p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
<p><strong>Wrap Type:</strong> <?= htmlspecialchars($order['wrap_type']) ?></p>
<p><strong>Order Date:</strong> <?= $order['created_at'] ?></p>

<table>
<thead>
<tr>
<th>Product</th>
<th>Qty</th>
<th>Price</th>
<th>Total</th>
<th>Customization</th>
</tr>
</thead>
<tbody>
<?php foreach($order_items as $item): 
    $custom = [];
    if(!empty($item['custom_details'])) $custom[] = "Message: ".htmlspecialchars($item['custom_details']);
    if(!empty($item['custom_photo']) && file_exists('uploads/custom/'.$item['custom_photo'])) $custom[] = "Photo: Yes";
    if(!empty($item['color'])) $custom[] = "Color: ".htmlspecialchars($item['color']);
    if(!empty($item['size'])) $custom[] = "Size: ".htmlspecialchars($item['size']);
?>
<tr>
<td><?= htmlspecialchars($item['product_name']) ?></td>
<td><?= $item['quantity'] ?></td>
<td>₹<?= number_format($item['price'],2) ?></td>
<td>₹<?= number_format($item['price']*$item['quantity'],2) ?></td>
<td><?= !empty($custom) ? implode(", ", $custom) : "-" ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<p class="text-right"><strong>Total Amount: ₹<?= number_format(array_sum(array_map(function($i){ return $i['price']*$i['quantity']; }, $order_items)),2) ?></strong></p>

<button class="print-btn" onclick="printInvoice()">Print Invoice</button>
</div>
</body>
</html>
