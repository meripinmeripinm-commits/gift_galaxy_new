<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

/* ================= UPDATE QUANTITY ================== */
$user_id = $_SESSION['user_id'];

if(isset($_GET['update_qty'])){
    $pid = (int)$_GET['update_qty'];
    $action = $_GET['action'] ?? '';
    // get current quantity
    $res = mysqli_query($conn,"SELECT quantity FROM cart WHERE user_id=$user_id AND product_id=$pid");
    if($res && mysqli_num_rows($res)){
        $row = mysqli_fetch_assoc($res);
        $qty = $row['quantity'];
        if($action=='plus') $qty++;
        if($action=='minus' && $qty>1) $qty--;
        mysqli_query($conn,"UPDATE cart SET quantity=$qty WHERE user_id=$user_id AND product_id=$pid");
    }
    header("Location: cart.php");
    exit;
}

/* ================= REMOVE ITEM ================== */
if(isset($_GET['remove'])){
    $pid = (int)$_GET['remove'];
    mysqli_query($conn,"DELETE FROM cart WHERE user_id=$user_id AND product_id=$pid");
    header("Location: cart.php");
    exit;
}

/* ================= FETCH CART ITEMS ================== */
$sql = "
SELECT p.id, p.product_name, p.product_price, p.product_image,
       p.shipping_charge, c.quantity
FROM cart c
LEFT JOIN products p ON c.product_id = p.id
WHERE c.user_id = $user_id AND p.deleted = 0
";

$result = mysqli_query($conn,$sql);

$subtotal = 0;
$total_shipping = 0;
$item_count = 0;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Cart | Gift Galaxy</title>
<style>
body{font-family:Segoe UI, sans-serif;background:#f4f4f9;margin:0;}
.header{display:flex;justify-content:space-between;padding:20px 40px;background:#fff;box-shadow:0 5px 20px rgba(0,0,0,.05);}
.header a{text-decoration:none;color:#6A1B9A;font-weight:600;}
.wrapper{max-width:1100px;margin:30px auto;display:grid;grid-template-columns:2fr 1fr;gap:25px;}
.cart-item{display:flex;gap:20px;background:#fff;padding:20px;border-radius:14px;margin-bottom:15px;box-shadow:0 5px 25px rgba(0,0,0,.06);}
.cart-item img{width:150px;height:150px;object-fit:cover;border-radius:12px;}
.details h4{margin:0 0 10px;}
.price{color:#6A1B9A;font-weight:700;}
.shipping{font-size:13px;color:#555;margin-top:5px;}
.qty{margin-top:10px;font-weight:600;display:flex;align-items:center;gap:8px;}
.qty button{background:#6A1B9A;color:#fff;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;font-weight:600;}
.remove{margin-top:8px;color:red;font-size:13px;text-decoration:none;display:inline-block;}
.button-group {
    display: flex;
    gap: 10px;          /* space between buttons */
    margin-top: 15px;
    flex-wrap: nowrap;   /* prevent wrapping to next line */
}

.gift-btn {
    flex: 1;              /* both buttons take equal width */
    min-width: 0;         /* prevents overflow text */
    text-align: center;
    background: #6A1B9A;
    color: #fff;
    border: none;
    padding: 10px 5px;   /* smaller padding avoids wrapping */
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;  /* prevent text from breaking */
    overflow: hidden;     /* crop if text too long */
    text-overflow: ellipsis; /* optional if really long */
}
.summary{background:#fff;padding:20px;border-radius:14px;position:sticky;top:20px;box-shadow:0 5px 25px rgba(0,0,0,.06);}
.summary h3{margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;}
.summary p{display:flex;justify-content:space-between;}
.total{font-weight:700;font-size:18px;color:#6A1B9A;}
</style>
</head>
<body>

<div class="header">
    <a href="index.php">⬅ Continue Shopping</a>
    <strong>My Cart</strong>
</div>

<div class="wrapper">

<div>
<?php if(mysqli_num_rows($result)>0){ ?>
    <?php while($row=mysqli_fetch_assoc($result)){ 
        $product_total = $row['product_price'] * $row['quantity'];
        $shipping_total = $row['shipping_charge'] * $row['quantity'];
        $subtotal += $product_total;
        $total_shipping += $shipping_total;
        $item_count++;
    ?>
    <div class="cart-item">
        <img src="uploads/products/<?php echo $row['product_image']; ?>">
        <div class="details">
            <h4><?php echo htmlspecialchars($row['product_name']); ?></h4>
            <div class="price">₹<?php echo number_format($row['product_price'],2); ?></div>
            <div class="shipping">Shipping: ₹<?php echo number_format($row['shipping_charge'],2); ?></div>
            
            <!-- QUANTITY CONTROL -->
            <div class="qty">
                Qty: 
                <a href="?update_qty=<?php echo $row['id']; ?>&action=minus"><button>-</button></a>
                <span><?php echo $row['quantity']; ?></span>
                <a href="?update_qty=<?php echo $row['id']; ?>&action=plus"><button>+</button></a>
            </div>

            <a href="?remove=<?php echo $row['id']; ?>" class="remove">Remove</a>

           <!-- BUTTONS INLINE -->
<div class="button-group">
    <a href="gift_now.php?product_id=<?php echo $row['id']; ?>" class="gift-btn">🎁 Gift Now</a>
    <a href="gift_yourself.php?product_id=<?php echo $row['id']; ?>" class="gift-btn">🎁Yourself</a>
</div>
        </div>
    </div>
    <?php } ?>
<?php } else { ?>
    <h3 style="text-align:center; margin-top:50px;">Your cart is empty 🛒</h3>
<?php } ?>
</div>

<?php 
$grand_total = $subtotal + $total_shipping;
?>
<div class="summary">
<h3>PRICE DETAILS</h3>
<p><span>Items (<?php echo $item_count; ?>)</span><span>₹<?php echo number_format($subtotal,2); ?></span></p>
<p><span>Shipping</span><span>₹<?php echo number_format($total_shipping,2); ?></span></p>
<hr>
<p class="total"><span>Total</span><span>₹<?php echo number_format($grand_total,2); ?></span></p>
</div>

</div>
</body>
</html>
