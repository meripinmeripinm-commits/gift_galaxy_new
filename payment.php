<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php?redirect=payment.php");
    exit;
}

$product_id = $_POST['product_id'] ?? $_GET['product_id'] ?? 0;
if(!$product_id){ header("Location: index.php"); exit; }

$price = $_POST['price'] ?? 0;
$receiver_name = $_POST['receiver_name'] ?? '';
$receiver_phone = $_POST['receiver_phone'] ?? '';
$address = $_POST['address'] ?? '';
$message = $_POST['message'] ?? '';

$productQuery = mysqli_query($conn,"SELECT * FROM products WHERE id=$product_id");
$product = mysqli_fetch_assoc($productQuery);
?>
<!DOCTYPE html>
<html>
<head>
<title>Payment | Gift Galaxy</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#f5f6fb;}
.container{max-width:600px;margin:50px auto;background:#fff;padding:30px;border-radius:20px;box-shadow:0 15px 30px rgba(0,0,0,0.1);}
.payment-methods{display:flex;gap:15px;flex-wrap:wrap;margin-top:15px;}
.payment-methods label{display:flex;align-items:center;gap:10px;padding:10px 15px;border:1px solid #ccc;border-radius:10px;cursor:pointer;background:#fff;}
.payment-methods input[type="radio"]{display:none;}
.payment-methods label.selected{border-color:#50207A;background:#EDE0F7;}
.btn{margin-top:20px;width:100%;padding:14px;border:none;border-radius:10px;background:#50207A;color:#fff;font-size:16px;cursor:pointer;}
</style>
<script>
function selectPayment(el){
    document.querySelectorAll('.payment-methods label').forEach(l=>l.classList.remove('selected'));
    el.classList.add('selected');
}
</script>
</head>
<body>
<div class="container">
<h2>Payment for: <?php echo htmlspecialchars($product['name']); ?></h2>
<p>Price: ₹<?php echo number_format($price,2); ?></p>

<form method="POST" action="save_order.php">
<input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
<input type="hidden" name="price" value="<?php echo $price; ?>">
<input type="hidden" name="receiver_name" value="<?php echo htmlspecialchars($receiver_name); ?>">
<input type="hidden" name="receiver_phone" value="<?php echo htmlspecialchars($receiver_phone); ?>">
<input type="hidden" name="address" value="<?php echo htmlspecialchars($address); ?>">
<input type="hidden" name="message" value="<?php echo htmlspecialchars($message); ?>">

<h3>Select Payment Method:</h3>
<div class="payment-methods">
    <label onclick="selectPayment(this)"><input type="radio" name="payment_method" value="gpay" required> Google Pay</label>
    <label onclick="selectPayment(this)"><input type="radio" name="payment_method" value="paytm"> Paytm</label>
    <label onclick="selectPayment(this)"><input type="radio" name="payment_method" value="cod"> Cash on Delivery</label>
</div>

<button type="submit" class="btn">Confirm Payment</button>
</form>
</div>
</body>
</html>
