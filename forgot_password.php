<?php
include "koneksi.php";

if(isset($_POST['reset'])){

    $email = $_POST['email'];

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($cek) > 0){
        // bawa email ke halaman reset
        echo "
        <script>
            window.location='reset_password.php?email=".urlencode($email)."';
        </script>
        ";
    }else{
        echo "<script>alert('Email tidak ditemukan');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Forgot Password FootballZone</title>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{
min-height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:
linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.6)),
url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=2070&auto=format&fit=crop');
background-size:cover;
background-position:center;
overflow:hidden;
}

.container{
width:900px;
height:560px;
display:flex;
border-radius:30px;
overflow:hidden;
backdrop-filter:blur(12px);
background:rgba(20,40,20,0.45);
box-shadow:0 10px 40px rgba(0,0,0,0.5);
border:1px solid rgba(255,255,255,0.15);
}

.left{
width:50%;
background:
linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.5)),
url('https://images.unsplash.com/photo-1517927033932-b3d18e61fb3a?q=80&w=1974&auto=format&fit=crop');
background-size:cover;
background-position:center;
padding:40px;
display:flex;
flex-direction:column;
justify-content:flex-end;
color:white;
}

.left h1{
font-size:42px;
margin-bottom:10px;
color:#a8ffb0;
}

.left p{
font-size:16px;
line-height:1.6;
color:#e8ffe8;
}

.right{
width:50%;
background:rgba(13,33,18,0.88);
padding:40px;
color:white;
position:relative;
}

h2{
margin-bottom:25px;
color:#9dffb2;
font-size:30px;
}

.input-box{
margin-bottom:18px;
}

.input-box label{
display:block;
margin-bottom:8px;
font-size:14px;
color:#d8ffd8;
}

.input-box input{
width:100%;
padding:14px;
border:none;
border-radius:14px;
background:#1d3d27;
color:white;
outline:none;
font-size:15px;
transition:0.3s;
}

.input-box input:focus{
background:#285534;
box-shadow:0 0 10px #63ff8a;
}

.btn{
width:100%;
padding:14px;
border:none;
border-radius:14px;
background:linear-gradient(135deg,#43c465,#2f8f4d);
color:white;
font-size:16px;
font-weight:bold;
cursor:pointer;
transition:0.3s;
margin-top:10px;
}

.btn:hover{
transform:scale(1.03);
box-shadow:0 0 20px rgba(106,255,145,0.5);
}

.bottom{
margin-top:20px;
text-align:center;
}

.bottom a{
color:#9dffb2;
text-decoration:none;
}

.bottom a:hover{
text-decoration:underline;
}

.football{
position:absolute;
top:-30px;
right:-30px;
width:140px;
opacity:0.1;
transform:rotate(20deg);
}

@media(max-width:900px){

.container{
flex-direction:column;
width:95%;
height:auto;
}

.left,.right{
width:100%;
}

.left{
height:220px;
}

}

</style>

</head>

<link rel="stylesheet" href="assets/page-transition.css">
<script defer src="assets/page-transition.js"></script>

<body>

<div id="page" class="container">

<div class="left">


<h1>⚽ FootballZone</h1>

<p>
Reset password akunmu dan kembali
menikmati vibes stadion malam
yang cozy dan modern.
</p>

</div>

<div class="right">

<img class="football"
src="https://cdn-icons-png.flaticon.com/512/53/53283.png">

<h2>Lupa Password</h2>

<form method="POST">

<div class="input-box">
<label>Email</label>
<input type="email" name="email" required>
</div>


<button type="submit" name="reset" class="btn">
Simpan Password Baru
</button>

</form>

<div class="bottom">
Kembali ke
<a href="login.php">Login</a>
</div>

</div>

</div>

</body>
</html>