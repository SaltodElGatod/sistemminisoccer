<?php
include "koneksi.php";

// Email dari query string
$email = isset($_GET['email']) ? $_GET['email'] : '';

// proses update password
if(isset($_POST['reset_password'])){
    session_start();
    // logout paksa jika user sedang login
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    $email_post = isset($_POST['email']) ? $_POST['email'] : '';
$password_baru = md5(trim($_POST['password_baru']));
$confirm_password = trim($_POST['confirm_password']);

if($password_baru !== md5($confirm_password)){
        echo "<script>alert('Konfirmasi password tidak sama');</script>";
    } else {
        $email_post = mysqli_real_escape_string($conn, $email_post);
        $email_post = trim($email_post);

        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email_post'");

        if(mysqli_num_rows($cek) > 0){
            mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE email='$email_post'");

            echo "
            <script>
                alert('Password berhasil diubah');
                window.location='login.php';
            </script>
            ";
        } else {
            echo "<script>alert('Email tidak ditemukan');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Reset Password FootballZone</title>

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

</style>
</head>

<link rel="stylesheet" href="assets/page-transition.css">
<script defer src="assets/page-transition.js"></script>

<body>

<div id="page" class="container">

<div class="left">

<h1>⚽ FootballZone</h1>

<p>
Buat password baru untuk akunmu.
</p>

</div>

<div class="right">

<img class="football" src="https://cdn-icons-png.flaticon.com/512/53/53283.png">

<h2>Reset Password</h2>

<form method="POST">
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">

    <div class="input-box">
        <label>Password Baru</label>
        <input type="password" name="password_baru" required>
    </div>

    <div class="input-box">
        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>
    </div>

    <button type="submit" name="reset_password" class="btn">
        Simpan Password Baru
    </button>
</form>

<div class="bottom">
Kembali ke <a href="login.php">Login</a>
</div>

</div>

</div>

</body>
</html>

