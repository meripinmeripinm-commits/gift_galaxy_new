<?php
include 'config.php';

/* HANDLE SEARCH */
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

/* HANDLE SORTING */
$sort = "id DESC";
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === "price_asc") $sort = "price ASC";
    if ($_GET['sort'] === "price_desc") $sort = "price DESC";
}

/* FETCH PRODUCTS */
$sql = "SELECT * FROM products 
        WHERE product_name LIKE '%$search%' 
        OR category LIKE '%$search%' 
        ORDER BY $sort";
$products = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Products - Gift Galaxy</title>

<style>
body { margin:0; font-family:'Segoe UI',sans-serif; background:#f4f4f9; }
header{ background:#50207A; color:#fff; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; }
header h1{ margin:0; }
nav a{ color:#D6B9FC; text-decoration:none; margin-left:20px; font-weight:bold; }
nav a:hover{ color:#fff; }
.container{ max-width:1200px; margin:30px auto; display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:30px; }
.card{ background:#fff; padding:20px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); transition:0.3s; text-align:center; }
.card:hover{ transform:translateY(-5px); box-shadow:0 10px 25px rgba(0,0,0,0.15); }
.card img{ max-width:100%; height:220px; object-fit:cover; border-radius:10px; margin-bottom:15px; }
.card h3{ margin:10px 0; color:#50207A; }
.card p{ color:#6B6B6B; font-size:0.9rem; margin-bottom:10px; }
.card .price{ font-weight:bold; margin-bottom:15px; color:#D646FF; font-size:1.1rem; }
.card button, .card a.action-btn{
    text-decoration:none;
    background:#50207A;
    color:#fff;
    padding:10px 20px;
    border-radius:5px;
    transition:0.3s;
    margin:5px;
    display:inline-block;
    cursor:pointer;
    font-weight:bold;
}
.card button:hover, .card a.action-btn:hover{ background:#6F5BB5; }

.share-buttons a{
    text-decoration:none;
    display:inline-block;
    margin:5px;
    font-size:13px;
    padding:6px 10px;
    border-radius:5px;
    background:#D646FF;
    color:#fff;
}
.share-buttons a:hover{ background:#BF00FF; }

.search-sort{
    max-width:1200px;
    margin:30px auto;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
}
.search-sort input[type=text],
.search-sort select{
    padding:8px 12px;
    border-radius:5px;
    border:1px solid #ccc;
}

footer{ background:#50207A; color:#fff; text-align:center; padding:20px 0; margin-top:50px; }

@media(max-width:768px){
    .container{ grid-template-columns:1fr; }
    .search-sort{ flex-direction:column; align-items:flex-start; }
}
</style>
</head>

<body>

<header>
    <h1>Gift Galaxy</h1>
    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
    </nav>
</header>

<div class="search-sort">
    <form method="GET">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <form method="GET">
        <select name="sort" onchange="this.form.submit()">
            <option value="">Sort By</option>
            <option value="price_asc" <?php if(isset($_GET['sort']) && $_GET['sort']=="price_asc") echo "selected"; ?>>Price Low → High</option>
            <option value="price_desc" <?php if(isset($_GET['sort']) && $_GET['sort']=="price_desc") echo "selected"; ?>>Price High → Low</option>
        </select>
    </form>
</div>

<div class="container">
<?php
while ($row = mysqli_fetch_assoc($products)) {

    /* SAFE DATA */
    $id     = $row['id'] ?? 0;
    $name   = $row['product_name'] ?? 'Unnamed Product';
    $category = $row['category'] ?? 'General';
    $price  = $row['price'] ?? '0';
    $image  = (!empty($row['image'])) ? $row['image'] : 'default.png';

    $productLink = "product_detail.php?id=".$id;
    $shareURL  = urlencode("https://giftgalaxyco.com/".$productLink);
    $shareText = urlencode("Check out this amazing gift: ".$name);
?>
    <div class="card">
        <img src="uploads/products/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>">

        <h3><?php echo htmlspecialchars($name); ?></h3>
        <p><?php echo htmlspecialchars($category); ?></p>
        <div class="price">₹<?php echo htmlspecialchars($price); ?></div>

        <a href="<?php echo $productLink; ?>" class="action-btn">Send as Gift</a>

        <form method="POST" action="cart.php">
            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
            <button type="submit" name="add_to_cart">Add to Cart</button>
        </form>

        <div class="share-buttons">
            <a href="https://www.facebook.com/sharer.php?u=<?php echo $shareURL; ?>" target="_blank">Facebook</a>
            <a href="https://twitter.com/intent/tweet?url=<?php echo $shareURL; ?>&text=<?php echo $shareText; ?>" target="_blank">Twitter</a>
            <a href="https://wa.me/?text=<?php echo $shareText.' '.$shareURL; ?>" target="_blank">WhatsApp</a>
        </div>
    </div>
<?php } ?>
</div>

<footer>
    &copy; 2026 Gift Galaxy. All rights reserved.
</footer>

</body>
</html>
