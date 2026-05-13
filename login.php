<?php
session_start();
include "koneksi.php";

if(isset($_POST['admin_login'])){
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPassword = $_POST['admin_password'] ?? '';
    $admins = [
        'admin@futsal.id' => 'futsalhebat123',
        'admin1@futsal.id' => 'futsalhebat321',
    ];

    if(isset($admins[$adminEmail]) && $admins[$adminEmail] === $adminPassword){
        $_SESSION['admin_login'] = true;
        $_SESSION['admin_email'] = $adminEmail;
        echo "<script>alert('Login admin berhasil');window.location='admin_dashboard.php';</script>";
        exit;
    }else{
        echo "<script>alert('Email atau password admin salah');</script>";
    }
}

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = mysqli_query($conn,"SELECT * FROM users 
    WHERE email='$email' AND password='$password'");

    $data = mysqli_fetch_assoc($query);

    if(mysqli_num_rows($query) > 0){

        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];

        echo "
        <script>
            alert('Login berhasil');
            window.location='dashboard.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Email atau Password salah');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Login FootballZone</title>

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
}

.input-box input{
width:100%;
padding:14px;
border:none;
border-radius:14px;
background:#1d3d27;
color:white;
outline:none;
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
margin-top:10px;
}

.btn:hover{
transform:scale(1.03);
}

.link{
margin-top:15px;
text-align:right;
}

.link a{
color:#9dffb2;
text-decoration:none;
}

.bottom{
margin-top:20px;
text-align:center;
}

.bottom a{
color:#9dffb2;
text-decoration:none;
}

.football{
position:absolute;
top:-30px;
right:-30px;
width:140px;
opacity:0.1;
cursor:pointer;
user-select:none;
}

.admin-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,0.65);z-index:9999;padding:18px;}
.admin-modal.is-open{display:flex;}
.admin-box{width:min(420px,94vw);background:rgba(13,33,18,0.98);border:1px solid rgba(157,255,178,0.25);border-radius:22px;padding:22px;color:white;box-shadow:0 18px 70px rgba(0,0,0,0.6);}
.admin-box h3{color:#9dffb2;margin-bottom:14px;font-size:22px;}
.admin-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:12px;}
.admin-actions button{width:auto;padding:12px 16px;}

</style>
</head>

<link rel="stylesheet" href="assets/page-transition.css">
<script defer src="assets/page-transition.js"></script>

<body>

<div id="page" class="container">

<div class="left">


<h1>⚽ FootballZone</h1>

<p>
Login dan nikmati vibes stadion malam
yang cozy dan modern.
</p>

</div>

<div class="right">

<img class="football"

src="https://cdn-icons-png.flaticon.com/512/53/53283.png">

<h2>Login Akun</h2>

<form method="POST">

<div class="input-box">
<label>Email</label>
<input type="email" name="email" required>
</div>

<div class="input-box">
<label>Password</label>
<input type="password" name="password" required>
</div>

<button type="submit" name="login" class="btn">
Masuk Sekarang
</button>

</form>

<div class="link">
<a href="forgot_password.php">Lupa Password?</a>
</div>

<div class="bottom">
Belum punya akun?
<a href="register.php">Daftar</a>
</div>

</div>

</div>

<div class="admin-modal" id="adminModal" aria-hidden="true">
<div class="admin-box">
<h3>Login Admin</h3>
<form method="POST">
<div class="input-box">
<label>Email Admin</label>
<input type="email" name="admin_email" required>
</div>
<div class="input-box">
<label>Password Admin</label>
<input type="password" name="admin_password" required>
</div>
<div class="admin-actions">
<button type="button" class="btn" id="adminCancel" style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);">Batal</button>
<button type="submit" name="admin_login" class="btn">Masuk Admin</button>
</div>
</form>
</div>
</div>

<script>
(function(){
    const ball = document.querySelector('.football');
    const modal = document.getElementById('adminModal');
    const cancel = document.getElementById('adminCancel');
    let clicks = 0;
    let timer = null;

    function openAdmin(){
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden','false');
    }

    function closeAdmin(){
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden','true');
    }

    ball && ball.addEventListener('click', function(){
        clicks++;
        clearTimeout(timer);
        timer = setTimeout(function(){ clicks = 0; }, 1400);
        if(clicks >= 5){
            clicks = 0;
            openAdmin();
        }
    });

    cancel && cancel.addEventListener('click', closeAdmin);
    modal && modal.addEventListener('click', function(e){
        if(e.target === modal) closeAdmin();
    });
})();
</script>

</body>
</html>
