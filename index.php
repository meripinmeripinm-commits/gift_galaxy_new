<?php
session_start();
include 'config.php';

/* ================================
   REDIRECT ADMIN TO DASHBOARD
================================ */
if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'){
    header("Location: admin_dashboard.php");
    exit;
}

/* ================================
   HANDLE SEARCH, CATEGORY, SORT
================================ */
$search   = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";
$category = isset($_GET['category']) ? intval($_GET['category']) : 0;
$sort     = "id DESC";

if (isset($_GET['sort'])) {
    if ($_GET['sort'] === "price_asc") $sort = "product_price ASC";
    if ($_GET['sort'] === "price_desc") $sort = "product_price DESC";
}

/* ================================
   FETCH PRODUCTS (ONLY NOT DELETED)
================================ */
$catQuery = $category ? " AND category_id=$category" : "";
$productQuery = "
    SELECT * FROM products
    WHERE deleted=0 AND product_name LIKE '%$search%' $catQuery
    ORDER BY $sort
";
$products = mysqli_query($conn, $productQuery);

/* ================================
   FETCH CATEGORIES
================================ */
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$category_list = mysqli_fetch_all($categories, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{box-sizing:border-box;}
body, html{margin:0;padding:0;font-family:'Segoe UI',sans-serif;background:#f5f6fb;}
.page-wrapper{min-height:100vh;display:flex;flex-direction:column;}

/* HEADER */
header { background:#50207A; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; }
.logo-area{display:flex;align-items:center;gap:14px;cursor:pointer;}
.logo-area img{height:70px;}
.logo-area span{color:#fff;font-size:34px;font-weight:700;}
.tagline{color:#E5CCFF;font-size:16px;margin-left:15px;}
nav{display:flex;gap:18px;align-items:center;position:relative;}
nav a{color:#fff;font-size:22px;text-decoration:none;transition:.2s;}
nav a:hover{color:#FFD6FF;}

/* ACCOUNT POPUP */
.account-popup{
    display:none;position:absolute;top:65px;right:0;
    background:#fff;border-radius:14px;
    box-shadow:0 10px 30px rgba(0,0,0,.25);
    width:220px;overflow:hidden;z-index:1000;
}
.account-popup a{display:block;padding:12px 18px;text-decoration:none;color:#50207A;border-bottom:1px solid #eee;}
.account-popup a:hover{background:#50207A;color:#fff;}
.account-popup.show{display:block;}

/* CONTROLS */
.top-controls{max-width:1200px;margin:25px auto;display:flex;gap:15px;padding:0 20px;flex-wrap:wrap;}
.top-controls input, select, button{padding:10px;border-radius:10px;border:1px solid #ccc;}
.search-box button{background:#50207A;color:#fff;border:none;cursor:pointer;}
#suggestions li:hover{background:#f0f0f0;cursor:pointer;}

/* CATEGORY STRIP */
.category-strip{max-width:1200px;margin:10px auto 30px;padding:0 20px;display:flex;gap:20px;overflow-x:auto;}
.category-strip::-webkit-scrollbar{display:none;}
.cat-card{text-align:center;text-decoration:none;color:#333;min-width:95px;}
.cat-card img{width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid #50207A;padding:3px;background:#fff;}
.cat-card span{display:block;font-size:13px;margin-top:6px;}

/* PRODUCTS GRID */
.container{max-width:1200px;margin:auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:25px;padding:0 20px 30px;}
.card{background:#fff;border-radius:20px;padding:15px;box-shadow:0 12px 30px rgba(0,0,0,.12);cursor:pointer;position:relative;text-align:center;}
.card img{width:100%;height:220px;object-fit:cover;border-radius:16px;}
.card h3{margin:12px 0 6px;font-size:18px;}
.price{color:#D646FF;font-size:18px;font-weight:bold;margin:4px 0;}
.stock{font-size:14px;margin:4px 0;}
.stock.low{color:red;}

/* ACTIONS */
.actions{display:flex;justify-content:center;gap:8px;margin-top:10px;flex-wrap:wrap;}
.gift-btn{background:#50207A;color:#fff;padding:6px 14px;border-radius:8px;text-decoration:none;font-size:13px;display:flex;align-items:center;gap:6px;cursor:pointer;text-align:center;}
.icon-btn{width:36px;height:36px;border-radius:50%;border:none;background:#eee;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:inherit;}
.icon-btn:hover{background:#50207A;color:#fff;}

/* FAVORITE POPUP */
.fav-popup{
    position:fixed;
    bottom:20px;
    left:50%;
    transform:translateX(-50%) translateY(100px);
    background:#50207A;
    color:#fff;
    padding:12px 22px;
    border-radius:12px;
    font-size:14px;
    opacity:0;
    pointer-events:none;
    transition:0.5s;
    z-index:9999;
}
.fav-popup.show{
    transform:translateX(-50%) translateY(0);
    opacity:1;
    pointer-events:auto;
}

/* FOOTER */
footer{background:#50207A;color:#fff;text-align:center;padding:25px;margin-top:auto;}
.footer-icons{display:flex;justify-content:center;gap:22px;margin-top:12px;}
.footer-icons a{color:#fff;font-size:22px;}
.footer-icons a:hover{color:#FFD6FF;transform:scale(1.3);}
</style>
</head>
<body>
<div class="page-wrapper">

<!-- HEADER -->
<header>
    <div class="logo-area" onclick="location.href='index.php'">
        <img src="assets/images/logo.png">
        <span>Gift Galaxy</span>
        <div class="tagline">Find the perfect surprise 🎁</div>
    </div>
    <nav>
        <a href="favorites.php"><i class="fa-solid fa-heart"></i></a>
        <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="chat.php"><i class="fa-solid fa-comment-dots"></i></a>
        <a href="#" id="account-icon"><i class="fa-solid fa-user"></i></a>

        <div class="account-popup" id="account-popup">
        <?php if(isset($_SESSION['user_id'])){ ?>
            <a href="profile.php">Profile</a>
            <a href="orders.php">Orders</a>
            <a href="manage_account.php">Manage</a>
            <a href="logout.php">Logout</a>
        <?php } else { ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php } ?>
        </div>
    </nav>
</header>

<!-- CONTROLS -->
<div class="top-controls">
    <form class="search-box" method="GET" style="position:relative;">
        <input id="searchInput" name="search" placeholder="Search gifts..." autocomplete="off" value="<?=htmlspecialchars($search)?>">
        <button>Search</button>
        <ul id="suggestions" style="position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ccc; border-top:none; border-radius:0 0 8px 8px; max-height:200px; overflow-y:auto; list-style:none; margin:0; padding:0; display:none; z-index:1000;"></ul>
    </form>

    <form method="GET">
        <input type="hidden" name="search" value="<?=htmlspecialchars($search)?>">
        <select name="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach($category_list as $c){ ?>
                <option value="<?=$c['id']?>" <?=($category==$c['id'])?'selected':''?>><?=$c['name']?></option>
            <?php } ?>
        </select>
    </form>

    <form method="GET">
        <input type="hidden" name="search" value="<?=htmlspecialchars($search)?>">
        <input type="hidden" name="category" value="<?=htmlspecialchars($category)?>">
        <select name="sort" onchange="this.form.submit()">
            <option value="">Sort</option>
            <option value="price_asc" <?=isset($_GET['sort']) && $_GET['sort']=='price_asc'?'selected':''?>>Low → High</option>
            <option value="price_desc" <?=isset($_GET['sort']) && $_GET['sort']=='price_desc'?'selected':''?>>High → Low</option>
        </select>
    </form>
</div>

<!-- CATEGORY STRIP -->
<div class="category-strip">
    <?php foreach($category_list as $cat){ ?>
        <a href="?category=<?=$cat['id']?>" class="cat-card">
            <img loading="lazy" src="uploads/categories/<?=$cat['image'] ?: 'default.png'?>">
            <span><?=$cat['name']?></span>
        </a>
    <?php } ?>
</div>

<!-- PRODUCTS GRID -->
<div class="container">
    <?php while($p=mysqli_fetch_assoc($products)): 
        $img = 'uploads/products/'.($p['product_image'] ?: 'default.png');
    ?>
    <div class="card" onclick="location.href='product_detail.php?id=<?=$p['id']?>'">
        <img loading="lazy" src="<?=$img?>" onerror="this.src='uploads/products/default.png'">
        <h3><?=htmlspecialchars($p['product_name'])?></h3>
        <div class="price">₹<?=number_format($p['product_price'],2)?></div>
        <div class="stock <?=($p['stock'] < 10)?'low':''?>">
            <?=$p['stock']>0?$p['stock'].' in stock':'Out of Stock'?>
        </div>
        <div class="actions">
            <a class="gift-btn" href="gift_now.php?product_id=<?=$p['id']?>">🎁 Gift Now</a>
            <button class="icon-btn" onclick="event.stopPropagation(); addToFavorites(<?=$p['id']?>)">❤️</button>
            <button class="icon-btn" onclick="shareProduct('<?=$p['id']?>');event.stopPropagation();">📤</button>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- FAVORITE POPUP -->
<div class="fav-popup" id="fav-popup">Added to favorites!</div>

<!-- FOOTER -->
<footer>
    © <?=date('Y')?> Gift Galaxy
    <div class="footer-icons">
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
    </div>
</footer>
</div>

<script>
const icon = document.getElementById('account-icon');
const popup = document.getElementById('account-popup');
icon.onclick = e => { e.stopPropagation(); popup.classList.toggle('show') }
document.onclick = () => popup.classList.remove('show');

function shareProduct(id){
    const url = window.location.origin + "/product_detail.php?id=" + id;
    if(navigator.share){ 
        navigator.share({ title: "Gift Galaxy Product", url: url }).catch(console.error); 
    } else { 
        prompt("Copy this link:", url); 
    }
}

// FAVORITES AJAX
function addToFavorites(product_id){
    const popup = document.getElementById('fav-popup');
    fetch('ajax_add_fav.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'product_id='+product_id
    })
    .then(res => res.text())
    .then(data => {
        popup.textContent = data;
        popup.classList.add('show');
        setTimeout(()=> popup.classList.remove('show'), 2000);
    })
    .catch(err => {
        popup.textContent = 'Failed to add to favorites.';
        popup.classList.add('show');
        setTimeout(()=> popup.classList.remove('show'), 2000);
    });
}

// LIVE SEARCH
const input = document.getElementById('searchInput');
const sugBox = document.getElementById('suggestions');
input.addEventListener('input', function() {
    const val = this.value.trim();
    if(!val){ sugBox.style.display='none'; return; }
    fetch('search_suggestions.php?term=' + encodeURIComponent(val))
    .then(res => res.json())
    .then(data => {
        sugBox.innerHTML = '';
        if(data.length){
            data.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                li.style.padding = '8px 12px';
                li.addEventListener('click', () => {
                    input.value = item;
                    sugBox.style.display = 'none';
                    input.form.submit();
                });
                sugBox.appendChild(li);
            });
            sugBox.style.display = 'block';
        } else { sugBox.style.display='none'; }
    });
});
document.addEventListener('click', e => { if(!input.contains(e.target)) sugBox.style.display='none'; });
</script>
</body>
</html>
