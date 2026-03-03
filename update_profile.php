<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER DATA */
$q = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

/* HANDLE AVATAR UPLOAD */
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $file_name = $_FILES['avatar']['name'];
    $file_tmp = $_FILES['avatar']['tmp_name'];
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        $new_name = "avatar_".$user_id."_".time().".".$ext;
        $upload_dir = "uploads/avatars/";
        if (!is_dir($upload_dir)) mkdir($upload_dir,0755,true);
        move_uploaded_file($file_tmp, $upload_dir.$new_name);
        mysqli_query($conn, "UPDATE users SET avatar='$new_name' WHERE id='$user_id'");
    }

    // Redirect back to profile page after avatar upload
    header("Location: profile.php?success=1");
    exit;
}

/* HANDLE PROFILE UPDATE */
if (isset($_POST['save'])) {
    $required_fields = ['username','email','phone','address','district','state','country','pincode'];
    $errors = [];
    $data = [];

    // Validate required fields
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $errors[] = $field;
        } else {
            $data[$field] = mysqli_real_escape_string($conn, trim($_POST[$field]));
        }
    }

    // Optional fields
    $data['landmark'] = isset($_POST['landmark']) ? mysqli_real_escape_string($conn, trim($_POST['landmark'])) : '';

    if (empty($errors)) {
        $update_query = "UPDATE users SET 
            username='{$data['username']}',
            email='{$data['email']}',
            phone='{$data['phone']}',
            address='{$data['address']}',
            landmark='{$data['landmark']}',
            district='{$data['district']}',
            state='{$data['state']}',
            country='{$data['country']}',
            pincode='{$data['pincode']}'
            WHERE id='$user_id'";
        mysqli_query($conn, $update_query);

        // Redirect to homepage after save
        header("Location: index.php");
        exit;
    } else {
        // Redirect back to profile if fields incomplete
        header("Location: profile.php?error=incomplete");
        exit;
    }
}
?>
