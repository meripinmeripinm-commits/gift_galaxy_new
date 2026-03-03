<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));

if($user['seller_paid'] == 1){
    header("Location: seller.php");
    exit();
}

if(isset($_POST['payment_success'])){
    mysqli_query($conn,"UPDATE users SET seller_paid=1 WHERE id='$user_id'");
    echo "success";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Become Seller | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#0f0c29;
    background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
    overflow:hidden;
    color:white;
}

/* Floating Glow Background */
.glow{
    position:absolute;
    width:400px;
    height:400px;
    background:#7B2CBF;
    filter:blur(150px);
    opacity:0.4;
    animation:float 8s infinite alternate ease-in-out;
}
.glow2{
    background:#D646FF;
    right:0;
    bottom:0;
    animation-delay:3s;
}
@keyframes float{
    from{transform:translateY(-50px);}
    to{transform:translateY(50px);}
}

.card{
    position:relative;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(25px);
    border-radius:25px;
    padding:40px;
    width:95%;
    max-width:520px;
    text-align:center;
    box-shadow:0 0 40px rgba(214,70,255,0.4);
    animation:fadeIn 1s ease-in-out;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(40px);}
    to{opacity:1; transform:translateY(0);}
}

h1{
    font-size:28px;
    margin-bottom:10px;
    background:linear-gradient(90deg,#D646FF,#ffffff);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

.subtitle{
    font-size:14px;
    opacity:0.8;
    margin-bottom:20px;
}

.price{
    font-size:36px;
    font-weight:bold;
    margin:20px 0;
    color:#D646FF;
}

.features{
    text-align:left;
    font-size:14px;
    line-height:1.8;
    margin:20px 0;
}

.features span{
    color:#D646FF;
    font-weight:600;
}

.earn-box{
    background:rgba(255,255,255,0.05);
    padding:15px;
    border-radius:15px;
    margin:20px 0;
    font-size:13px;
}

button{
    background:linear-gradient(90deg,#7B2CBF,#D646FF);
    border:none;
    padding:15px;
    width:100%;
    border-radius:50px;
    font-size:16px;
    font-weight:600;
    color:white;
    cursor:pointer;
    transition:0.3s;
    box-shadow:0 0 20px #D646FF;
}

button:hover{
    transform:scale(1.05);
    box-shadow:0 0 40px #D646FF;
}

.note{
    font-size:11px;
    margin-top:15px;
    opacity:0.7;
}

.success-overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:black;
    display:none;
    justify-content:center;
    align-items:center;
    flex-direction:column;
    font-size:22px;
}
</style>
</head>
<body>

<div class="glow"></div>
<div class="glow glow2"></div>

<div class="card">
    <h1>Become a Gift Galaxy Seller</h1>
    <div class="subtitle">Earn 80–85% of every sale. Build your brand. Grow unlimited.</div>

    <div class="price">₹189</div>

    <div class="features">
        ✔ <span>15–20% commission</span> per order<br>
        ✔ No monthly charges<br>
        ✔ Lifetime dashboard access<br>
        ✔ Secure Razorpay payments<br>
        ✔ Performance-based lower commission
    </div>

    <div class="earn-box">
        Example:<br>
        Sell ₹1000 product → Commission ₹180<br>
        <strong>You earn ₹820</strong>
    </div>

    <button onclick="payNow()">Unlock Seller Dashboard</button>

    <div class="note">
        One-time non-refundable registration fee.
    </div>
</div>

<div class="success-overlay" id="successBox">
    <div>🎉 Payment Successful</div>
    <div>Redirecting to Dashboard...</div>
</div>

<script>
function payNow(){
    var options = {
        "key": "rzp_test_SLUuFIqQnJCdyz",
        "amount": 18900,
        "currency": "INR",
        "name": "Gift Galaxy",
        "description": "Seller Registration",
        "handler": function (response){
            fetch("seller_payment.php",{
                method:"POST",
                headers:{"Content-Type":"application/x-www-form-urlencoded"},
                body:"payment_success=1"
            })
            .then(res=>res.text())
            .then(data=>{
                if(data=="success"){
                    document.getElementById("successBox").style.display="flex";
                    setTimeout(function(){
                        window.location="seller.php";
                    },2000);
                }
            });
        },
        "theme": { "color": "#7B2CBF" }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
}
</script>

</body>
</html>
