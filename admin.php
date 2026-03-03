<?php
session_start();
include 'config.php';

/* Check admin login */
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

/* Fetch all data */
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
$sellers = mysqli_query($conn, "SELECT s.*, u.username, u.email FROM sellers s JOIN users u ON s.user_id=u.id ORDER BY s.id DESC");
$products = mysqli_query($conn, "SELECT p.*, s.username FROM products p JOIN sellers s ON p.seller_id=s.id ORDER BY p.id DESC");

/* Add Category */
$cat_errors = $success_cat = "";
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $tmp = $_FILES['image']['tmp_name'];
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = preg_replace("/[^a-zA-Z0-9_-]/", "", pathinfo($_FILES['image']['name'], PATHINFO_FILENAME)).'_'.time().'.'.$ext;
        move_uploaded_file($tmp,'uploads/categories/'.$image);
    }

    if ($name != '') {
        mysqli_query($conn,"INSERT INTO categories(name,image) VALUES('$name','$image')");
        $success_cat = "Category added successfully!";
        $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
    } else {
        $cat_errors = "Category name required!";
    }
}

/* Approve / Remove Seller */
if (isset($_GET['approve_seller'])) {
    $id = intval($_GET['approve_seller']);
    mysqli_query($conn,"UPDATE sellers SET approved=1 WHERE id=$id");
    header("Location: admin.php");
    exit;
}
if (isset($_GET['remove_seller'])) {
    $id = intval($_GET['remove_seller']);
    mysqli_query($conn,"DELETE FROM sellers WHERE id=$id");
    header("Location: admin.php");
    exit;
}

/* Delete Category */
if (isset($_GET['delete_category'])) {
    $id = intval($_GET['delete_category']);
    mysqli_query($conn,"DELETE FROM categories WHERE id=$id");
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Panel | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:#f5f6fb;}
.container{max-width:1200px;margin:40px auto; display:grid; grid-template-columns:300px 1fr; gap:25px;}
@media(max-width:900px){.container{grid-template-columns:1fr;}}
.sidebar, .main{background:#fff;border-radius:18px; box-shadow:0 12px 30px rgba(0,0,0,.1); padding:25px;}
.sidebar img{width:120px; height:120px; border-radius:50%; border:4px solid #50207A; object-fit:cover; margin-bottom:15px;}
.sidebar h2{color:#50207A;margin:10px 0;}
.sidebar a{display:block; margin:10px 0; color:#50207A; font-weight:600; text-decoration:none;}
.sidebar a:hover{color:#D646FF;}
.main h2{color:#50207A;margin-bottom:20px;}
input, select, textarea, button{width:100%; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ccc;}
button{background:#50207A; color:#fff; border:none; cursor:pointer;}
button:hover{background:#D646FF;}
table{width:100%; border-collapse:collapse; margin-bottom:20px;}
table, th, td{border:1px solid #ccc;}
th, td{padding:10px; text-align:left;}
th{background:#50207A;color:#fff;}
.success{background:#e3ffe8;color:#067a1c;padding:10px;border-radius:8px;margin-bottom:15px;}
.alert{background:#ffe0e0;color:#a00;padding:10px;border-radius:8px;margin-bottom:15px;}
.popup-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); display:none; align-items:center; justify-content:center; z-index:10000;}
.popup-content { background:#fff; border-radius:15px; padding:25px; text-align:center; max-width:400px; width:90%; position:relative;}
.popup-close{position:absolute; top:10px; right:15px; font-size:24px; cursor:pointer;}
</style>
</head>
<body>

<div class="container">
<!-- SIDEBAR -->
<div class="sidebar">
<h2>Admin Panel</h2>
<a href="#categories">Categories</a>
<a href="#sellers">Sellers</a>
<a href="#products">Products</a>
<a href="logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<h2 id="categories">Categories</h2>
<?php if($cat_errors) echo "<div class='alert'>$cat_errors</div>"; ?>
<?php if($success_cat) echo "<div class='success'>$success_cat</div>"; ?>
<form method="POST" enctype="multipart/form-data">
<input type="text" name="name" placeholder="Category Name" required>
<input type="file" name="image">
<button type="submit" name="add_category">Add Category</button>
</form>

<table>
<tr><th>ID</th><th>Name</th><th>Image</th><th>Action</th></tr>
<?php while($c=mysqli_fetch_assoc($categories)): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= $c['name'] ?></td>
<td>
<?php if($c['image']): ?>
<img src="uploads/categories/<?= $c['image'] ?>" width="50">
<?php else: ?>
N/A
<?php endif; ?>
</td>
<td>
<a href="admin.php?delete_category=<?= $c['id'] ?>" style="color:red;">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h2 id="sellers">Sellers</h2>
<table>
<tr><th>ID</th><th>Username</th><th>Email</th><th>Approved</th><th>Actions</th></tr>
<?php while($s=mysqli_fetch_assoc($sellers)): ?>
<tr>
<td><?= $s['id'] ?></td>
<td><?= $s['username'] ?></td>
<td><?= $s['email'] ?></td>
<td><?= $s['approved']?'Yes':'No' ?></td>
<td>
<?php if(!$s['approved']): ?>
<a href="admin.php?approve_seller=<?= $s['id'] ?>">Approve</a>
<?php endif; ?>
<a href="admin.php?remove_seller=<?= $s['id'] ?>" style="color:red;">Remove</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<h2 id="products">Products</h2>
<table>
<tr><th>ID</th><th>Name</th><th>Seller</th><th>Price</th><th>Stock</th></tr>
<?php while($p=mysqli_fetch_assoc($products)): ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= $p['product_name'] ?></td>
<td><?= $p['username'] ?></td>
<td>₹<?= number_format($p['price'],2) ?></td>
<td><?= $p['stock'] ?></td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

</body>
</html>
