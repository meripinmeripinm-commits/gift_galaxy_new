<?php
include 'config.php';
$data=json_decode(file_get_contents("php://input"),true);

mysqli_query($conn,"INSERT INTO orders
(product_id,wrap_price,message,total,payment_method)
VALUES(
'{$data['product_id']}',
'{$data['wrap_price']}',
'".mysqli_real_escape_string($conn,$data['message'])."',
'{$data['total']}',
'{$data['payment']}'
)");

// Fetch product info
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'"));
if(!$product){
    die("Invalid product selected.");
}

$seller_id = $product['seller_id'];
$price = $product['product_price'];

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert into orders table
    $order_sql = "INSERT INTO orders (user_id, payment_method, status, created_at) 
                  VALUES ('$user_id', '".mysqli_real_escape_string($conn,$payment_method)."', 'Pending', NOW())";
    mysqli_query($conn, $order_sql);
    $order_id = mysqli_insert_id($conn);

    // Insert into order_items
    $item_sql = "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price)
                 VALUES ('$order_id', '$product_id', '$seller_id', '$quantity', '$price')";
    mysqli_query($conn, $item_sql);

    // Commit transaction
    mysqli_commit($conn);
    
    // Reduce stock
    mysqli_query($conn, "UPDATE products SET stock = stock - $quantity WHERE id='$product_id'");

    // Order confirmation page
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <title>Order Confirmation</title>
    <style>
    body{font-family:'Segoe UI',sans-serif;background:#f5f6fb;}
    .container{max-width:600px;margin:50px auto;background:#fff;padding:30px;border-radius:20px;box-shadow:0 15px 30px rgba(0,0,0,0.1);text-align:center;}
    .btn{margin-top:20px;padding:12px 20px;background:#50207A;color:#fff;border:none;border-radius:10px;cursor:pointer;text-decoration:none;display:inline-block;}
    </style>
    </head>
    <body>
    <div class="container">
        <h2>🎉 Order Confirmed!</h2>
        <p><strong>Order ID:</strong> #<?= $order_id ?></p>
        <p><strong>Product:</strong> <?= htmlspecialchars($product['product_name']) ?></p>
        <p><strong>Quantity:</strong> <?= $quantity ?></p>
        <p><strong>Price:</strong> ₹<?= number_format($price * $quantity,2) ?></p>
        <p><strong>Payment Method:</strong> <?= strtoupper($payment_method) ?></p>
        <?php if(!empty($receiver_name)): ?>
        <h3>Receiver Details</h3>
        <p><strong>Name:</strong> <?= htmlspecialchars($receiver_name) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($receiver_phone) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
        <p><strong>Message:</strong> <?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <a href="index.php" class="btn">Back to Home</a>
    </div>
    </body>
    </html>
    <?php

} catch(Exception $e){
    mysqli_rollback($conn);
    die("Error processing order: " . $e->getMessage());
}
?>
