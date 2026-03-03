<?php
/* ============================================================
   PRODUCT DETAIL PAGE
   Gift Galaxy
   ============================================================ */

session_start();
include 'config.php';

/* ============================================================
   SESSION & USER
   ============================================================ */
$user_id         = $_SESSION['user_id'] ?? null;
$logged_username = $_SESSION['username'] ?? 'Guest';

/* ============================================================
   PRODUCT ID CHECK
   ============================================================ */
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_GET['id'];

/* ============================================================
   FETCH PRODUCT + SELLER
   ============================================================ */
$product_sql = "
    SELECT 
        p.*,
        s.store_name
    FROM products p
    LEFT JOIN sellers s ON p.seller_id = s.id
    WHERE p.id = $product_id
      AND p.deleted = 0
    LIMIT 1
";

$product_q = mysqli_query($conn, $product_sql);

if (!$product_q || mysqli_num_rows($product_q) == 0) {
    die("Product not found");
}

$product = mysqli_fetch_assoc($product_q);

/* ============================================================
   BASIC DATA
   ============================================================ */
$product_name  = htmlspecialchars($product['product_name']);
$product_price = number_format($product['product_price'], 2);
$stock         = (int)$product['stock'];
/* ============================================================
   WISHLIST + CART ACTIONS
   ============================================================ */

$is_wishlisted = false;

if($user_id){
    $check_wish = mysqli_query($conn,"SELECT id FROM wishlist WHERE user_id=$user_id AND product_id=$product_id");
    if(mysqli_num_rows($check_wish)>0){
        $is_wishlisted = true;
    }
}

if(isset($_GET['toggle_wishlist']) && $user_id){
    if($is_wishlisted){
        mysqli_query($conn,"DELETE FROM wishlist WHERE user_id=$user_id AND product_id=$product_id");
    }else{
        mysqli_query($conn,"INSERT INTO wishlist (user_id,product_id) VALUES ($user_id,$product_id)");
    }
    header("Location: product_detail.php?id=$product_id");
    exit;
}

if(isset($_GET['add_to_cart']) && $user_id){
    $check_cart = mysqli_query($conn,"SELECT id FROM cart WHERE user_id=$user_id AND product_id=$product_id");
    if(mysqli_num_rows($check_cart)>0){
        mysqli_query($conn,"UPDATE cart SET quantity=quantity+1 WHERE user_id=$user_id AND product_id=$product_id");
    }else{
        mysqli_query($conn,"INSERT INTO cart (user_id,product_id) VALUES ($user_id,$product_id)");
    }
    header("Location: product_detail.php?id=$product_id");
    exit;
}


$store_name = isset($product['store_name']) && $product['store_name'] != ''
    ? htmlspecialchars($product['store_name'])
    : 'Gift Galaxy';

/* ============================================================
   PRODUCT IMAGES
   ============================================================ */
$images = [];

if (!empty($product['product_image'])) {
    $images[] = $product['product_image'];
}

foreach (['image','image2','image3','image4','image5'] as $img) {
    if (!empty($product[$img]) && !in_array($product[$img], $images)) {
        $images[] = $product[$img];
    }
}

if (empty($images)) {
    $images[] = 'default.png';
}

/* ============================================================
   REVIEW SUBMIT
   ============================================================ */
if (isset($_POST['submit_review']) && $user_id) {
    $comment = trim(mysqli_real_escape_string($conn, $_POST['comment']));
    if ($comment !== '') {
        mysqli_query(
            $conn,
            "INSERT INTO reviews (product_id, user_id, rating, comment)
             VALUES ($product_id, $user_id, 5, '$comment')"
        );
        header("Location: product_detail.php?id=$product_id");
        exit;
    }
}

/* ============================================================
   REVIEW DELETE
   ============================================================ */
if (isset($_GET['delete_review']) && $user_id) {
    $rid = (int)$_GET['delete_review'];
    mysqli_query(
        $conn,
        "DELETE FROM reviews
         WHERE id = $rid AND user_id = $user_id"
    );
    header("Location: product_detail.php?id=$product_id");
    exit;
}

/* ============================================================
   FETCH REVIEWS
   ============================================================ */
$reviews_q = mysqli_query(
    $conn,
    "SELECT r.*, u.username
     FROM reviews r
     LEFT JOIN users u ON r.user_id = u.id
     WHERE r.product_id = $product_id
     ORDER BY r.id DESC"
);
$review_count = mysqli_num_rows($reviews_q);

/* ============================================================
   SIMILAR PRODUCTS
   ============================================================ */
$similar_q = mysqli_query(
    $conn,
    "SELECT id, product_name, product_price, product_image
     FROM products
     WHERE id != $product_id
       AND deleted = 0
     ORDER BY id DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $product_name; ?> | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
/* ============================================================
   ROOT + RESET
   ============================================================ */
:root{
    --primary:#6A1B9A;
    --accent:#B832F0;
    --bg:#f4f4f9;
}

*{ box-sizing:border-box; }

body{
    margin:0;
    font-family:Segoe UI, Arial, sans-serif;
    background:var(--bg);
    color:#222;
}

/* ============================================================
   MAIN CONTAINER
   ============================================================ */
.container{
    max-width:1200px;
    margin:40px auto;
    background:#fff;
    border-radius:24px;
    padding:40px;
    box-shadow:0 25px 80px rgba(0,0,0,.08);
    position:relative;
}

/* ============================================================
   BACK BUTTON
   ============================================================ */
.back-home{
    position:absolute;
    top:20px;
    left:20px;
    background:var(--primary);
    color:#fff;
    padding:10px 18px;
    border-radius:14px;
    font-weight:700;
    text-decoration:none;
    transition:.3s;
}
.back-home:hover{
    transform:translateY(-2px);
}

/* ============================================================
   GRID
   ============================================================ */
.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:50px;
}
@media(max-width:900px){
    .grid{ grid-template-columns:1fr; }
}

/* ============================================================
   IMAGE GALLERY
   ============================================================ */
.gallery{
    background:#fafafa;
    border-radius:22px;
    padding:25px;
}

.main-img{
    height:420px;
    display:flex;
    align-items:center;
    justify-content:center;
}
.main-img img{
    max-width:100%;
    max-height:100%;
    border-radius:18px;
}

/* ============================================================
   PRODUCT DETAILS
   ============================================================ */
.store-name{
    font-size:14px;
    color:#777;
    margin-bottom:4px;
}

h1{
    font-size:34px;
    margin:8px 0;
}

.price{
    font-size:30px;
    font-weight:700;
    color:var(--accent);
}

.stock{
    font-size:14px;
    color:#555;
}

/* ============================================================
   BUTTONS
   ============================================================ */
.btn-group{
    display:flex;
    gap:16px;
    margin-top:28px;
}

.btn{
    flex:1;
    padding:18px;
    border-radius:18px;
    text-align:center;
    font-weight:700;
    text-decoration:none;
    transition:.35s;
}

.gift-btn{
    background:linear-gradient(135deg,var(--primary),var(--accent));
    color:#fff;
}
.self-btn{
    background:#f3e7ff;
    border:2px solid var(--primary);
    color:var(--primary);
}

.btn:hover{
    transform:translateY(-3px) scale(1.02);
    box-shadow:0 12px 30px rgba(0,0,0,.15);
}

/* ============================================================
   DESCRIPTION + REVIEWS
   ============================================================ */
.desc-review{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:25px;
    margin-top:35px;
}

.desc-box{
    background:#fafafa;
    padding:20px;
    border-radius:16px;
}

.review-box{
    max-height:300px;
    overflow-y:auto;
    background:#fafafa;
    padding:15px;
    border-radius:16px;
}

.review{
    border-bottom:1px solid #eee;
    padding:10px 0;
}
.review:last-child{ border-bottom:none; }

.review-form textarea{
    width:100%;
    padding:12px;
    border-radius:12px;
    border:1px solid #ddd;
    margin-top:10px;
}
.review-form button{
    width:100%;
    margin-top:10px;
    padding:12px;
    background:var(--primary);
    color:#fff;
    border:none;
    border-radius:12px;
    font-weight:700;
}

/* ============================================================
   SIMILAR PRODUCTS
   ============================================================ */
.similar{
    max-width:1200px;
    margin:60px auto;
}

.similar-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:24px;
}

.similar-card{
    background:#fff;
    border-radius:18px;
    padding:16px;
    text-align:center;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
    transition:.35s;
    cursor:pointer;
}
.similar-card:hover{
    transform:translateY(-6px);
    box-shadow:0 22px 55px rgba(0,0,0,.18);
}

.similar-card img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:14px;
}

/* ============================================================
   FOOTER (OLD STYLE – EXPANDED)
   ============================================================ */
footer{
    background:#4A1775;
    color:#fff;
    padding:40px 20px;
}

.footer-grid{
    max-width:1200px;
    margin:auto;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:25px;
}

.footer-grid h4{
    margin-bottom:10px;
}

.footer-grid a{
    display:block;
    color:#ddd;
    font-size:14px;
    text-decoration:none;
    margin:6px 0;
}

.footer-bottom{
    text-align:center;
    margin-top:25px;
    font-size:13px;
    opacity:.9;
}
</style>
</head>

<body>

<div class="container">

<a href="index.php" class="back-home">⬅ Home</a>

<div class="grid">
<div class="gallery">

    <!-- MAIN IMAGE -->
    <div class="main-img">
        <img id="mainImage" src="uploads/products/<?php echo $images[0]; ?>">
    </div>

    <!-- THUMBNAILS -->
    <?php if(count($images) > 1): ?>
    <div style="display:flex;gap:12px;margin-top:15px;flex-wrap:wrap;">
        <?php foreach($images as $img): ?>
            <img 
                src="uploads/products/<?php echo htmlspecialchars($img); ?>" 
                style="width:78px;height:78px;object-fit:cover;border-radius:10px;cursor:pointer;border:2px solid #ff68a7;"
                onclick="document.getElementById('mainImage').src=this.src;"
            >
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>


<div>
    <div class="store-name"><?php echo $store_name; ?></div>
    <h1><?php echo $product_name; ?></h1>

    <div style="display:flex;align-items:center;justify-content:space-between;">

    <div class="price">
        ₹<?php echo $product_price; ?>
        <span class="stock">(<?php echo $stock; ?> in stock)</span>
    </div>

    <?php if($user_id){ ?>
    <div style="display:flex;gap:18px;font-size:26px;align-items:center;">

        <!-- WISHLIST -->
        <a href="?id=<?php echo $product_id; ?>&toggle_wishlist=1" 
           style="text-decoration:none;transition:.3s;">
            <?php if($is_wishlisted){ ?>
                <span style="color:#e91e63;">❤️</span>
            <?php }else{ ?>
                <span style="color:#bbb;">🤍</span>
            <?php } ?>
        </a>

        <!-- CART -->
        <a href="?id=<?php echo $product_id; ?>&add_to_cart=1" 
           style="text-decoration:none;">
            <span style="color:#6A1B9A;">🛒</span>
        </a>

    </div>
    <?php } ?>

</div>


    <div class="btn-group">
        <a href="gift_now.php?product_id=<?php echo $product_id; ?>" class="btn gift-btn">🎁 Gift Now</a>
        <a href="gift_yourself.php?product_id=<?php echo $product_id; ?>" class="btn self-btn">👤 Gift Yourself</a>
    </div>

    <div class="desc-review">

        <div>
            <div class="desc-box">
                <?php echo nl2br(htmlspecialchars($product['product_description'])); ?>
            </div>
        </div>

        <div>
            <h3>Customer Reviews (<?php echo $review_count; ?>)</h3>

            <div class="review-box">
                <?php while($r=mysqli_fetch_assoc($reviews_q)){ ?>
                    <div class="review">
                        <strong><?php echo htmlspecialchars($r['username'] ?? 'User'); ?></strong>
                        <p><?php echo htmlspecialchars($r['comment']); ?></p>
                        <?php if($r['user_id']==$user_id){ ?>
                            <a href="?id=<?php echo $product_id; ?>&delete_review=<?php echo $r['id']; ?>" style="color:red;font-size:12px;">Delete</a>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <?php if($user_id){ ?>
            <form method="post" class="review-form">
                <textarea name="comment" placeholder="Write your review..." required></textarea>
                <button name="submit_review">Submit</button>
            </form>
            <?php } ?>
        </div>

    </div>
</div>
</div>
</div>

<div class="similar">
<h2>Similar Gifts You’ll Love 💜</h2>

<div class="similar-grid">
<?php while($s=mysqli_fetch_assoc($similar_q)){ ?>
    <a href="product_detail.php?id=<?php echo $s['id']; ?>" style="text-decoration:none;color:inherit;">
        <div class="similar-card">
            <img src="uploads/products/<?php echo $s['product_image']; ?>">
            <h4><?php echo htmlspecialchars($s['product_name']); ?></h4>
            <div class="price">₹<?php echo number_format($s['product_price'],2); ?></div>
        </div>
    </a>
<?php } ?>
</div>
</div>

<footer>
<div class="footer-grid">
    <div>
        <h4>Gift Galaxy</h4>
        <a href="about_us.php">About Us</a>
        <a href="#">Careers</a>
        <a href="#">Our Story</a>
    </div>
    <div>
        <h4>Support</h4>
        <a href="#">Help Center</a>
        <a href="#">Returns</a>
        <a href="#">Shipping</a>
    </div>
    <div>
        <h4>Legal</h4>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms & Conditions</a>
    </div>
    <div>
        <h4>Follow Us</h4>
        <a href="#">Instagram</a>
        <a href="#">Facebook</a>
    </div>
</div>

<div class="footer-bottom">
© <?php echo date('Y'); ?> Gift Galaxy — Made with 💜 in India
</div>
</footer>

</body>
</html>
