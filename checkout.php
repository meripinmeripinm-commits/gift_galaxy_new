<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT c.product_id, c.quantity,
       p.product_name, p.product_price,
       p.shipping_charge
FROM cart c
JOIN products p ON c.product_id = p.id
WHERE c.user_id = $user_id
";

$result = mysqli_query($conn,$sql);

$subtotal = 0;
$total_shipping = 0;
$items = [];

while($row = mysqli_fetch_assoc($result)){
    $row_total = $row['product_price'] * $row['quantity'];
    $row_shipping = $row['shipping_charge'] * $row['quantity'];

    $subtotal += $row_total;
    $total_shipping += $row_shipping;

    $items[] = $row;
}

$grand_total = $subtotal + $total_shipping;

if($grand_total <= 0){
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Gift Galaxy</title>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
/* GLOBAL */
body{
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg,#f5f7fa,#e4e9f2);
    animation: fadeIn .6s ease-in-out;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(20px);}
    to{opacity:1; transform:translateY(0);}
}

.container{
    max-width:1200px;
    margin:40px auto;
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:30px;
    padding:20px;
}

/* FORM */
.form-box{
    background:#fff;
    padding:35px;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
    transition:.3s;
}

.form-box:hover{
    transform:translateY(-4px);
}

h2{
    margin-top:0;
    color:#6A1B9A;
}

label{
    font-size:13px;
    font-weight:600;
    color:#444;
}

input, textarea{
    width:100%;
    padding:13px;
    margin:6px 0 18px;
    border-radius:10px;
    border:1px solid #ddd;
    font-size:14px;
    transition:.3s;
}

input:focus, textarea:focus{
    border-color:#6A1B9A;
    box-shadow:0 0 0 3px rgba(106,27,154,.1);
    outline:none;
}

.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

/* BUTTON */
.complete-btn{
    width:100%;
    padding:16px;
    background:linear-gradient(135deg,#6A1B9A,#9C27B0);
    color:#fff;
    border:none;
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}

.complete-btn:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 30px rgba(106,27,154,.4);
}

/* SUMMARY */
.summary{
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
    height:fit-content;
    position:sticky;
    top:20px;
}

.summary h3{
    margin-top:0;
    border-bottom:1px solid #eee;
    padding-bottom:10px;
}

.summary-item{
    display:flex;
    justify-content:space-between;
    font-size:14px;
    margin-bottom:10px;
}

.total{
    font-weight:700;
    font-size:18px;
    color:#6A1B9A;
}

.notice{
    margin-top:15px;
    font-size:13px;
    color:#777;
}

/* MOBILE */
@media(max-width:900px){
    .container{
        grid-template-columns:1fr;
    }

    .summary{
        position:relative;
        top:0;
    }

    .row{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<div class="container">

<!-- LEFT -->
<div class="form-box">
<h2>Delivery Details</h2>

<form id="checkoutForm">

<label>Your Name / Nickname</label>
<input type="text" id="name" required>

<label>Phone</label>
<input type="text" id="phone" required>

<label>Address</label>
<textarea id="address" required></textarea>

<label>Landmark</label>
<input type="text" id="landmark">

<div class="row">
<div>
<label>City</label>
<input type="text" id="city" required>
</div>
<div>
<label>State</label>
<input type="text" id="state" required>
</div>
</div>

<label>Pincode</label>
<input type="text" id="pincode" required>

<h3>Payment Method</h3>
<p>Online Payment (Razorpay)</p>

<button type="button" class="complete-btn" onclick="payNow()">
Complete Order 💳
</button>

<div class="notice">
Notice: We will provide Cash on Delivery method very soon!
</div>

</form>
</div>

<!-- RIGHT -->
<div class="summary">
<h3>Order Summary</h3>

<?php foreach($items as $item){ ?>
<div class="summary-item">
<span><?php echo $item['product_name']; ?> × <?php echo $item['quantity']; ?></span>
<span>₹<?php echo number_format($item['product_price'] * $item['quantity'],2); ?></span>
</div>
<?php } ?>

<hr>

<div class="summary-item">
<span>Subtotal</span>
<span>₹<?php echo number_format($subtotal,2); ?></span>
</div>

<div class="summary-item">
<span>Shipping</span>
<span>₹<?php echo number_format($total_shipping,2); ?></span>
</div>

<hr>

<div class="summary-item total">
<span>Total</span>
<span>₹<?php echo number_format($grand_total,2); ?></span>
</div>

</div>

</div>

<script>

/* AUTO SAVE ADDRESS */
const fields = ["name","phone","address","landmark","city","state","pincode"];

fields.forEach(id=>{
    const input = document.getElementById(id);
    input.value = localStorage.getItem(id) || "";
    input.addEventListener("input",()=>{
        localStorage.setItem(id,input.value);
    });
});

/* RAZORPAY */
function payNow(){

var name = document.getElementById("name").value;
var phone = document.getElementById("phone").value;
var address = document.getElementById("address").value;
var city = document.getElementById("city").value;
var state = document.getElementById("state").value;
var pincode = document.getElementById("pincode").value;

if(!name || !phone || !address || !city || !state || !pincode){
    alert("Please fill all required fields");
    return;
}

var options = {
    "key": "rzp_test_SGnSZdqBG7ngm8",
    "amount": "<?php echo $grand_total * 100; ?>",
    "currency": "INR",
    "name": "Gift Galaxy",
    "description": "Order Payment",
    "handler": function (response){

        window.location.href = "payment_success.php?payment_id="
        + response.razorpay_payment_id
        + "&name="+encodeURIComponent(name)
        + "&phone="+encodeURIComponent(phone)
        + "&address="+encodeURIComponent(address)
        + "&city="+encodeURIComponent(city)
        + "&state="+encodeURIComponent(state)
        + "&pincode="+encodeURIComponent(pincode);
    },
    "theme": {"color": "#6A1B9A"}
};

var rzp = new Razorpay(options);
rzp.open();
}
</script>

</body>
</html>
