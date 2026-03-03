<?php
session_start();
include 'config.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

// Validate ID from URL
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("Location: admin.php");
    exit();
}
$id = intval($_GET['id']);

// Fetch product using prepared statement
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows == 0){
    header("Location: admin.php");
    exit();
}
$product = $result->fetch_assoc();

// Upload folder
$upload_dir = "uploads/";
if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// Handle update
if(isset($_POST['update_product'])){
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    if(isset($_FILES['image']) && $_FILES['image']['name'] != ""){
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        $target = $upload_dir . $image_name;

        if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
            // Delete old image safely
            if(file_exists($upload_dir . $product['image'])){
                unlink($upload_dir . $product['image']);
            }
            // Update product with new image using prepared statement
            $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, description=?, price=?, image=? WHERE id=?");
            $stmt->bind_param("sssddi", $name, $brand, $description, $price, $image_name, $id);
            $stmt->execute();
        }
    } else {
        // Update product without changing image
        $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, description=?, price=? WHERE id=?");
        $stmt->bind_param("sssdi", $name, $brand, $description, $price, $id);
        $stmt->execute();
    }

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product - Gift Galaxy</title>
<style>
body { font-family:'Segoe UI',sans-serif; background:#f4f4f9; margin:0; padding:20px; }
.sidebar{ position:fixed; width:220px; height:100%; background:#50207A; color:#fff; padding-top:20px; }
.sidebar h2{text-align:center;margin-bottom:30px;}
.sidebar a{display:block;color:#D6B9FC;text-decoration:none;padding:15px 20px;transition:0.3s;}
.sidebar a:hover{background:#6F5BB5;color:#fff;}
.main{margin-left:220px;padding:30px;}
.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;}
.header h1{color:#50207A;}
.header a.logout{text-decoration:none;background:#D646FF;color:#fff;padding:10px 20px;border-radius:5px;transition:0.3s;}
.header a.logout:hover{background:#BF00FF;}
.card{background:#fff;padding:25px;border-radius:10px;box-shadow:0 0 15px rgba(0,0,0,0.1);}
.card h2{margin-bottom:20px;color:#50207A;}
.card input, .card textarea{width:100%;padding:12px;margin-bottom:15px;border-radius:5px;border:1px solid #ccc;}
.card button{background:#50207A;color:#fff;padding:12px 20px;border:none;border-radius:5px;cursor:pointer;font-weight:bold;transition:0.3s;}
.card button:hover{background:#6F5BB5;}
#preview{max-width:200px;margin-top:10px;border-radius:5px;transition:0.3s;}
#preview:hover{transform:scale(1.2);}
@media(max-width:768px){.sidebar{width:100%;position:relative;}.main{margin-left:0;padding:20px;}}
</style>
<script>
// Live image preview
function previewImage(event){
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('preview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</head>
<body>

<div class="sidebar">
    <h2>Gift Galaxy</h2>
    <a href="admin.php">Dashboard</a>
</div>

<div class="main">
    <div class="header">
        <h1>Edit Product</h1>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="card">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="Product Name" required>
            <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" placeholder="Brand" required>
            <textarea name="description" placeholder="Description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" placeholder="Price" required>

            <p>Current Image:</p>
            <?php if($product['image'] != "") { ?>
                <img src="uploads/<?php echo $product['image']; ?>" id="preview" alt="Product Image">
            <?php } else { ?>
                <img id="preview" alt="No Image">
            <?php } ?>

            <p>Change Image (optional):</p>
            <input type="file" name="image" accept="image/*" onchange="previewImage(event)">

            <button type="submit" name="update_product">Update Product</button>
        </form>
    </div>
</div>
</body>
</html>
