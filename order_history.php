<?php
session_start();
include 'config.php';

// For simplicity, we’ll assume user is logged in as 'customer_email' in session
if(!isset($_SESSION['customer_email'])){
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['customer_email'];

// Fetch all orders for this user
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE email='$user_email' ORDER BY order_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order History - Gift Galaxy</title>
<style>
body { margin:0; font-family:'Segoe UI',sans-serif; background:#f4f4f9; }
header{ background:#50207A; color:#fff; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; }
header h1{ margin:0; }
nav a{ color:#D6B9FC; text-decoration:none; margin-left:20px; font-weight:bold; }
nav a:hover{ color:#fff; }

.container{ max-width:1200px; margin:50px auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); }
h2{ color:#50207A; margin-bottom:20px; }
table{ width:100%; border-collapse:collapse; margin-bottom:30px; }
th, td{ padding:12px; border-bottom:1px solid #ccc; text-align:left; }
th{ background:#f4f4f9; }
a.button{ background:#50207A; color:#fff; padding:8px 15px; border-radius:5px; text-decoration:none; transition:0.3s; }
a.button:hover{ background:#6F5BB5; }
</style>
</head>
<body>

<header>
    <h1>Gift Galaxy</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Your Orders</h2>

    <?php if(mysqli_num_rows($orders) == 0){ ?>
        <p>You have not placed any orders yet. <a href="products.php" class="button">Shop Now</a></p>
    <?php } else { ?>
        <?php while($order = mysqli_fetch_assoc($orders)) { 
            $order_id = $order['id'];
            $items = mysqli_query($conn, "SELECT oi.*, p.name, p.image FROM order_items oi 
                                          JOIN products p ON oi.product_id=p.id 
                                          WHERE oi.order_id='$order_id'");
        ?>
        <div style="margin-bottom:40px; border-bottom:1px solid #ccc; padding-bottom:20px;">
            <h3>Order #<?php echo $order_id; ?> | Date: <?php echo date('d M Y', strtotime($order['order_date'])); ?> | Total: ₹<?php echo $order['total_price']; ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = mysqli_fetch_assoc($items)) { ?>
                        <tr>
                            <td>
                                <img src="uploads/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:50px; vertical-align:middle; margin-right:10px;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo $item['price']; ?></td>
                            <td>₹<?php echo $item['quantity'] * $item['price']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    <?php } ?>
</div>

</body>
</html>
