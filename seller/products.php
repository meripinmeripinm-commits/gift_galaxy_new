<?php
session_start();
include '../config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['is_seller'] != 1){
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$products = mysqli_query($conn, "SELECT * FROM products WHERE seller_id='$seller_id'");
?>
<!DOCTYPE html>
<html>
<head>
<title>My Products</title>
<style>
body{font-family:Segoe UI;background:#f4f6fb;padding:30px;}
table{width:100%;background:#fff;border-collapse:collapse;}
th,td{padding:12px;border-bottom:1px solid #ddd;}
th{background:#50207A;color:#fff;}
</style>
</head>
<body>

<h2>My Products</h2>

<table>
<tr>
<th>Name</th>
<th>Price</th>
<th>Image</th>
</tr>

<?php while($p=mysqli_fetch_assoc($products)){ ?>
<tr>
<td><?php echo $p['name']; ?></td>
<td>₹<?php echo $p['price']; ?></td>
<td><img src="../uploads/<?php echo $p['image']; ?>" width="60"></td>
</tr>
<?php } ?>

</table>
</body>
</html>
