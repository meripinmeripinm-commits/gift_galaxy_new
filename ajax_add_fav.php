<?php
session_start();
include 'config.php';

header('Content-Type: text/plain'); // Respond as plain text

if(!isset($_SESSION['user_id'])){
    echo "Please login to add favorites.";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if($product_id <= 0){
    echo "Invalid product.";
    exit;
}

// Check if already in favorites
$check = mysqli_query($conn, "SELECT * FROM wishlist WHERE user_id=$user_id AND product_id=$product_id");
if(mysqli_num_rows($check) > 0){
    echo "Already in favorites.";
    exit;
}

// Insert into wishlist
$insert = mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)");
if($insert){
    echo "Added to favorites!";
} else {
    echo "Failed to add to favorites.";
}
?>
