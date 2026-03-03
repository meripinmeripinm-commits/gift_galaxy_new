<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));

if($user['seller_paid'] == 0){
    header("Location: seller_payment.php");
    exit;
}

$username = $user['username'];
/* CREATE SELLER ACCOUNT */
if(isset($_POST['create_seller'])){

    $store_name = trim($_POST['store_name']);
    $store_desc = trim($_POST['store_desc']);

    if(empty($store_name)){
        echo "<script>alert('Store name is required');</script>";
    } else {

        $store_name = mysqli_real_escape_string($conn,$store_name);
        $store_desc = mysqli_real_escape_string($conn,$store_desc);

        mysqli_query($conn,"
            INSERT INTO sellers (user_id, store_name, store_desc)
            VALUES ('$user_id','$store_name','$store_desc')
        ");

        header("Location: seller.php");
        exit;
    }
}

$products = $feedbacks = [];
$stats = ['total_orders'=>0,'total_items'=>0,'total_revenue'=>0];

/* Date Filter */
$filter = $_GET['filter'] ?? 'all';
$date_condition = "";

if($filter == "30days"){
    $date_condition = " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}
elseif($filter == "1year"){
    $date_condition = " AND o.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
}

/* Preload categories */
$categoryMap = [];
$catRes = mysqli_query($conn, "SELECT id, name FROM categories");
while($c = mysqli_fetch_assoc($catRes)){
    $categoryMap[$c['id']] = $c['name'];
}

if($seller){
    $seller_id = $seller['id'];

    $products = mysqli_query($conn, "SELECT * FROM products WHERE seller_id='$seller_id' AND deleted=0 ORDER BY created_at DESC");

    $feedbacks = mysqli_query($conn, "
        SELECT r.*, p.product_name, u.username
        FROM reviews r
        LEFT JOIN products p ON r.product_id=p.id
        LEFT JOIN users u ON r.user_id=u.id
        WHERE p.seller_id='$seller_id'
        ORDER BY r.id DESC
    ");

    /* Updated Analytics Query With Filter */
    $stats_query = mysqli_query($conn, "
        SELECT 
            COUNT(DISTINCT oi.order_id) AS total_orders,
            SUM(CASE WHEN o.delivery_status='Delivered' THEN oi.quantity ELSE 0 END) AS total_items,
            SUM(CASE WHEN o.delivery_status='Delivered' THEN oi.price * oi.quantity ELSE 0 END) AS total_revenue
        FROM order_items_gg oi
        JOIN orders_new o ON oi.order_id=o.id
        WHERE oi.seller_id='$seller_id' $date_condition
    ");

    if($stats_query) $stats = mysqli_fetch_assoc($stats_query);
}

/* AJAX Review Delete */
if(isset($_POST['delete_review_id'])){
    $del_id = intval($_POST['delete_review_id']);
    mysqli_query($conn, "DELETE FROM reviews WHERE id='$del_id'");
    echo "success";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Seller Dashboard | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
a{text-decoration:none !important;}
td a{text-decoration:none;color:#50207A;font-weight:600;}
body{font-family:Segoe UI,sans-serif;background:#f2f3f7;}
.container{max-width:1400px;margin:20px auto;display:grid;grid-template-columns:300px 1fr;gap:20px;}
@media(max-width:1100px){.container{grid-template-columns:1fr;}}
.sidebar,.main{background:#fff;border-radius:18px;box-shadow:0 12px 30px rgba(0,0,0,.1);}
.sidebar{padding:25px;text-align:center;}
.avatar{width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #50207A;cursor:pointer;transition:all 0.3s ease;box-shadow:0 0 15px #D646FF;}
.avatar:hover{transform:scale(1.05);box-shadow:0 0 25px #D646FF;}
.sidebar h2{margin-top:15px;color:#50207A;cursor:pointer;}
.sidebar p{margin-top:5px;color:#333;}
.menu{margin-top:25px;text-align:left;}
.menu a{display:block;padding:12px;border-radius:10px;font-weight:600;color:#333;text-decoration:none;margin-bottom:5px;transition:all 0.3s ease;}
.menu a:hover,.menu a.active{background:#f3e9ff;box-shadow:0 0 8px #D646FF;}
.main{padding:25px;}
.section{margin-bottom:35px;}
.section h3{font-size:20px;color:#50207A;border-bottom:2px solid #E5CCFF;padding-bottom:10px;display:flex;justify-content:space-between;align-items:center;}
button, .btn{background:#50207A;color:#fff;border:none;padding:10px 20px;border-radius:50px;font-size:14px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.3s ease;}
button:hover, .btn:hover{background:#D646FF;transform:scale(1.05);}
table{width:100%;border-collapse:collapse;margin-top:15px;}
table, th, td{border:1px solid #ccc;}
th, td{padding:12px;text-align:left;}
th{background:#50207A;color:#fff;}
img.product-thumb{width:60px;height:60px;object-fit:cover;border-radius:6px;}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:20px;}
.stat{background:#f7f0ff;padding:20px;border-radius:15px;text-align:center;box-shadow:0 0 8px #D646FF;}
.feedback-msg{color:green;font-weight:600;margin-bottom:10px;}
.delete-btn{color:red;cursor:pointer;}
.order-card{background:#f9f4ff;padding:15px;margin-bottom:15px;border-radius:12px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
.order-header{display:flex;justify-content:space-between;align-items:center;cursor:pointer;}
.order-header h4{margin:0;}
.order-details{display:none;margin-top:10px;}
.order-details table{width:100%;border:1px solid #ccc;border-radius:10px;overflow:hidden;}
.order-icons{display:flex;gap:10px;align-items:center;margin-top:5px;}
.order-icons a,.delivery-partner{color:#50207A;font-size:18px;transition:0.3s;cursor:pointer;}
.order-icons a:hover,.delivery-partner:hover{color:#D646FF;}
.notice{margin-top:20px;padding:15px;background:#f9f4ff;border-left:5px solid #50207A;border-radius:8px;font-size:14px;color:#50207A;line-height:1.5;}
.order-box{max-height:400px;overflow-y:auto;}
</style>
</head>
<body>

<div class="container">

<!-- Sidebar -->
<div class="sidebar">
<a href="profile.php"><img class="avatar" src="<?= !empty($user['avatar']) ? 'uploads/avatars/'.$user['avatar'] : 'assets/images/user-avatar.png'; ?>"></a>
<a href="profile.php">
   <h2>
<?= htmlspecialchars($seller['store_name'] ?? 'Create Your Store'); ?>
</h2>
</a>
<p><?= htmlspecialchars($user['phone']) ?></p>
<div class="menu">
<a href="#" class="active"><i class="fa fa-store"></i> Seller Dashboard</a>
<a href="manage_store.php"><i class="fa fa-edit"></i> Manage Store</a>
<a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>
<div class="notice">
<p><strong>Notice:</strong> As a seller on Gift Galaxy:</p>
<ul>
<li><strong>Revenue Crediting:</strong> Your revenue is credited only after delivery confirmation by the customer or delivery partner.</li>
<li><strong>Items Sold / Revenue:</strong> Only delivered orders count towards Items Sold and Total Revenue.</li>
<li><strong>Order Finality:</strong> All orders are final sale; refunds or cancellations are not available at this early stage.</li>
<li>Please maintain accurate stock, high-quality products, and respond promptly to buyer queries to avoid delays or complaints.</li>
<li>Delivery partner details for each order are shown as truck icons above. Hover over the icon to see which partner was used.</li>
</ul>
</div>
</div>

<!-- Main -->
<div class="main">

<?php if(!$seller): ?>
<div class="section">
<h3>👤 Create Seller Account</h3>
<form method="POST">
<label>Store Name <span>*</span></label>
<input type="text" name="store_name" required>
<label>Store Description</label>
<textarea name="store_desc"></textarea>
<button type="submit" name="create_seller">Create Seller Account</button>
</form>
</div>
<?php else: ?>

<!-- Analytics -->
<div class="section">
<h3>📊 Seller Analytics
<form method="GET" style="margin-left:auto;">
<select name="filter" onchange="this.form.submit()" style="padding:6px;border-radius:8px;">
<option value="all" <?= $filter=='all'?'selected':'' ?>>All Time</option>
<option value="30days" <?= $filter=='30days'?'selected':'' ?>>Last 30 Days</option>
<option value="1year" <?= $filter=='1year'?'selected':'' ?>>Last 1 Year</option>
</select>
</form>
</h3>

<div class="stats">
<div class="stat">
<h2><?= (int)($stats['total_orders'] ?? 0) ?></h2>
<p>Total Orders</p>
</div>

<div class="stat">
<h2><?= (int)($stats['total_items'] ?? 0) ?></h2>
<p>Items Sold</p>
</div>

<div class="stat">
<h2>₹<?= number_format($stats['total_revenue'] ?? 0,2) ?></h2>
<p>Total Revenue</p>
</div>
</div>
</div>

<!-- Products Table -->
<div class="section">
<h3>📦 My Products
<a href="add_product.php" class="btn"><i class="fa fa-plus"></i> Add Product</a>
</h3>
<table class="datatable">
<thead>
<tr>
<th>ID</th>
<th>Image</th>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while($p = mysqli_fetch_assoc($products)):
$img = !empty($p['product_image']) && file_exists('uploads/products/'.$p['product_image']) ? 'uploads/products/'.$p['product_image'] : 'assets/images/default-product.png';
$categoryName = $categoryMap[$p['category_id']] ?? 'Uncategorized';
?>
<tr>
<td><?= $p['id'] ?></td>
<td><img src="<?= $img ?>" class="product-thumb"></td>
<td><?= htmlspecialchars($p['product_name']) ?></td>
<td><?= htmlspecialchars($categoryName) ?></td>
<td>₹<?= number_format($p['product_price'],2) ?></td>
<td><?= $p['stock'] ?></td>
<td>
<a href="edit_product.php?id=<?= $p['id'] ?>">Edit</a> |
<a href="delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- Feedback -->
<div class="section">
<h3>💬 Feedback / Reviews</h3>
<table class="datatable">
<thead>
<tr>
<th>Product</th>
<th>User</th>
<th>Rating</th>
<th>Comment</th>
<th>Date</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while($f = mysqli_fetch_assoc($feedbacks)): ?>
<tr id="review-<?= $f['id'] ?>">
<td><?= htmlspecialchars($f['product_name']) ?></td>
<td><?= htmlspecialchars($f['username'] ?? 'User') ?></td>
<td><?= $f['rating'] ?? 5 ?>/5</td>
<td><?= htmlspecialchars($f['comment']) ?></td>
<td><?= $f['created_at'] ?? '' ?></td>
<td><span class="delete-btn" data-id="<?= $f['id'] ?>">Remove</span></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>

<!-- Pending Orders -->
<div class="section">
<h3>🧾 Orders & Customers (Pending)</h3>
<div class="order-box">
<?php
$pending_orders = mysqli_query($conn, "
    SELECT oi.*, o.id as order_id, o.wrap_type, o.message, o.from_name, o.recipient_name, o.phone, o.address, o.city, o.state, o.pincode,
           o.payment_method, o.total_amount, o.created_at, o.delivery_status, o.awb_code,
           p.product_name, p.product_image
    FROM order_items_gg oi
    JOIN orders_new o ON oi.order_id=o.id
    JOIN products p ON oi.product_id=p.id
    WHERE oi.seller_id='$seller_id' AND o.delivery_status='Pending'
    ORDER BY o.created_at DESC
");
if(mysqli_num_rows($pending_orders) > 0):
while($o = mysqli_fetch_assoc($pending_orders)):
$order_img = !empty($o['product_image']) && file_exists('uploads/products/'.$o['product_image']) ? 'uploads/products/'.$o['product_image'] : 'assets/images/default-product.png';
?>
<div class="order-card">
<div class="order-header">
<h4>Order #<?= $o['order_id'] ?> | <?= htmlspecialchars($o['product_name']) ?></h4>
<div class="order-icons">
<a href="deliverypartner.php?order_id=<?= $o['order_id'] ?>" title="Open Delivery Partner Page"><i class="fa fa-truck"></i></a>
<a href="print_invoice.php?order_id=<?= $o['order_id'] ?>" target="_blank" title="Print Invoice"><i class="fa fa-print"></i></a>
<span style="margin-left:15px;cursor:pointer;" class="toggle-details"><i class="fa fa-chevron-down"></i></span>
</div>
</div>

<div class="order-details">
<table>
<tr><th>Image</th><td><img src="<?= $order_img ?>" class="product-thumb"></td></tr>
<tr><th>Quantity</th><td><?= $o['quantity'] ?></td></tr>
<tr><th>Price</th><td>₹<?= number_format($o['price'],2) ?></td></tr>
<tr><th>Payment</th><td><?= htmlspecialchars($o['payment_method']) ?></td></tr>
<tr><th>Wrap</th><td><?= htmlspecialchars($o['wrap_type']) ?></td></tr>
<tr><th>Message</th><td><?= htmlspecialchars($o['message']) ?></td></tr>
<tr><th>From</th><td><?= htmlspecialchars($o['from_name']) ?></td></tr>
<tr><th>Recipient</th><td><?= htmlspecialchars($o['recipient_name']) ?></td></tr>
<tr><th>Phone</th><td><?= htmlspecialchars($o['phone']) ?></td></tr>
<tr><th>Address</th><td><?= htmlspecialchars($o['address']) ?></td></tr>
<tr><th>City</th><td><?= htmlspecialchars($o['city']) ?></td></tr>
<tr><th>State</th><td><?= htmlspecialchars($o['state']) ?></td></tr>
<tr><th>Pincode</th><td><?= htmlspecialchars($o['pincode']) ?></td></tr>
<tr><th>Ordered On</th><td><?= $o['created_at'] ?></td></tr>
<tr><th>AWB</th><td><?= htmlspecialchars($o['awb_code'] ?? '-') ?></td></tr>
</table>
</div>
</div>
<?php endwhile; else: ?><p>No pending orders.</p><?php endif; ?>
</div>
</div>

<!-- Shipped Products -->
<div class="section">
<h3>📦 Shipped Products</h3>
<div class="order-box">
<?php
$shipped_orders = mysqli_query($conn, "
    SELECT oi.*, o.id as order_id, o.wrap_type, o.message, o.from_name, o.recipient_name, o.phone, o.address, o.city, o.state, o.pincode,
           o.payment_method, o.total_amount, o.created_at, o.delivery_status, o.awb_code,
           p.product_name, p.product_image
    FROM order_items_gg oi
    JOIN orders_new o ON oi.order_id=o.id
    JOIN products p ON oi.product_id=p.id
    WHERE oi.seller_id='$seller_id' AND o.delivery_status='Shipped'
    ORDER BY o.created_at DESC
");
if(mysqli_num_rows($shipped_orders) > 0):
while($so = mysqli_fetch_assoc($shipped_orders)):
$order_img = !empty($so['product_image']) && file_exists('uploads/products/'.$so['product_image']) ? 'uploads/products/'.$so['product_image'] : 'assets/images/default-product.png';
?>
<div class="order-card">
<div class="order-header">
<h4>Order #<?= $so['order_id'] ?> | <?= htmlspecialchars($so['product_name']) ?></h4>
<div class="order-icons">
<a href="deliverypartner.php?order_id=<?= $so['order_id'] ?>" title="Open Delivery Partner Page"><i class="fa fa-truck"></i></a>
<a href="print_invoice.php?order_id=<?= $so['order_id'] ?>" target="_blank" title="Print Invoice"><i class="fa fa-print"></i></a>
<i class="fa fa-barcode delivery-partner" title="AWB: <?= htmlspecialchars($so['awb_code'] ?? '-') ?>"></i>
<span style="margin-left:15px;cursor:pointer;" class="toggle-details"><i class="fa fa-chevron-down"></i></span>
</div>
</div>

<div class="order-details">
<table>
<tr><th>Image</th><td><img src="<?= $order_img ?>" class="product-thumb"></td></tr>
<tr><th>Quantity</th><td><?= $so['quantity'] ?></td></tr>
<tr><th>Price</th><td>₹<?= number_format($so['price'],2) ?></td></tr>
<tr><th>Payment</th><td><?= htmlspecialchars($so['payment_method']) ?></td></tr>
<tr><th>Wrap</th><td><?= htmlspecialchars($so['wrap_type']) ?></td></tr>
<tr><th>Message</th><td><?= htmlspecialchars($so['message']) ?></td></tr>
<tr><th>From</th><td><?= htmlspecialchars($so['from_name']) ?></td></tr>
<tr><th>Recipient</th><td><?= htmlspecialchars($so['recipient_name']) ?></td></tr>
<tr><th>Phone</th><td><?= htmlspecialchars($so['phone']) ?></td></tr>
<tr><th>Address</th><td><?= htmlspecialchars($so['address']) ?></td></tr>
<tr><th>City</th><td><?= htmlspecialchars($so['city']) ?></td></tr>
<tr><th>State</th><td><?= htmlspecialchars($so['state']) ?></td></tr>
<tr><th>Pincode</th><td><?= htmlspecialchars($so['pincode']) ?></td></tr>
<tr><th>Ordered On</th><td><?= $so['created_at'] ?></td></tr>
<tr><th>AWB</th><td><?= htmlspecialchars($so['awb_code'] ?? '-') ?></td></tr>
</table>
</div>
</div>
<?php endwhile; else: ?><p>No shipped orders yet.</p><?php endif; ?>
</div>
</div>

<!-- Delivered Products -->
<div class="section">
<h3>✅ Delivered Products</h3>
<div class="order-box">
<?php
$delivered_orders = mysqli_query($conn, "
    SELECT oi.*, o.id as order_id, o.wrap_type, o.message, o.from_name, o.recipient_name, o.phone, o.address, o.city, o.state, o.pincode,
           o.payment_method, o.total_amount, o.created_at, o.delivery_status, o.awb_code,
           p.product_name, p.product_image
    FROM order_items_gg oi
    JOIN orders_new o ON oi.order_id=o.id
    JOIN products p ON oi.product_id=p.id
    WHERE oi.seller_id='$seller_id' AND o.delivery_status='Delivered'
    ORDER BY o.created_at DESC
");
if(mysqli_num_rows($delivered_orders) > 0):
while($do = mysqli_fetch_assoc($delivered_orders)):
$order_img = !empty($do['product_image']) && file_exists('uploads/products/'.$do['product_image']) ? 'uploads/products/'.$do['product_image'] : 'assets/images/default-product.png';
?>
<div class="order-card">
<div class="order-header">
<h4>Order #<?= $do['order_id'] ?> | <?= htmlspecialchars($do['product_name']) ?></h4>
<div class="order-icons">
<a href="print_invoice.php?order_id=<?= $do['order_id'] ?>" target="_blank" title="Print Invoice"><i class="fa fa-print"></i></a>
<i class="fa fa-check delivery-partner" title="Delivered"></i>
<span style="margin-left:15px;cursor:pointer;" class="toggle-details"><i class="fa fa-chevron-down"></i></span>
</div>
</div>

<div class="order-details">
<table>
<tr><th>Image</th><td><img src="<?= $order_img ?>" class="product-thumb"></td></tr>
<tr><th>Quantity</th><td><?= $do['quantity'] ?></td></tr>
<tr><th>Price</th><td>₹<?= number_format($do['price'],2) ?></td></tr>
<tr><th>Payment</th><td><?= htmlspecialchars($do['payment_method']) ?></td></tr>
<tr><th>Wrap</th><td><?= htmlspecialchars($do['wrap_type']) ?></td></tr>
<tr><th>Message</th><td><?= htmlspecialchars($do['message']) ?></td></tr>
<tr><th>From</th><td><?= htmlspecialchars($do['from_name']) ?></td></tr>
<tr><th>Recipient</th><td><?= htmlspecialchars($do['recipient_name']) ?></td></tr>
<tr><th>Phone</th><td><?= htmlspecialchars($do['phone']) ?></td></tr>
<tr><th>Address</th><td><?= htmlspecialchars($do['address']) ?></td></tr>
<tr><th>City</th><td><?= htmlspecialchars($do['city']) ?></td></tr>
<tr><th>State</th><td><?= htmlspecialchars($do['state']) ?></td></tr>
<tr><th>Pincode</th><td><?= htmlspecialchars($do['pincode']) ?></td></tr>
<tr><th>Ordered On</th><td><?= $do['created_at'] ?></td></tr>
<tr><th>AWB</th><td><?= htmlspecialchars($do['awb_code'] ?? '-') ?></td></tr>
</table>
</div>
</div>
<?php endwhile; else: ?><p>No delivered orders yet.</p><?php endif; ?>
</div>
</div>

<?php endif; ?>
</div>
</div>

<script>
$(document).ready(function(){
    $('.datatable').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "pageLength": 10,
        "lengthMenu": [5,10,25,50,100],
        "order": [[0, "desc"]]
    });

    $('.delete-btn').click(function(){
        if(confirm("Are you sure you want to remove this review?")){
            let review_id = $(this).data('id');
            $.post('<?= $_SERVER['PHP_SELF'] ?>', {delete_review_id: review_id}, function(res){
                if(res.trim()=='success'){
                    $('#review-'+review_id).fadeOut();
                }
            });
        }
    });

    $('.toggle-details').click(function(){
        $(this).closest('.order-card').find('.order-details').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
    });
});
</script>

</body>
</html>
