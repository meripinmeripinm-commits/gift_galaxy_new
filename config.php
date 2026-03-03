<?php
// ===============================
// Gift Galaxy - Database Config
// ===============================

$host = "localhost";
$user = "root";
$pass = "";
$db   = "giftgalaxy";
$port = 3306;

// Create connection
$conn = mysqli_connect($host, $user, $pass, $db, $port);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Optional: Set charset (recommended)
mysqli_set_charset($conn, "utf8mb4");
?>
