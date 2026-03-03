<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "giftgalaxy", 3307);

if ($conn) {
    echo "DB CONNECTED ✅";
} else {
    echo "DB FAILED ❌ : " . mysqli_connect_error();
}
