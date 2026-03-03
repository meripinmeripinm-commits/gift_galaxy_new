<?php
// about_us.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>About Us | Gift Galaxy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body{font-family:Segoe UI,Arial;background:#f3f4f9;margin:0;padding:0;color:#222;}
.container{max-width:800px;margin:50px auto;background:#fff;padding:30px;border-radius:20px;box-shadow:0 20px 50px rgba(0,0,0,.08);}
h1{color:#50207A;margin-bottom:20px;}
p{line-height:1.7;margin-bottom:16px;}
.back-btn{display:inline-block;margin-top:20px;background:#50207A;color:#fff;padding:10px 16px;border-radius:12px;text-decoration:none;font-weight:700;}
.back-btn:hover{opacity:.85;}
</style>
</head>
<body>
<div class="container">
<h1>About Gift Galaxy</h1>
<p>Hi, a 21-year-old dreamer from Kanyakumari, Tamil Nadu, India, and the proud founder of Gift Galaxy Co. Yes! I'm Meripin M. I’m not a big corporation or a team of dozens of people—I’m just one person with a vision, a laptop (ChatGPT + VS Code), and an unshakable belief that even small ideas can create something extraordinary. Ever since I was a child, I’ve been fascinated by the joy that thoughtful gifts bring to people (because I couldn’t give gifts to my loved ones). That curiosity turned into a passion, and now, at this young age, I’m running my little company, dedicated to helping people share happiness and love through personalized gifts.</p>

<p>Every product, every design, and every experience on Gift Galaxy is personally curated by me. I believe in quality, creativity, and a touch of magic in every gift I offer. Running a business alone is not easy, but the challenges only fuel my determination. Each day is a new lesson in entrepreneurship, technology, and connecting with people across India. I’m constantly learning, evolving, and working to make Gift Galaxy a place where every gift feels special, personal, and memorable.</p>

<p>Even though I’m young, I carry a big dream—to make gifting more meaningful and joyful for everyone. My journey is just beginning, but I hope that through my small efforts, I can bring smiles, warm hearts, and moments of happiness to people everywhere. Gift Galaxy is more than a business; it’s a reflection of my dedication, creativity, and belief in the power of small gestures. I invite you to be part of this journey, celebrate life’s special moments, and experience the joy of gifting with me.</p>

<a href="product_detail.php?id=<?php echo $_GET['id'] ?? ''; ?>" class="back-btn">⬅ Back</a>
</div>
</body>
</html>
