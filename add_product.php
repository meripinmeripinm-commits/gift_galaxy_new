<?php
session_start();
include 'config.php';

/* =============================== 
   LOGIN CHECK
============================== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Fetch user info */
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));

/* Check if user is a seller */
$seller = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sellers WHERE user_id='$user_id'"));

$errors = [];
$success = '';

/* Fetch categories dynamically */
$categories_result = mysqli_query($conn, "SELECT * FROM categories");
$categories = [];
while($c = mysqli_fetch_assoc($categories_result)){
    $categories[] = $c;
}

/* Handle Add Product form submission */
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $shipping_charge = isset($_POST['shipping_charge']) ? floatval($_POST['shipping_charge']) : 0;
    $is_customized = isset($_POST['is_customized']) ? 1 : 0;

    // Handle multiple images
    $images = ['product_image','image2','image3','image4','image5'];
    $uploadedImages = [];

    foreach($images as $imgField){
        if(!empty($_FILES[$imgField]['name'])){
            $tmp = $_FILES[$imgField]['tmp_name'];
            $mime = mime_content_type($tmp);
            $allowed = ['image/jpeg','image/png','image/webp'];

            if(!in_array($mime,$allowed)){
                $errors[] = "Invalid image type for $imgField. Allowed: JPG, PNG, WEBP";
                continue;
            }

            $ext = '';
            switch($mime){
                case 'image/jpeg': $ext='jpg'; break;
                case 'image/png': $ext='png'; break;
                case 'image/webp': $ext='webp'; break;
            }

            $safeName = preg_replace("/[^a-zA-Z0-9_-]/","",pathinfo($_FILES[$imgField]['name'], PATHINFO_FILENAME));
            $filename = $safeName.'_'.time().'_'.rand(1000,9999).'.'.$ext;
            $target = 'uploads/products/'.$filename;

            list($width,$height) = getimagesize($tmp);
            $dst = imagecreatetruecolor(400,400);

            switch($ext){
                case 'jpg': case 'jpeg': $src = imagecreatefromjpeg($tmp); break;
                case 'png': $src = imagecreatefrompng($tmp); imagealphablending($dst,false); imagesavealpha($dst,true); break;
                case 'webp': $src = imagecreatefromwebp($tmp); break;
            }

            imagecopyresampled($dst,$src,0,0,0,0,400,400,$width,$height);

            switch($ext){
                case 'jpg': case 'jpeg': imagejpeg($dst,$target,90); break;
                case 'png': imagepng($dst,$target,6); break;
                case 'webp': imagewebp($dst,$target,90); break;
            }

            imagedestroy($src);
            imagedestroy($dst);

            $uploadedImages[$imgField] = $filename;
        }
    }

    if(empty($product_name) || empty($category_id) || empty($price) || empty($stock)){
        $errors[] = "Please fill all required fields.";
    }

    if(empty($errors)){
        $sql = "INSERT INTO products (
                    seller_id,
                    category_id,
                    product_name,
                    product_description,
                    product_price,
                    stock,
                    shipping_charge,
                    product_image,
                    image2,
                    image3,
                    image4,
                    image5,
                    is_customized
                ) VALUES (
                    '".$seller['id']."',
                    '$category_id',
                    '$product_name',
                    '$description',
                    '$price',
                    '$stock',
                    '$shipping_charge',
                    '".($uploadedImages['product_image'] ?? '')."',
                    '".($uploadedImages['image2'] ?? '')."',
                    '".($uploadedImages['image3'] ?? '')."',
                    '".($uploadedImages['image4'] ?? '')."',
                    '".($uploadedImages['image5'] ?? '')."',
                    '$is_customized'
                )";

        if(mysqli_query($conn,$sql)){
            $success = "Product added successfully!";
            $_POST = [];
        } else {
            $errors[] = "Database error: ".mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Product | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{box-sizing:border-box;}
body{margin:0;font-family:Segoe UI, sans-serif;background:#f2f3f7;}
.container{max-width:1200px;margin:40px auto;display:grid;grid-template-columns:280px 1fr;gap:25px;}
@media(max-width:900px){.container{grid-template-columns:1fr;}}
.sidebar,.main{background:#fff;border-radius:18px;box-shadow:0 12px 30px rgba(0,0,0,.1);}
.sidebar{padding:25px;text-align:center;}
.avatar{width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #50207A;cursor:pointer;}
.menu{margin-top:25px;text-align:left;}
.menu p{background:#f9f2ff;color:#50207A;padding:10px;border-radius:10px;margin-bottom:5px;font-weight:600;}
.start-selling{display:block;margin:10px 0 15px;text-decoration:none;color:#50207A;font-weight:600;}
.main{padding:30px 35px;}
label{display:block;font-weight:600;margin:15px 0 6px;}
label span{color:red;}
input,textarea,select{width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;font-size:15px;margin-bottom:15px;}
textarea{height:100px;resize:none;}
button{background:#50207A;color:#fff;border:none;padding:14px 35px;border-radius:50px;font-size:16px;cursor:pointer;}
button:hover{background:#D646FF;}
.alert{background:#ffe0e0;color:#a00;padding:12px;border-radius:8px;margin-bottom:20px;font-weight:600;}
.success{background:#e3ffe8;color:#067a1c;padding:12px;border-radius:8px;margin-bottom:20px;font-weight:600;}
.category-select{display:flex;flex-wrap:wrap;gap:15px;margin-bottom:20px;}
.category-select label{cursor:pointer;flex:0 0 90px;text-align:center;transition:.3s;perspective:800px;}
.category-select img{width:80px;height:80px;border-radius:50%;border:3px solid #50207A;object-fit:cover;transition:.3s;}
.category-select input[type=radio]{display:none;}
.category-select input[type=radio]:checked + img{transform: rotateY(15deg) rotateX(10deg) scale(1.05);border-color:#D646FF;}
.category-select label:hover img{transform: rotateY(15deg) rotateX(10deg) scale(1.05);border-color:#FFD6FF;}
.category-select div{margin-top:6px;font-size:13px;}
.notice-text{font-size:14px;color:#a00;margin:10px 0;font-style:italic;}
</style>
</head>
<body>
<div class="container">

<div class="sidebar">
    <img class="avatar" src="<?= !empty($user['avatar']) ? 'uploads/avatars/'.$user['avatar'] : 'assets/images/user-avatar.png'; ?>">
    <h2><?= htmlspecialchars($user['username']) ?></h2>
    <p><?= htmlspecialchars($user['phone']) ?></p>
    <a href="seller.php" class="start-selling"><i class="fa fa-store"></i> Your Dashboard</a>
    <div class="menu">
        <p>💡 Tip: Add high-quality product images to attract buyers.</p>
        <p>📝 Tip: Provide accurate descriptions and stock details.</p>
        <p>🚚 Tip: Set shipping charge (0 = Free Shipping).</p>
        <p>🚀 Tip: Mark products as customizable for personalized gifts.</p>
    </div>
</div>

<div class="main">
    <h2>➕ Add New Product</h2>

    <?php if(!empty($errors)): ?>
        <div class="alert"><?php foreach($errors as $e) echo $e."<br>"; ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name <span>*</span></label>
        <input type="text" name="product_name" required>

        <label>Category <span>*</span></label>
        <div class="category-select">
            <?php foreach($categories as $cat):
                $catImg = !empty($cat['image']) && file_exists('uploads/categories/'.$cat['image']) ? 'uploads/categories/'.$cat['image'] : 'uploads/categories/default.png';
            ?>
            <label class="cat-card" title="<?= $cat['name']; ?>">
                <input type="radio" name="category" value="<?= $cat['id']; ?>" required>
                <img src="<?= $catImg; ?>" alt="<?= $cat['name']; ?>">
                <div><?= $cat['name']; ?></div>
            </label>
            <?php endforeach; ?>
        </div>

        <label>Description</label>
        <textarea name="description"></textarea>

        <label>Price <span>*</span></label>
        <input type="number" step="0.01" name="price" required>

        <label>Stock <span>*</span></label>
        <input type="number" name="stock" required>

        <label>Shipping Charge (₹)</label>
        <input type="number" step="0.01" name="shipping_charge" placeholder="0 for Free Shipping">

        <label>Product Image 1</label>
        <input type="file" name="product_image">
        <label>Product Image 2</label>
        <input type="file" name="image2">
        <label>Product Image 3</label>
        <input type="file" name="image3">
        <label>Product Image 4</label>
        <input type="file" name="image4">
        <label>Product Image 5</label>
        <input type="file" name="image5">

        <label><input type="checkbox" name="is_customized"> Customizable Product (User can add name, color, image, size)</label>

        <div class="notice-text">You can edit customization options in Edit Product page</div>

        <br>
        <button type="submit" name="add_product">Add Product</button>
    </form>
</div>

</div>
</body>
</html>
