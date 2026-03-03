<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ============================================================
   REMOVE FROM WISHLIST
   ============================================================ */
if(isset($_GET['remove'])){
    $remove_id = (int)$_GET['remove'];
    mysqli_query($conn,"DELETE FROM wishlist WHERE user_id=$user_id AND product_id=$remove_id");
    header("Location: favorites.php");
    exit;
}


/* ============================================================
   FETCH WISHLIST PRODUCTS
   ============================================================ */
$sql = "
    SELECT p.id, p.product_name, p.product_price, p.product_image
    FROM wishlist w
    LEFT JOIN products p ON w.product_id = p.id
    WHERE w.user_id = $user_id
    AND p.deleted = 0
    ORDER BY w.id DESC
";

$result = mysqli_query($conn, $sql);

/* ============================================================
   COUNTS
   ============================================================ */
$wishlist_count = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM wishlist w
    LEFT JOIN products p ON w.product_id = p.id
    WHERE w.user_id = $user_id
    AND p.deleted = 0
"))['total'];

$cart_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM cart WHERE user_id=$user_id"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Wishlist | Gift Galaxy</title>

<style>
/* ===== GLOBAL ===== */
body{
    font-family: 'Segoe UI', Arial, sans-serif;
    background:#f4f4f9;
    margin:0;
}
a{text-decoration:none;color:inherit;}

/* ===== HEADER ===== */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 40px;
    background:#fff;
    box-shadow:0 5px 20px rgba(0,0,0,.05);
}
.header a{
    font-weight:600;
    color:#6A1B9A;
    font-size:15px;
    transition:.3s;
}
.header a:hover{
    opacity:.8;
}
.heart{
    font-size:18px;
    color:#ff4081;
    animation:pulse 1.2s infinite;
}
@keyframes pulse{
    0%{transform:scale(1);}
    50%{transform:scale(1.2);}
    100%{transform:scale(1);}
}

/* ===== TITLE ===== */
.title{
    text-align:center;
    margin:30px 0 20px;
    font-size:22px;
    color:#50207A;
}

/* ===== CONTAINER GRID ===== */
.container{
    max-width:1000px;
    margin:0 auto 60px;
}
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
    gap:20px;
}

/* ===== CARD ===== */
.card{
    background:#fff;
    border-radius:14px;
    padding:15px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    position:relative;
    transition:.3s;
}
.card:hover{
    transform:translateY(-5px);
    box-shadow:0 15px 40px rgba(0,0,0,.12);
}

/* IMAGE */
.card img{
    width:150px;
    height:150px;
    object-fit:cover;
    border-radius:12px;
    margin:0 auto 10px;
    transition:.3s;
}
.card:hover img{
    transform:scale(1.05);
}

/* NAME & PRICE */
.card h4{
    font-size:15px;
    margin:5px 0;
    min-height:40px;
    color:#333;
}
.price{
    font-size:14px;
    font-weight:700;
    color:#6A1B9A;
    margin-bottom:10px;
}

/* REMOVE BUTTON */
.remove-btn{
    position:absolute;
    top:8px;
    right:10px;
    background:none;
    border:none;
    font-size:18px;
    cursor:pointer;
    color:#ff3c3c;
    transition:.3s;
}
.remove-btn:hover{
    transform:scale(1.2);
}

/* BUTTON GROUP */
.button-group{
    display:flex;
    gap:10px;
    margin-top:10px;
}
.gift-btn{
    flex:1;
    background:#6A1B9A;
    color:#fff;
    border:none;
    padding:8px 12px;
    border-radius:8px;
    font-size:13px;
    cursor:pointer;
    transition:.2s;
}
.gift-btn:hover{
    background:#50207A;
    transform:translateY(-2px);
}

/* FLOATING CART */
.floating-cart{
    position:fixed;
    bottom:25px;
    right:25px;
    background:#6A1B9A;
    color:#fff;
    width:60px;
    height:60px;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:24px;
    box-shadow:0 15px 35px rgba(0,0,0,.3);
    text-decoration:none;
}
.cart-badge{
    position:absolute;
    top:-5px;
    right:-5px;
    background:#ff4081;
    color:#fff;
    font-size:11px;
    padding:4px 7px;
    border-radius:50px;
}

/* EMPTY WISHLIST NOTE */
.empty-note{
    text-align:center;
    margin-top:50px;
    font-size:16px;
    color:#50207A;
}
.empty-note span{
    color:#ff4081;
}
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <a href="index.php">⬅ Home</a>
    <?php if($wishlist_count>0){ ?>
        <div class="heart">❤️ (<?php echo $wishlist_count; ?>)</div>
    <?php } ?>
</div>

<h2 class="title">My Wishlist</h2>

<div class="container">
<div class="grid">

<?php if(mysqli_num_rows($result)>0){ ?>
    <?php while($row=mysqli_fetch_assoc($result)){ ?>
        <div class="card">
            <form method="GET">
                <input type="hidden" name="remove" value="<?php echo $row['id']; ?>">
                <button class="remove-btn">❌</button>
            </form>

            <a href="product_detail.php?id=<?php echo $row['id']; ?>" style="text-decoration:none;color:inherit;">
                <img src="uploads/products/<?php echo htmlspecialchars($row['product_image']); ?>" alt="Product Image">
                <h4><?php echo htmlspecialchars(substr($row['product_name'],0,30)); ?></h4>
                <div class="price">₹<?php echo number_format($row['product_price'],2); ?></div>
            </a>

            <div class="button-group">
                <a href="gift_now.php?product_id=<?php echo $row['id']; ?>">
                    <button class="gift-btn">🎁 Gift Now</button>
                </a>
                <a href="gift_yourself.php?product_id=<?php echo $row['id']; ?>">
                    <button class="gift-btn">💝 Gift Yourself</button>
                </a>
            </div>
        </div>
    <?php } ?>
<?php } else { ?>
    <div class="empty-note">
        Your wishlist is empty 💜<br>
        Browse products and <span>add your favorites here</span> to gift later!
    </div>
<?php } ?>

</div>
</div>

<!-- FLOATING CART -->
<a href="cart.php" class="floating-cart">
🛒
<?php if($cart_count>0){ ?>
    <span class="cart-badge"><?php echo $cart_count; ?></span>
<?php } ?>
</a>

</body>
</html>
