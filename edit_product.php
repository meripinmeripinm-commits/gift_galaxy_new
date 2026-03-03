<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
$seller = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sellers WHERE user_id='$user_id'"));

if(!$seller) die("You are not registered as a seller.");

$product_id = intval($_GET['id'] ?? 0);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id' AND seller_id='".$seller['id']."'"));

if(!$product) die("Product not found or access denied.");

/* Fetch categories */
$categories = [];
$catRes = mysqli_query($conn, "SELECT * FROM categories");
while($c = mysqli_fetch_assoc($catRes)) $categories[] = $c;

/* Fetch existing gift wraps */
$gift_wraps = [];
$wrapRes = mysqli_query($conn, "SELECT * FROM gift_wraps WHERE product_id='$product_id'");
while($w = mysqli_fetch_assoc($wrapRes)) $gift_wraps[] = $w;

$errors = [];
$success = '';

/* AJAX Delete Wrap */
if(isset($_POST['delete_wrap_id'])){
    $wrap_id = intval($_POST['delete_wrap_id']);
    $wrap = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM gift_wraps WHERE id='$wrap_id' AND product_id='$product_id'"));
    if($wrap){
        if(file_exists('uploads/gift_wraps/'.$wrap['image'])) unlink('uploads/gift_wraps/'.$wrap['image']);
        mysqli_query($conn,"DELETE FROM gift_wraps WHERE id='$wrap_id'");
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

/* Handle product update */
if(isset($_POST['update_product'])){
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name'] ?? '');
    $category_id  = intval($_POST['category'] ?? 0);
    $description  = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $price        = floatval($_POST['price'] ?? 0);
    $stock        = intval($_POST['stock'] ?? 0);

    $imageFields = ['product_image','image2','image3','image4','image5'];
    $uploadedImages = [];

    foreach($imageFields as $field){
        $uploadedImages[$field] = $product[$field] ?? '';

        if(!empty($_FILES[$field]['name'])){
            $tmp = $_FILES[$field]['tmp_name'];
            $mime = mime_content_type($tmp);
            $allowed = ['image/jpeg','image/png','image/webp'];
            if(!in_array($mime,$allowed)){
                $errors[] = "Invalid image type for $field. Allowed: JPG, PNG, WEBP";
                continue;
            }
            $ext = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime];
            $safeName = preg_replace("/[^a-zA-Z0-9_-]/","",pathinfo($_FILES[$field]['name'], PATHINFO_FILENAME));
            $filename = $safeName.'_'.time().'_'.$product_id.'.'.$ext;
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

            $uploadedImages[$field] = $filename;
        }
    }

    if(empty($product_name) || $category_id <= 0 || $price <= 0 || $stock < 0){
        $errors[] = "Please fill all required fields.";
    }

    /* Process new gift wraps */
    if(isset($_POST['wrap_color']) && isset($_FILES['wrap_image'])){
        foreach($_POST['wrap_color'] as $index => $wrap_color){
            $wrap_color = trim(mysqli_real_escape_string($conn, $wrap_color));
            $wrap_price = floatval($_POST['wrap_price'][$index] ?? 0);
            $wrap_file  = $_FILES['wrap_image']['tmp_name'][$index] ?? '';
            $wrap_name  = $_FILES['wrap_image']['name'][$index] ?? '';

            if($wrap_color != '' && $wrap_file != ''){
                $ext = pathinfo($wrap_name, PATHINFO_EXTENSION);
                $filename = uniqid()."_".$product_id.".".$ext;
                $target = "uploads/gift_wraps/".$filename;
                if(!file_exists('uploads/gift_wraps')) mkdir('uploads/gift_wraps', 0777, true);

                if(move_uploaded_file($wrap_file, $target)){
                    mysqli_query($conn,"INSERT INTO gift_wraps (product_id,color,image,price) VALUES ('$product_id','$wrap_color','$filename','$wrap_price')");
                }
            }
        }
    }

    if(empty($errors)){
        $sql = "UPDATE products SET 
                    product_name='$product_name',
                    category_id='$category_id',
                    product_description='$description',
                    product_price='$price',
                    stock='$stock',
                    product_image='".$uploadedImages['product_image']."',
                    image2='".$uploadedImages['image2']."',
                    image3='".$uploadedImages['image3']."',
                    image4='".$uploadedImages['image4']."',
                    image5='".$uploadedImages['image5']."'
                WHERE id='$product_id' AND seller_id='".$seller['id']."'";

        if(mysqli_query($conn,$sql)){
            $success = "Product updated successfully!";
            header("Location: seller.php");
            exit;
        } else {
            $errors[] = "Database error: ".mysqli_error($conn);
        }
    }
}

/* Helper for image display */
function getImage($file){
    return !empty($file) && file_exists('uploads/products/'.$file) ? 'uploads/products/'.$file : '';
}
function getWrapImage($file){
    return !empty($file) && file_exists('uploads/gift_wraps/'.$file) ? 'uploads/gift_wraps/'.$file : '';
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Edit Product | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* CSS same as before */
*{box-sizing:border-box;}
body{margin:0;font-family:Segoe UI,sans-serif;background:#f2f3f7;}
.container{max-width:1200px;margin:40px auto;display:grid;grid-template-columns:280px 1fr;gap:25px;}
@media(max-width:900px){.container{grid-template-columns:1fr;}}
.sidebar,.main{background:#fff;border-radius:18px;box-shadow:0 12px 30px rgba(0,0,0,.1);}
.sidebar{padding:25px;text-align:center;}
.avatar{width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #50207A;cursor:pointer;}
.menu{margin-top:25px;text-align:left;}
.menu a{display:block;padding:12px;border-radius:10px;font-weight:600;color:#333;text-decoration:none;margin-bottom:5px;}
.menu a:hover{background:#f3e9ff;}
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
.current-img{width:100px;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,0.1);margin-bottom:15px;}
.category-select{display:flex;flex-wrap:wrap;gap:15px;margin-bottom:20px;}
.category-select label{cursor:pointer;flex:0 0 90px;text-align:center;transition:.3s;perspective:800px;}
.category-select img{width:80px;height:80px;border-radius:50%;border:3px solid #50207A;object-fit:cover;transition:.3s;}
.category-select input[type=radio]{display:none;}
.category-select input[type=radio]:checked + img{transform: rotateY(15deg) rotateX(10deg) scale(1.05);border-color:#D646FF;}
.category-select label:hover img{transform: rotateY(15deg) rotateX(10deg) scale(1.05);border-color:#FFD6FF;}
.category-select div{margin-top:6px;font-size:13px;}
.wrap-item{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.wrap-item img{width:60px;height:60px;border-radius:10px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.2);}
.wrap-item span{font-weight:600;}
.wrap-item button{background:#ff4d4d;color:#fff;border:none;padding:3px 8px;border-radius:5px;cursor:pointer;}
</style>
</head>
<body>
<div class="container">
<!-- Sidebar -->
<div class="sidebar">
<img class="avatar" src="<?= !empty($user['avatar']) ? 'uploads/avatars/'.$user['avatar'] : 'assets/images/user-avatar.png'; ?>">
<h2><?= htmlspecialchars($user['username']) ?></h2>
<p><?= htmlspecialchars($user['phone']) ?></p>
<a href="seller.php" class="start-selling"><i class="fa fa-store"></i> Seller Dashboard</a>
<div class="menu">
<a href="#"><i class="fa fa-box"></i> My Orders</a>
<a href="#"><i class="fa fa-heart"></i> Wishlist</a>
<a href="#"><i class="fa fa-map-marker-alt"></i> Addresses</a>
<a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>
</div>

<!-- Main -->
<div class="main">
<h2>✏️ Edit Product</h2>
<?php if(!empty($errors)): ?><div class="alert"><?php foreach($errors as $e) echo $e."<br>"; ?></div><?php endif; ?>
<?php if(!empty($success)): ?><div class="success"><?= $success ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<label>Product Name <span>*</span></label>
<input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']); ?>" required>

<label>Category <span>*</span></label>
<div class="category-select">
<?php foreach($categories as $cat): 
    $catImg = !empty($cat['image']) && file_exists('uploads/categories/'.$cat['image']) ? 'uploads/categories/'.$cat['image'] : 'uploads/categories/default.png';
?>
<label title="<?= $cat['name']; ?>">
    <input type="radio" name="category" value="<?= $cat['id']; ?>" <?= ($product['category_id']==$cat['id'])?'checked':''; ?> required>
    <img src="<?= $catImg; ?>" alt="<?= $cat['name']; ?>">
    <div><?= $cat['name']; ?></div>
</label>
<?php endforeach; ?>
</div>

<label>Description</label>
<textarea name="description"><?= htmlspecialchars($product['product_description']); ?></textarea>

<label>Price <span>*</span></label>
<input type="number" step="0.01" name="price" value="<?= $product['product_price']; ?>" required>

<label>Stock <span>*</span></label>
<input type="number" name="stock" value="<?= $product['stock']; ?>" required>

<?php
$imageFields = ['product_image','image2','image3','image4','image5'];
foreach($imageFields as $field):
    $imgPath = getImage($product[$field] ?? '');
?>
<label><?= ucfirst(str_replace('_',' ',$field)) ?></label>
<?php if($imgPath): ?>
<img src="<?= $imgPath ?>" class="current-img">
<?php else: ?>
<p>No image uploaded</p>
<?php endif; ?>
<input type="file" name="<?= $field ?>" accept="image/*">
<?php endforeach; ?>

<h3>🎁 Gift Wrapping Options</h3>
<div id="wraps">
<?php foreach($gift_wraps as $wrap): ?>
<div class="wrap-item" id="wrap-<?= $wrap['id'] ?>">
<img src="<?= getWrapImage($wrap['image']); ?>" alt="">
<span><?= htmlspecialchars($wrap['color']); ?> - ₹<?= number_format($wrap['price'],2) ?></span>
<button type="button" onclick="deleteWrap(<?= $wrap['id'] ?>)">Delete</button>
</div>
<?php endforeach; ?>
</div>

<button type="button" onclick="addWrap()">+ Add Wrap</button>
<div id="wrap_inputs"></div>

<br>
<button type="submit" name="update_product"><i class="fa fa-save"></i> Update Product</button>
</form>
</div>
</div>

<script>
let wrapIndex = 0;
function addWrap(){
    const container = document.getElementById('wrap_inputs');
    const html = `
        <div class="wrap-item">
            <input type="text" name="wrap_color[]" placeholder="Wrap Color" required>
            <input type="number" name="wrap_price[]" placeholder="Price (₹)" step="0.01" value="0" required>
            <input type="file" name="wrap_image[]" accept="image/*" required>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function deleteWrap(id){
    if(confirm('Are you sure you want to delete this wrap?')){
        const formData = new FormData();
        formData.append('delete_wrap_id', id);

        fetch('edit_product.php?id=<?= $product_id ?>',{
            method:'POST',
            body: formData
        })
        .then(res => res.text())
        .then(res => {
            if(res.trim() === 'success'){
                const elem = document.getElementById('wrap-'+id);
                elem.remove();
            } else {
                alert('Error deleting wrap.');
            }
        })
        .catch(err => alert('Error deleting wrap.'));
    }
}
</script>
</body>
</html>
