<?php
session_start();
include 'config.php';

// Check admin login
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$errors = [];
$success = "";

if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image type. Allowed: JPG, PNG, WEBP";
        } else {
            $image = time().'_'.rand(1000,9999).'.'.$ext;
            if(!is_dir('uploads/categories')) mkdir('uploads/categories', 0755, true);
            move_uploaded_file($tmp, 'uploads/categories/'.$image);
        }
    }

    if (empty($name)) $errors[] = "Category name is required.";

    if (empty($errors)) {
        mysqli_query($conn, "INSERT INTO categories(name,image) VALUES('$name','$image')");
        $success = "Category added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Category | Gift Galaxy</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
body{font-family:Segoe UI;background:#f2f3f7;margin:0;padding:0;}
.container{max-width:600px;margin:50px auto;background:#fff;padding:30px;border-radius:15px;box-shadow:0 10px 25px rgba(0,0,0,.1);}
input,button{width:100%;padding:12px;margin-bottom:15px;border-radius:8px;border:1px solid #ccc;}
button{background:#50207A;color:#fff;border:none;cursor:pointer;}
.alert{background:#ffe0e0;color:#a00;padding:12px;border-radius:8px;}
.success{background:#e3ffe8;color:#067a1c;padding:12px;border-radius:8px;}
</style>
</head>
<body>
<div class="container">
<h2>➕ Add Category</h2>
<?php if(!empty($errors)) echo '<div class="alert">'.implode('<br>',$errors).'</div>'; ?>
<?php if(!empty($success)) echo '<div class="success">'.$success.'</div>'; ?>
<form method="POST" enctype="multipart/form-data">
<label>Category Name *</label>
<input type="text" name="name" required>

<label>Category Image</label>
<input type="file" name="image">

<button type="submit" name="add_category">Add Category</button>
</form>
</div>
</body>
</html>
