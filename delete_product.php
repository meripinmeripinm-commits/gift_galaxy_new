<?php
session_start();
include 'config.php';

// Check user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch seller info
$seller = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM sellers WHERE user_id='$user_id'"));
if (!$seller) {
    header("Location: seller.php");
    exit;
}

$seller_id = $seller['id'];

// Check product ID
if (!isset($_GET['id'])) {
    header("Location: seller.php");
    exit;
}

$product_id = intval($_GET['id']);

// 1️⃣ Delete associated reviews
mysqli_query($conn, "DELETE FROM reviews WHERE product_id='$product_id'");

// 2️⃣ Soft delete the product (mark deleted=1)
$sql = "UPDATE products SET deleted=1 WHERE id='$product_id' AND seller_id='$seller_id'";
if (mysqli_query($conn, $sql)) {
    header("Location: seller.php?success=product_deleted");
    exit;
} else {
    die("Error deleting product: " . mysqli_error($conn));
}
?>
