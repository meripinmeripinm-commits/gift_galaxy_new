 <?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
include 'config.php';

/* ===============================
   LOGIN CHECK
============================== */
if(!isset($_SESSION['username'])){
    $redirect = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect=$redirect");
    exit();
}

/* ===============================
   PRODUCT FETCH
============================== */
if(!isset($_GET['product_id'])){
    die('Product not found.');
}
$product_id = intval($_GET['product_id']);

$product_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id' LIMIT 1");
if(mysqli_num_rows($product_query) == 0){
    die('Product not found.');
}
$product = mysqli_fetch_assoc($product_query);
$shipping_charge = isset($product['shipping_charge']) ? floatval($product['shipping_charge']) : 0;
$seller_id = intval($product['seller_id']);

/* ===============================
   CATEGORY FETCH
============================== */
$category_query = mysqli_query($conn, "SELECT * FROM categories WHERE id='".$product['category_id']."' LIMIT 1");
$category = mysqli_fetch_assoc($category_query);

/* ===============================
   CUSTOMIZATION CHECK
============================== */
$show_custom_box = isset($product['is_customized']) && intval($product['is_customized']) === 1;

/* ===============================
   GIFT WRAPS FETCH
============================== */
$gift_wraps = [];
$wrapRes = mysqli_query($conn,"SELECT * FROM gift_wraps WHERE product_id='$product_id'");
while($w = mysqli_fetch_assoc($wrapRes)) $gift_wraps[] = $w;

/* ===============================
   HANDLE ORDER POST
============================== */
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['order_json'])){
    header('Content-Type: application/json');
if(ob_get_length()) ob_clean();
    $data = json_decode($_POST['order_json'], true);
    $order = $data['order'];

    $wrap_type      = mysqli_real_escape_string($conn, $order['wrap_type']);
    $wrap_price     = floatval($order['wrap_price']);
    $message        = mysqli_real_escape_string($conn, $order['message']);
    $from_name      = mysqli_real_escape_string($conn, $order['from_name']);
    $recipient      = mysqli_real_escape_string($conn, $order['recipient']);
    $phone          = mysqli_real_escape_string($conn, $order['phone']);
    $address        = mysqli_real_escape_string($conn, $order['address']);
    $landmark       = mysqli_real_escape_string($conn, $order['landmark']);
    $city           = mysqli_real_escape_string($conn, $order['city']);
    $state          = mysqli_real_escape_string($conn, $order['state']);
    $pincode        = mysqli_real_escape_string($conn, $order['pincode']);
    $custom_details = mysqli_real_escape_string($conn, $order['custom_details'] ?? '');
    $custom_color   = mysqli_real_escape_string($conn, $order['color'] ?? '');
    $custom_size    = mysqli_real_escape_string($conn, $order['size'] ?? '');
    $custom_photo   = '';

    // Handle optional photo upload
    if(isset($_FILES['custom_photo']) && !empty($_FILES['custom_photo']['name'])){
        $tmp = $_FILES['custom_photo']['tmp_name'];
        $ext = pathinfo($_FILES['custom_photo']['name'], PATHINFO_EXTENSION);
        $filename = 'custom_'.$product_id.'_'.time().'.'.$ext;
        move_uploaded_file($tmp, 'uploads/custom/'.$filename);
        $custom_photo = $filename;
    }

    $total          = floatval($order['total']);
    $payment_method = mysqli_real_escape_string($conn, $order['payment_method']);
    $payment_id     = mysqli_real_escape_string($conn, $order['payment_id'] ?? '');
    $user_id        = $_SESSION['user_id'];
    $cod_fee        = 0;

    // Insert into orders_new
    $insert_order = mysqli_query($conn,"INSERT INTO orders_new
    (user_id, product_id, seller_id, wrap_type, wrap_price, shipping_cost, cod_fee, message, from_name, recipient_name, phone, address, city, state, pincode, payment_method, payment_id, total_amount, created_at, color, size, custom_details, custom_photo)
    VALUES
    ('$user_id','$product_id','$seller_id','$wrap_type','$wrap_price','$shipping_charge','$cod_fee','$message','$from_name','$recipient','$phone','$address','$city','$state','$pincode','$payment_method','$payment_id','$total',NOW(),'$custom_color','$custom_size','$custom_details','$custom_photo')
    ");

    if(!$insert_order){
        echo json_encode([
            'status'=>'error',
            'message'=>mysqli_error($conn)
        ]);
        exit;
    }

    $order_id = mysqli_insert_id($conn);

// Insert into order_items_gg for seller analytics
$quantity = 1;
$price    = floatval($product['product_price']);
mysqli_query($conn, "INSERT INTO order_items_gg
(order_id, product_id, seller_id, quantity, price, created_at)
VALUES
('$order_id','$product_id','$seller_id','$quantity','$price',NOW())
");

/* ===============================
   AUTO SPLIT PAYMENT - 20%
============================== */

try {

    if(file_exists('razorpay_config.php')){

        include_once 'razorpay_config.php';

        if(isset($api)){

            $seller_query = mysqli_query($conn, "SELECT route_account_id FROM sellers WHERE id='$seller_id' LIMIT 1");
            $seller_data  = mysqli_fetch_assoc($seller_query);

            if($seller_data && !empty($seller_data['route_account_id'])){

                $route_account_id = $seller_data['route_account_id'];

                $commission_percentage = 20;
                $commission_amount = ($total * $commission_percentage) / 100;
                $seller_amount = $total - $commission_amount;

                $api->transfer->create([
                    'amount'   => round($seller_amount * 100),
                    'currency' => 'INR',
                    'account'  => $route_account_id,
                    'notes'    => [
                        'order_id' => $order_id
                    ]
                ]);
            }
        }
    }

} catch (Throwable $e) {
    error_log("Split error: " . $e->getMessage());
}
echo json_encode(['status'=>'success']);
exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Gift Now | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Segoe UI,sans-serif;}
body{background:#f4f5fa;color:#333;}
.container{max-width:900px;margin:40px auto;padding:20px;}
.card{background:#fff;border-radius:20px;box-shadow:0 15px 40px rgba(0,0,0,.08);padding:28px;margin-bottom:28px;}
.card h3{margin-bottom:18px;color:#50207A;font-weight:700;}
.product-img{width:100%;max-width:240px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,.15);}
.wrap-item,#normal-wrap{display:flex;align-items:center;gap:16px;margin-bottom:16px;padding:14px;border-radius:14px;cursor:pointer;background:#fafafa;}
.wrap-item.selected,#normal-wrap.selected{border:2px solid #D646FF;background:#f8f3ff;}
.wrap-item img{width:62px;height:62px;border-radius:12px;object-fit:cover;}
input,textarea{width:100%;padding:14px;border-radius:10px;border:1px solid #ccc;margin-bottom:16px;font-size:15px;}
textarea{height:130px;}
.generate-row{display:flex;justify-content:flex-end;margin-bottom:8px;}
.generate-row i{cursor:pointer;color:#50207A;font-size:20px;}
.shipping-card{background:#f3fff4;border:1px dashed #4caf50;border-radius:14px;padding:14px;display:flex;align-items:center;gap:10px;font-weight:700;color:#2e7d32;}
.payment-methods{display:flex;gap:16px;}
button{border:none;padding:14px;border-radius:50px;font-size:16px;font-weight:600;cursor:pointer;}
button.disabled{background:#ccc;cursor:not-allowed;pointer-events:none;}
#onlineBtn{background:#D646FF;color:#fff;}
.total-price{font-size:20px;font-weight:800;text-align:right;color:#50207A;margin-top:12px;}
#successBox{position:fixed;inset:0;background:rgba(0,0,0,.75);display:none;align-items:center;justify-content:center;z-index:9999;}
.successCard{background:#fff;padding:50px;border-radius:20px;text-align:center;animation:pop .5s ease;}
.successCard h1{color:#4CAF50;}
@keyframes pop{0%{transform:scale(.6);opacity:0}100%{transform:scale(1);opacity:1}}
.check{font-size:80px;color:#4CAF50;animation:bounce 1s infinite alternate}
@keyframes bounce{to{transform:translateY(-10px)}}
</style>
</head>
<body>
<div class="container">
<form id="giftForm" enctype="multipart/form-data">

<!-- PRODUCT -->
<div class="card" style="display:flex;gap:22px;align-items:center;">
<img src="uploads/products/<?= htmlspecialchars($product['product_image']) ?>" class="product-img">
<div>
<h2><?= htmlspecialchars($product['product_name']) ?></h2>
<p><?= nl2br(htmlspecialchars($product['product_description'])) ?></p>
<p style="font-size:22px;font-weight:800;color:#D646FF;">Base Price: ₹<?= number_format($product['product_price'],2) ?></p>
</div>
</div>

<!-- WRAP -->
<div class="card">
<h3>Gift Wrapping Options</h3>
<div id="normal-wrap" class="selected" data-price="<?= floatval($product['gift_wrap_price'] ?? 0) ?>"><i class="fa fa-gift"></i> Normal Wrapping - Free</div>
<?php foreach($gift_wraps as $wrap): ?>
<div class="wrap-item" data-price="<?= $wrap['price'] ?>">
<img src="uploads/gift_wraps/<?= $wrap['image'] ?>">
<?= htmlspecialchars($wrap['color']) ?> - ₹<?= number_format($wrap['price'],2) ?>
</div>
<?php endforeach; ?>
</div>

<?php if($show_custom_box): ?>
<div class="card">
<h3>Customization Details</h3>
<label>Color</label><input type="text" name="color" placeholder="Color">
<label>Size</label><input type="text" name="size" placeholder="Size">
<label>Message / Special Request</label><textarea name="custom_details" placeholder="Any special request..."></textarea>
<label>Upload Photo (optional)</label>
<input type="file" name="custom_photo" accept="image/*">
</div>
<?php endif; ?>

<!-- SHIPPING -->
<div class="card">
    <div class="shipping-card">
        <i class="fa fa-truck"></i>
        <?php if($shipping_charge > 0): ?>
            Shipping: ₹<?= number_format($shipping_charge,2) ?>
        <?php else: ?>
            Free Shipping
        <?php endif; ?>
    </div>
</div>

<!-- MESSAGE -->
<div class="card">
<h3>Lovable Message (Mandatory)</h3>
<div class="generate-row"><i class="fa fa-wand-magic-sparkles" id="generateMsg"></i></div>
<textarea id="message" placeholder="Write your lovable message..." required></textarea>
</div>

<!-- FROM -->
<div class="card">
<h3>From Name</h3>
<input id="from_name" placeholder="Your Name" required>
</div>

<!-- DELIVERY -->
<div class="card">
<h3>Delivery Details</h3>
<input id="recipient" placeholder="Recipient Name (Nick Name)" required>
<input id="phone" placeholder="Phone" required>
<input id="address" placeholder="Address" required>
<input id="landmark" placeholder="Landmark" required>
<input id="city" placeholder="City" required>
<input id="state" placeholder="State" required>
<input id="pincode" placeholder="Pincode" required>
</div>

<!-- PAYMENT -->
<div class="card">
<h3>Payment Method</h3>
<div class="payment-methods">
<button type="button" id="onlineBtn">Complete Order 💳</button>
</div>
<div class="total-price">Total: ₹ <span id="total"><?= number_format($product['product_price'] + $shipping_charge + ($product['gift_wrap_price'] ?? 0),2) ?></span></div>
</form>
</div>

<!-- SUCCESS -->
<div id="successBox">
<div class="successCard">
<div class="check">✔</div>
<h1>Congratulations!</h1>
<p>Your order is placed successfully</p>
<p>Redirecting to home...</p>
</div>
</div>

<script>
let base = <?= $product['product_price'] ?>;
let shipping = <?= $shipping_charge ?>;
let wrapPrice = <?= $product['gift_wrap_price'] ?? 0 ?>;
let wrapType = "Normal Wrapping";

// Wrap selection
document.querySelectorAll('.wrap-item,#normal-wrap').forEach(w=>{
    w.onclick=()=>{
        document.querySelectorAll('.wrap-item,#normal-wrap').forEach(x=>x.classList.remove('selected'));
        w.classList.add('selected');
        wrapPrice = parseFloat(w.dataset.price) || 0;
        wrapType = w.innerText.split('-')[0].trim();
        document.getElementById('total').innerText = (base + wrapPrice + shipping).toFixed(2);
    };
});

// AI message generation
document.getElementById('generateMsg')?.addEventListener('click',()=>{
    fetch('ai_generate_message.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({mood:'love'})
    }).then(r=>r.json()).then(d=>{
        if(d.message) document.getElementById('message').value=d.message;
    });
});

// Success animation
function showSuccessAnimation(){
    document.getElementById('successBox').style.display='flex';
    setTimeout(()=>{window.location='index.php'},3500);
}

// Online payment
document.getElementById('onlineBtn').onclick = ()=>{
    let missing = [];
    const fields = ['message','from_name','recipient','phone','address','landmark','city','state','pincode'];
    const fieldNames = {message:"Lovable Message",from_name:"From Name",recipient:"Recipient Name",phone:"Phone",address:"Address",landmark:"Landmark",city:"City",state:"State",pincode:"Pincode"};
    fields.forEach(f=>{if(!document.getElementById(f).value.trim()) missing.push(fieldNames[f])});

    <?php if($show_custom_box): ?>
    let colorVal = document.querySelector('[name=color]').value.trim();
    let sizeVal  = document.querySelector('[name=size]').value.trim();
    let customMsg= document.querySelector('[name=custom_details]').value.trim();
    if(!colorVal) missing.push("Color");
    if(!sizeVal) missing.push("Size");
    if(!customMsg) missing.push("Customization Message");
    <?php endif; ?>

    if(missing.length>0){alert("❌ Please fill the following fields before paying:\n- "+missing.join("\n- "));return;}

    let totalAmount = base + wrapPrice + shipping;

    var options = {
        "key": "rzp_test_SLUuFIqQnJCdyz",
        "amount": Math.round(totalAmount * 100),
        "currency": "INR",
        "name": "Gift Galaxy",
        "description": "<?= htmlspecialchars($product['product_name']) ?> - "+wrapType,
        "handler": function(response){
            let formData = new FormData(document.getElementById('giftForm'));
            let orderData = {
                wrap_type: wrapType,
                wrap_price: wrapPrice,
                message: document.getElementById('message').value,
                from_name: document.getElementById('from_name').value,
                recipient: document.getElementById('recipient').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                landmark: document.getElementById('landmark').value,
                city: document.getElementById('city').value,
                state: document.getElementById('state').value,
                pincode: document.getElementById('pincode').value,
                total: totalAmount,
                payment_method: "ONLINE",
                payment_id: response.razorpay_payment_id,
                <?php if($show_custom_box): ?>
                color: colorVal,
                size: sizeVal,
                custom_details: customMsg
                <?php endif; ?>
            };
            formData.append('order_json', JSON.stringify({order: orderData}));

            fetch('gift_yourself.php?product_id=<?= $product_id ?>',{
                method:'POST',
                body: formData
            })
            .then(r=>r.json())
            .then(res=>{
                if(res.status=="success"){ showSuccessAnimation(); } 
                else{ alert("❌ Error saving order"); }
            })
            .catch(err=>{console.log(err); alert("Server error: " + err);});
        },
        "theme":{"color":"#50207A"}
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
};
</script>
</body>
</html>
