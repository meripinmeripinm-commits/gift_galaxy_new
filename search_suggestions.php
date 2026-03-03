<?php
include 'config.php';

header('Content-Type: application/json');

$term = isset($_GET['term']) ? mysqli_real_escape_string($conn, $_GET['term']) : "";

if(!$term){
    echo json_encode([]);
    exit;
}

/* Fetch up to 10 matching product names */
$query = mysqli_query($conn, "
    SELECT product_name 
    FROM products 
    WHERE product_name LIKE '%$term%' 
    ORDER BY product_name ASC 
    LIMIT 10
");

$results = [];
while($row = mysqli_fetch_assoc($query)){
    $results[] = $row['product_name'];
}

echo json_encode($results);
