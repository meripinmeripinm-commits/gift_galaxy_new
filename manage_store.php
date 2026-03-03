<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

/* ================= USER SESSION ================= */
if(!isset($_SESSION['user_id'])){
    echo "Seller not logged in.";
    exit();
}

$user_id = intval($_SESSION['user_id']);
$success = "";
$error   = "";

/* ================= FETCH USER + SELLER DATA ================= */
$stmt = mysqli_prepare($conn, "SELECT u.*, s.store_name, s.id AS seller_id, s.last_name_change FROM users u LEFT JOIN sellers s ON u.id=s.user_id WHERE u.id=?");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(!$result || mysqli_num_rows($result)==0){
    die("User not found.");
}

$user = mysqli_fetch_assoc($result);

/* ================= STORE NAME CHANGE CONTROL ================= */
$today = date("Y-m-d");
$last_change = $user['last_name_change'] ?? null;
$name_locked = false;
$remaining_days = 0;
$next_change_date = "";

if(!empty($last_change)){
    $diff = (strtotime($today) - strtotime($last_change)) / (60*60*24);
    if($diff < 20){
        $name_locked = true;
        $remaining_days = 20 - floor($diff);
        $next_change_date = date("d M Y", strtotime($last_change . " +20 days"));
    }
}

/* ================= UPDATE STORE ================= */
if(isset($_POST['update_store'])){

    $store_name  = trim($_POST['store_name']);
    $store_phone = trim($_POST['store_phone']);

    if(empty($store_name)){
        $error = "Store name is required.";
    }
    elseif(empty($store_phone)){
        $error = "Phone is required.";
    }
    elseif(!preg_match('/^[0-9]{10}$/',$store_phone)){
        $error = "Phone must be 10 digits.";
    }
    else{

        if($name_locked && $store_name != $user['store_name']){
            $error = "You can change store name after $remaining_days more days.";
        }
        else{

            // Update store name in sellers table if changed
            if($store_name != $user['store_name']){
                $stmt = mysqli_prepare($conn,"
                    UPDATE sellers 
                    SET store_name=?, last_name_change=? 
                    WHERE user_id=?

                ");
                mysqli_stmt_bind_param($stmt,"ssi",$store_name,$today,$user_id);
                mysqli_stmt_execute($stmt);
            }

            // Update phone in users table
            $stmt_phone = mysqli_prepare($conn,"UPDATE users SET phone=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_phone,"si",$store_phone,$user_id);
            if(mysqli_stmt_execute($stmt_phone)){
                $success = "Store updated successfully!";
            } else {
                $error = "Something went wrong.";
            }
        }
    }
}

/* ================= SAVE VERIFICATION ================= */
if(isset($_POST['save_bank'])){

    $pan     = trim($_POST['pan_number']);
    $aadhaar = trim($_POST['aadhaar_number']);
    $address = trim($_POST['business_address']);
    $holder  = trim($_POST['account_holder_name']);
    $account = trim($_POST['bank_account']);
    $ifsc    = trim($_POST['ifsc_code']);

    if(empty($pan) || empty($holder) || empty($account) || empty($ifsc)){
        $error = "All required verification fields are required.";
    }
    else{

        /* ===== FILE UPLOAD (OPTIONAL) ===== */
        $cheque_path = $user['cancelled_cheque'] ?? "";
        $id_path     = $user['id_proof'] ?? "";

        if(!empty($_FILES['cancelled_cheque']['name'])){
            $cheque_path = "uploads/cheque_" . time() . "_" . $_FILES['cancelled_cheque']['name'];
            move_uploaded_file($_FILES['cancelled_cheque']['tmp_name'],$cheque_path);
        }

        if(!empty($_FILES['id_proof']['name'])){
            $id_path = "uploads/id_" . time() . "_" . $_FILES['id_proof']['name'];
            move_uploaded_file($_FILES['id_proof']['tmp_name'],$id_path);
        }

        $stmt = mysqli_prepare($conn,"
            UPDATE users 
            SET pan_number=?, 
                aadhaar_number=?,
                business_address=?,
                account_holder_name=?, 
                bank_account=?, 
                ifsc_code=?,
                cancelled_cheque=?,
                id_proof=?
            WHERE id=?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssssi",
            $pan,
            $aadhaar,
            $address,
            $holder,
            $account,
            $ifsc,
            $cheque_path,
            $id_path,
            $user_id
        );

        if(mysqli_stmt_execute($stmt)){
            $success = "Verification details saved successfully!";
        } else {
            $error = "Something went wrong.";
        }
    }
}

/* ================= ACTIVATE RAZORPAY ================= */
if(isset($_POST['activate_razorpay'])){

    if(empty($user['pan_number']) || empty($user['account_holder_name']) || empty($user['bank_account']) || empty($user['ifsc_code'])){
        $error = "Please complete verification details before activation.";
    }
    else{
        mysqli_query($conn,"UPDATE users SET razorpay_status='pending' WHERE id='$user_id'");
        $success = "Activation request initiated.";
    }
}

/* ================= REFRESH USER ================= */
$stmt = mysqli_prepare($conn,"SELECT u.*, s.store_name, s.last_name_change 
FROM users u 
LEFT JOIN sellers s ON u.id=s.user_id 
WHERE u.id=?");
mysqli_stmt_bind_param($stmt,"i",$user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

?>
<!DOCTYPE html>
<html>
<head>
<title>🏪 Manage Store - Gift Galaxy</title>

<style>
body{
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(135deg,#667eea,#764ba2);
    margin:0;
    padding:0;
}
.container{
    width:90%;
    max-width:850px;
    margin:50px auto;
    background:#fff;
    padding:35px;
    border-radius:15px;
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
}
h2{margin-top:0;}
label{
    font-weight:600;
    display:block;
    margin-top:15px;
}
input, textarea{
    width:100%;
    padding:12px;
    margin-top:6px;
    border:1px solid #ddd;
    border-radius:8px;
}
button{
    margin-top:20px;
    padding:12px 25px;
    background:linear-gradient(135deg,#667eea,#764ba2);
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
.success{
    background:#e8fff1;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    color:#0f5132;
}
.error{
    background:#ffeaea;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    color:#842029;
}
.section{
    margin-top:40px;
    padding-top:25px;
    border-top:1px solid #eee;
}
.info-box{
    background:#eef2ff;
    padding:10px;
    border-radius:8px;
    margin-top:10px;
    font-size:14px;
}
</style>
</head>
<body>

<div class="container">

<h2>🏪 Manage Store</h2>

<?php if($success) echo "<div class='success'>$success</div>"; ?>
<?php if($error) echo "<div class='error'>$error</div>"; ?>

<form method="POST">

<label>Store Name</label>
<input type="text" name="store_name"
value="<?= htmlspecialchars($user['store_name'] ?? ''); ?>"
<?= $name_locked ? 'readonly' : ''; ?> required>

<?php if($name_locked): ?>
<div class="info-box">
You can change store name in <strong><?= $remaining_days ?></strong> days.<br>
Next available date: <strong><?= $next_change_date ?></strong>
</div>
<?php endif; ?>

<label>Phone</label>
<input type="text" name="store_phone"
value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" required>

<button type="submit" name="update_store">Update Store</button>

</form>

<div class="section">
<h2>🔐 Seller Verification</h2>

<form method="POST" enctype="multipart/form-data">

<label>PAN Number *</label>
<input type="text" name="pan_number"
value="<?= htmlspecialchars($user['pan_number'] ?? ''); ?>" required>

<label>Aadhaar Number (Optional)</label>
<input type="text" name="aadhaar_number"
value="<?= htmlspecialchars($user['aadhaar_number'] ?? ''); ?>">

<label>Business Address (Optional)</label>
<textarea name="business_address"><?= htmlspecialchars($user['business_address'] ?? ''); ?></textarea>

<label>Account Holder Name *</label>
<input type="text" name="account_holder_name"
value="<?= htmlspecialchars($user['account_holder_name'] ?? ''); ?>" required>

<label>Bank Account Number *</label>
<input type="text" name="bank_account"
value="<?= htmlspecialchars($user['bank_account'] ?? ''); ?>" required>

<label>IFSC Code *</label>
<input type="text" name="ifsc_code"
value="<?= htmlspecialchars($user['ifsc_code'] ?? ''); ?>" required>

<label>Upload Cancelled Cheque (Optional)</label>
<input type="file" name="cancelled_cheque">

<label>Upload ID Proof (Optional)</label>
<input type="file" name="id_proof">

<button type="submit" name="save_bank">Save Verification</button>

</form>
</div>

<div class="section">
<h2>🚀 Razorpay Activation</h2>
<p>Status: <strong><?= strtoupper($user['razorpay_status'] ?? 'not started'); ?></strong></p>

<?php if(($user['razorpay_status'] ?? '')!='activated'){ ?>
<form method="POST">
<button type="submit" name="activate_razorpay">Complete Razorpay KYC</button>
</form>
<?php } else { ?>
<div class="success">Your account is activated. You can now sell products.</div>
<?php } ?>

<div class="info-box">
• PAN name must match bank account holder name.<br>
• Activation may take 24–48 hours.<br>
• GST not required for small individual sellers in Gift Galaxy.
</div>

</div>

</div>
</body>
</html>
