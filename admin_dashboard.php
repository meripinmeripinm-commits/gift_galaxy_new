<?php
session_start();
include 'config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php?redirect=admin_dashboard.php");
    exit;
}

// ================== FETCH STATS ==================
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users"))['count'];
$totalSellers = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='seller'"))['count'];
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM products"))['count'];
$totalOrders = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as count FROM orders_new")
)['count'];

// Pending sellers
$pendingSellers = mysqli_query($conn,"SELECT * FROM users WHERE role='seller' AND status='pending' ORDER BY created_at DESC LIMIT 5");

// Recent users
$recentUsers = mysqli_query($conn,"SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// ================== CATEGORIES ==================

// Add new category
$category_msg = '';
if(isset($_POST['add_category'])){
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $image_name = null;

    if(isset($_FILES['category_image']) && $_FILES['category_image']['name'] != ''){
        $ext = strtolower(pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION));
        $image_name = 'cat_'.time().'.'.$ext;

        // Ensure folder exists
        if(!is_dir('uploads/categories')) mkdir('uploads/categories', 0755, true);

        move_uploaded_file($_FILES['category_image']['tmp_name'], 'uploads/categories/'.$image_name);
    }

    if(!empty($category_name)){
        mysqli_query($conn,"INSERT INTO categories (name, image) VALUES ('$category_name', '$image_name')");
        $category_msg = "Category added successfully!";
    }
}

// Update category
if(isset($_POST['update_category'])){
    $id = (int)$_POST['cat_id'];
    $name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $image_name = $_POST['old_image'];

    if(isset($_FILES['category_image']) && $_FILES['category_image']['name'] != ''){
        $ext = strtolower(pathinfo($_FILES['category_image']['name'], PATHINFO_EXTENSION));
        $image_name = 'cat_'.time().'.'.$ext;

        if(!is_dir('uploads/categories')) mkdir('uploads/categories', 0755, true);

        move_uploaded_file($_FILES['category_image']['tmp_name'], 'uploads/categories/'.$image_name);
    }

    mysqli_query($conn,"UPDATE categories SET name='$name', image='$image_name' WHERE id=$id");
    $category_msg = "Category updated successfully!";
}

// Fetch categories
$categories = mysqli_query($conn,"SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ===== GENERAL ===== */
body { margin:0; font-family:'Segoe UI',sans-serif; background:#f5f6fb; }
.page-wrapper { display:flex; min-height:100vh; }
.sidebar { width:250px; background:#50207A; color:#fff; display:flex; flex-direction:column; padding-top:20px; }
.sidebar a { color:#fff; text-decoration:none; padding:15px 20px; display:flex; align-items:center; gap:10px; transition:.2s; }
.sidebar a:hover, .sidebar a.active { background:#D646FF; }
.main { flex:1; padding:20px; }
.header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.header h1 { margin:0; color:#50207A; }
.header a { text-decoration:none; color:#50207A; font-weight:600; }
.cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; margin-bottom:25px; }
.card { background:#fff; padding:20px; border-radius:14px; box-shadow:0 10px 25px rgba(0,0,0,.1); text-align:center; }
.card h3 { margin:10px 0; font-size:20px; color:#50207A; }
.card p { font-size:16px; color:#D646FF; margin:0; }
table { width:100%; border-collapse:collapse; margin-bottom:30px; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.05); }
th, td { padding:12px 15px; text-align:left; }
th { background:#50207A; color:#fff; }
tr:nth-child(even){ background:#f5f6fb; }
.action-btn { padding:6px 10px; border:none; border-radius:8px; cursor:pointer; color:#fff; }
.approve { background:#28a745; }
.reject { background:#dc3545; }
.edit { background:#007bff; }
.chart-container { background:#fff; padding:20px; border-radius:14px; box-shadow:0 10px 25px rgba(0,0,0,.05); margin-bottom:25px; }
@media(max-width:768px){ .cards { grid-template-columns:1fr 1fr; } }
@media(max-width:480px){ .cards { grid-template-columns:1fr; } }
img.cat-img { width:50px; height:50px; object-fit:cover; border-radius:6px; }
</style>
</head>
<body>

<div class="page-wrapper">
    <div class="sidebar">
        <a href="admin_dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="#"><i class="fa-solid fa-users"></i> Users</a>
        <a href="#"><i class="fa-solid fa-user-tie"></i> Sellers</a>
        <a href="#"><i class="fa-solid fa-box"></i> Products</a>
        <a href="#"><i class="fa-solid fa-tags"></i> Categories</a>
        <a href="#"><i class="fa-solid fa-cart-shopping"></i> Orders</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <a href="logout.php">Logout</a>
        </div>

        <!-- CARDS -->
        <div class="cards">
            <div class="card"><h3>Total Users</h3><p><?= $totalUsers ?></p></div>
            <div class="card"><h3>Total Sellers</h3><p><?= $totalSellers ?></p></div>
            <div class="card"><h3>Total Products</h3><p><?= $totalProducts ?></p></div>
            <div class="card"><h3>Total Orders</h3><p><?= $totalOrders ?></p></div>
        </div>

        <!-- CHART -->
        <div class="chart-container"><canvas id="usersChart"></canvas></div>

        <!-- PENDING SELLERS -->
        <h2>Pending Sellers</h2>
        <table>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Registered At</th><th>Action</th></tr>
            <?php while($seller=mysqli_fetch_assoc($pendingSellers)){ ?>
            <tr id="sellerRow<?= $seller['id'] ?>">
                <td><?= $seller['id'] ?></td>
                <td><?= htmlspecialchars($seller['username']) ?></td>
                <td><?= htmlspecialchars($seller['email']) ?></td>
                <td><?= $seller['created_at'] ?></td>
                <td>
                    <button class="action-btn approve" onclick="updateSeller(<?= $seller['id'] ?>,'approved')">Approve</button>
                    <button class="action-btn reject" onclick="updateSeller(<?= $seller['id'] ?>,'rejected')">Reject</button>
                </td>
            </tr>
            <?php } ?>
        </table>

        <!-- RECENT USERS -->
        <h2>Recent Users</h2>
        <table>
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Registered At</th></tr>
            <?php while($user=mysqli_fetch_assoc($recentUsers)){ ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['created_at'] ?></td>
            </tr>
            <?php } ?>
        </table>

        <!-- CATEGORIES MANAGEMENT -->
        <h2>Categories</h2>
        <?php if($category_msg){ ?>
            <p style="color:green;"><?= $category_msg ?></p>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data" style="margin-bottom:15px;">
            <input type="text" name="category_name" placeholder="Category Name" required style="padding:8px 10px; border-radius:8px; border:1px solid #ccc;">
            <input type="file" name="category_image" style="padding:5px; margin-left:10px;">
            <button type="submit" name="add_category" style="padding:8px 12px; border:none; border-radius:8px; background:#50207A; color:#fff; cursor:pointer;">Add Category</button>
        </form>

        <table>
            <tr><th>ID</th><th>Name</th><th>Image</th><th>Action</th></tr>
            <?php while($cat=mysqli_fetch_assoc($categories)){ ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= htmlspecialchars($cat['name']) ?></td>
                    <td>
                        <?php if($cat['image']){ ?>
                            <img src="uploads/categories/<?= $cat['image'] ?>" class="cat-img">
                        <?php } else { echo "-"; } ?>
                    </td>
                    <td>
                        <button class="action-btn edit" onclick="editCategory(<?= $cat['id'] ?>,'<?= htmlspecialchars($cat['name'],ENT_QUOTES) ?>','<?= $cat['image'] ?>')">Edit</button>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <!-- EDIT CATEGORY FORM -->
        <form method="POST" enctype="multipart/form-data" id="editForm" style="display:none; margin-top:15px;">
            <input type="hidden" name="cat_id" id="edit_id">
            <input type="hidden" name="old_image" id="old_image">
            <input type="text" name="category_name" id="edit_name" placeholder="Category Name" required style="padding:8px 10px; border-radius:8px; border:1px solid #ccc;">
            <input type="file" name="category_image" style="padding:5px; margin-left:10px;">
            <button type="submit" name="update_category" style="padding:8px 12px; border:none; border-radius:8px; background:#007bff; color:#fff; cursor:pointer;">Update Category</button>
            <button type="button" onclick="document.getElementById('editForm').style.display='none'" style="padding:8px 12px; border:none; border-radius:8px; background:#dc3545; color:#fff; cursor:pointer;">Cancel</button>
        </form>

    </div>
</div>

<script>
function updateSeller(id,status){
    if(!confirm('Are you sure?')) return;
    fetch('update_seller.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id+'&status='+status
    }).then(res=>res.text()).then(data=>{
        if(data==='success'){
            document.getElementById('sellerRow'+id).remove();
        }else{
            alert('Error: '+data);
        }
    });
}

const ctx = document.getElementById('usersChart').getContext('2d');
const usersChart = new Chart(ctx, {
    type:'bar',
    data:{
        labels:['Users','Sellers','Products','Orders'],
        datasets:[{
            label:'Count',
            data:[<?= $totalUsers ?>,<?= $totalSellers ?>,<?= $totalProducts ?>,<?= $totalOrders ?>],
            backgroundColor:['#50207A','#D646FF','#6C63FF','#28a745']
        }]
    },
    options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});

function editCategory(id,name,image){
    document.getElementById('editForm').style.display='block';
    document.getElementById('edit_id').value=id;
    document.getElementById('edit_name').value=name;
    document.getElementById('old_image').value=image;
    window.scrollTo({top:document.getElementById('editForm').offsetTop-20, behavior:'smooth'});
}
</script>

</body>
</html>
