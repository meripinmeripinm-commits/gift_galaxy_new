<?php
session_start();
include 'config.php';

/* SELLER AUTH */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$seller_id = (int)$_SESSION['user_id'];

/* ADD WRAP */
if (isset($_POST['add_wrap'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $price = (float)$_POST['price'];

    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $image = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/wraps/".$image);
    }

    mysqli_query($conn, "
        INSERT INTO gift_wraps (seller_id, title, image, color, price)
        VALUES ($seller_id, '$title', '$image', '$color', $price)
    ");
}

/* TOGGLE STATUS */
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    mysqli_query($conn, "
        UPDATE gift_wraps 
        SET status = IF(status=1,0,1) 
        WHERE id=$id AND seller_id=$seller_id
    ");
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "
        DELETE FROM gift_wraps 
        WHERE id=$id AND seller_id=$seller_id
    ");
}

/* FETCH */
$wraps = mysqli_query($conn, "
    SELECT * FROM gift_wraps 
    WHERE seller_id=$seller_id 
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Gift Wraps | Seller Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{margin:0;font-family:Segoe UI;background:#f3f4f9}
.container{max-width:1100px;margin:40px auto;background:#fff;padding:30px;border-radius:22px;box-shadow:0 25px 70px rgba(0,0,0,.1)}
h1{color:#4B1B6D}
form{margin-bottom:30px}
input{padding:12px;border-radius:12px;border:1px solid #ddd;width:100%;margin-top:8px}
button{padding:14px 22px;border:none;border-radius:14px;background:#4B1B6D;color:#fff;font-weight:700;margin-top:12px;cursor:pointer}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
.card{border:2px solid #ddd;border-radius:18px;padding:14px;text-align:center}
.card img{width:100%;height:140px;object-fit:cover;border-radius:14px}
.actions a{margin:0 6px;font-size:14px;text-decoration:none;font-weight:700}
.on{color:green}
.off{color:red}
</style>
</head>

<body>

<div class="container">
<h1>🎁 My Gift Wraps</h1>

<form method="POST" enctype="multipart/form-data">
<input type="text" name="title" placeholder="Wrap Title" required>
<input type="text" name="color" placeholder="Color (Red / Gold / Pink)" required>
<input type="number" name="price" step="0.01" placeholder="Price" required>
<input type="file" name="image" required>
<button name="add_wrap">Add Gift Wrap</button>
</form>

<div class="grid">
<?php while($w=mysqli_fetch_assoc($wraps)){ ?>
<div class="card">
<img src="uploads/wraps/<?php echo htmlspecialchars($w['image']); ?>" onerror="this.src='uploads/wraps/default.png'">
<h3><?php echo htmlspecialchars($w['title']); ?></h3>
<div><?php echo htmlspecialchars($w['color']); ?></div>
<strong>₹<?php echo number_format($w['price'],2); ?></strong>
<div class="actions">
<a class="<?php echo $w['status']?'on':'off'; ?>" href="?toggle=<?php echo $w['id']; ?>">
<?php echo $w['status']?'Enabled':'Disabled'; ?>
</a>
|
<a style="color:red" href="?delete=<?php echo $w['id']; ?>">Delete</a>
</div>
</div>
<?php } ?>
</div>

</div>
</body>
</html>
