<?php
session_start();

if(!isset($_SESSION['id_user'])){
    header("Location: login.php");
    exit;
}

include "koneksi.php";

$id_user = $_SESSION['id_user'] ?? '';
$nama = $_SESSION['nama'] ?? '';
$username = $_SESSION['username'] ?? $nama;
$email = '';
$saldo = $_SESSION['saldo'] ?? '0';

$profilePhotoChoice = $_SESSION['profile_photo_choice'] ?? '';
if(!$profilePhotoChoice){
    // default pakai man1
    $profilePhotoChoice = 'man1.png';
}

if($id_user !== ''){
    // ambil email (kolom wajib)
    $q = mysqli_query($conn, "SELECT email FROM users WHERE id_user='$id_user' LIMIT 1");
    if($q && mysqli_num_rows($q) > 0){
        $row = mysqli_fetch_assoc($q);
        $email = $row['email'] ?? '';
    }
}

function getProfileImageUrlFromChoice(string $choice): string {
    $allowed = ['man1.png','man2.png','man3.png','women1.png','women2.png','women3.png'];
    if(!in_array($choice, $allowed, true)){
        return 'assets/man1.png';
    }
    return 'assets/'.$choice;
}

function pickProfileImage($jenis_kelamin): string {
    $j = strtolower(trim((string)$jenis_kelamin));

    // Jika database tidak punya nilai, default ke man1.png
    if($j === 'l' || $j === 'laki-laki' || $j === 'laki laki' || $j === 'male' || $j === 'pria'){
        $set = ['man1.png','man2.png','man3.png'];
    } else if($j === 'p' || $j === 'perempuan' || $j === 'women' || $j === 'female' || $j === 'wanita'){
        $set = ['women1.png','women2.png','women3.png'];
    } else {
        $set = ['man1.png','man2.png','man3.png'];
    }

    // deterministic random berdasarkan id_user agar konsisten
    $seed = crc32($jenis_kelamin);
    $idx = abs($seed) % count($set);
    return $set[$idx];
}

// Pilih image dari pilihan session (6 gambar assets)
$imgUrl = getProfileImageUrlFromChoice($profilePhotoChoice);


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - FootballZone</title>
    <link rel="stylesheet" href="assets/page-transition.css">
    <script defer src="assets/page-transition.js"></script>
    <link rel="stylesheet" href="assets/dashboard.css">

    <style>
        body{ overflow-x:hidden; }

        .profile-page{
            min-height:100vh;
            padding:22px;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .fade-in{
            animation: fadeIn .35s ease-out both;
        }

        @keyframes fadeIn{
            from{ opacity:0; transform: translateY(8px); }
            to{ opacity:1; transform: translateY(0); }
        }

        .profile-card{
            width:min(560px, 94vw);

            background:rgba(7,22,14,0.92);
            border:1px solid rgba(67,196,101,0.25);
            border-radius:28px;
            padding:26px 22px;

            backdrop-filter: blur(10px);
            box-shadow:
                0 18px 70px rgba(0,0,0,0.45),
                0 0 0 1px rgba(67,196,101,0.06),
                0 0 26px rgba(67,196,101,0.08);

            display:flex;
            flex-direction:column;
            gap:18px;
        }

        .profile-avatar{
            width:116px;
            height:116px;
            border-radius:50%;
            overflow:hidden;
            margin:0 auto;

            border:2px solid rgba(67,196,101,0.45);
            box-shadow:
                0 0 0 6px rgba(67,196,101,0.07),
                0 0 32px rgba(67,196,101,0.20);
            background:rgba(255,255,255,0.03);
            flex:0 0 116px;
        }

        .profile-avatar img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
        }

        .profile-center{
            text-align:center;
            display:flex;
            flex-direction:column;
            align-items:center;
            gap:8px;
        }

        .profile-username{
            color:#9dffb2;
            font-weight:1000;
            font-size:26px;
            letter-spacing:0.2px;
        }

        .profile-email{
            color:rgba(255,255,255,0.78);
            font-size:14.5px;
            word-break:break-word;
        }

        .profile-saldo{
            margin-top:6px;
            padding:10px 14px;
            border-radius:999px;
            border:1px solid rgba(67,196,101,0.35);
            background:rgba(67,196,101,0.12);
            color:#e8ffe8;
            font-weight:1000;
            font-size:15px;
        }

        .profile-actions{
            display:flex;
            gap:12px;
            flex-wrap:wrap;
            justify-content:center;
            margin-top:8px;
        }

        .btn{
            border:none;
            border-radius:16px;
            padding:12px 14px;
            cursor:pointer;
            font-weight:1000;
            font-size:14px;
            transition: transform .15s ease, filter .15s ease, background .15s ease, border-color .15s ease;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
        }

        .btn:hover{ transform: translateY(-1px); filter: brightness(1.05); }

        .btn--ghost{
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.10);
            color:#e8ffe8;
        }

        .btn--primary{
            background:linear-gradient(135deg,#43c465,#2f8f4d);
            color:white;
        }

        .btn--danger{
            background:linear-gradient(135deg,#ff5b5b,#cc2f2f);
            color:white;
        }

        .btn--neon{
            background:rgba(67,196,101,0.12);
            border:1px solid rgba(67,196,101,0.35);
            color:#9dffb2;
        }

        /* Modal */
        .modal{
            position:fixed;
            inset:0;
            display:none;
            z-index:9999;
        }

        .modal.is-open{ display:block; }

        .modal__backdrop{
            position:absolute;
            inset:0;
            background:rgba(0,0,0,0.6);
        }

        .modal__dialog{
            position:relative;
            width:min(520px, 92vw);
            margin:8vh auto 0 auto;

            background:rgba(7,22,14,0.98);
            border:1px solid rgba(67,196,101,0.25);
            border-radius:24px;
            padding:18px;

            box-shadow:0 18px 70px rgba(0,0,0,0.55);
        }

        .modal__title{
            color:#9dffb2;
            font-weight:1000;
            font-size:18px;
            margin-bottom:8px;
        }

        .modal__text{ color:rgba(255,255,255,0.75); font-size:13.5px; margin-bottom:14px; line-height:1.5; }

        .form-grid{ display:flex; flex-direction:column; gap:12px; }

        .field label{ display:block; font-size:12.5px; color:rgba(255,255,255,0.7); font-weight:900; margin-bottom:6px; }

        .field input{
            width:100%;
            padding:12px 12px;
            border-radius:14px;
            border:1px solid rgba(255,255,255,0.10);
            background:rgba(255,255,255,0.04);
            color:white;
            outline:none;
        }

        .photo-pick{
            display:grid;
            grid-template-columns: repeat(3, 1fr);
            gap:10px;
            margin-top:6px;
        }

        .photo-pick__item{
            display:block;
            cursor:pointer;
            border-radius:16px;
            border:1px solid rgba(255,255,255,0.10);
            background:rgba(255,255,255,0.04);
            padding:8px;
            transition: transform .15s ease, border-color .15s ease, filter .15s ease, background .15s ease;
            text-align:center;
        }

        .photo-pick__item img{
            width:100%;
            height:78px;
            object-fit:cover;
            border-radius:12px;
            display:block;
        }

        .photo-pick__item input{
            display:none;
        }

        .photo-pick__item[data-active="true"]{
            border-color: rgba(67,196,101,0.55);
            background: rgba(67,196,101,0.10);
            box-shadow: 0 0 0 2px rgba(67,196,101,0.12);
        }

        .photo-pick__item:hover{
            transform: translateY(-1px);
            filter: brightness(1.05);
        }

        .photo-pick__hint{
            margin-top:10px;
            color: rgba(255,255,255,0.65);
            font-size:12.5px;
            text-align:center;
            font-weight:800;
        }


        .modal__actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:14px; }

        .hidden{ display:none; }

        @media (max-width:520px){
            .profile-card{ padding:22px 16px; }
            .profile-avatar{ width:104px; height:104px; flex-basis:104px; }
            .profile-username{ font-size:24px; }
        }
    </style>
</head>
<body>

<div class="profile-page">
    <div class="profile-card fade-in">
        <div class="profile-avatar">
            <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Foto Profil" onerror="this.src='assets/man1.png';">
        </div>

        <div class="profile-center">
            <div class="profile-username"><?php echo htmlspecialchars($username); ?></div>
            <div class="profile-email"><?php echo htmlspecialchars($email); ?></div>
            <div class="profile-saldo">Saldo: Rp<?php echo htmlspecialchars($saldo); ?></div>
        </div>

        <div class="profile-actions">
            <button type="button" class="btn btn--neon" id="btnEdit">
                ✏️ Edit Profil
            </button>
            <button type="button" class="btn btn--ghost" id="btnTopUp" disabled>
                💸 Top Up Saldo
            </button>
            <a class="btn btn--danger" href="logout.php">⏻ Logout</a>
            <a class="btn btn--ghost" href="dashboard.php">⬅️ Kembali</a>
        </div>

        <!-- Modal Edit Profil -->
        <div class="modal" id="modalEdit" aria-hidden="true">
            <div class="modal__backdrop" data-close="true"></div>
            <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
                <div class="modal__title" id="modalEditTitle">Edit Profil</div>
<div class="modal__text">Ubah username dan email, serta pilih foto profil dari 6 opsi gambar.</div>

                <form class="form-grid" method="POST">
                    <input type="hidden" name="edit_profile" value="1" />
                    <div class="field">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required />
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                    </div>

                    <div class="field">
                        <label>Pilih Gambar Profil</label>
                        <div class="photo-pick" role="radiogroup" aria-label="Pilih gambar profil">
                            <?php
                                $photoOptions = ['man1.png','man2.png','man3.png','women1.png','women2.png','women3.png'];
                                foreach($photoOptions as $opt){
                                    $isChecked = ($profilePhotoChoice === $opt);
                                    $img = 'assets/'.$opt;
                                    $id = 'photo_'.$opt;
                                    $label = ucfirst(str_replace('.png','',$opt));
                                    echo '<label class="photo-pick__item" for="'.$id.'" tabindex="0" '.($isChecked ? 'data-active="true"' : '').'>
                                            <img src="'.$img.'" alt="'.$label.'" onerror="this.src=\'assets/man1.png\'">
                                            <input type="radio" name="profile_photo_choice" value="'.$opt.'" id="'.$id.'" '.($isChecked ? 'checked' : '').'>
                                          </label>';
                                }
                            ?>
                        </div>
                        <div class="photo-pick__hint">Ganti gambar profil</div>
                    </div>


                    <div class="modal__actions">
                        <button type="button" class="btn btn--ghost" data-close="true">Batal</button>
                        <button type="submit" class="btn btn--primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    const modal = document.getElementById('modalEdit');
    const btnEdit = document.getElementById('btnEdit');

    function openModal(){
        if(!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden','false');
    }

    function closeModal(){
        if(!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden','true');
    }

    if(btnEdit){ btnEdit.addEventListener('click', openModal); }

    document.querySelectorAll('[data-close="true"]').forEach(el => {
        el.addEventListener('click', closeModal);
    });

    modal && modal.addEventListener('click', (e)=>{
        if(e.target && e.target.getAttribute('data-close') === 'true') closeModal();
    });
</script>

<?php
if(isset($_POST['edit_profile'])){
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    if($newUsername !== '' && $newEmail !== ''){
        // Update username & email
        // DB project ini memakai kolom: nama (bukan username) dan email.

        $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, email=? WHERE id_user=?");
        if($stmt){
            mysqli_stmt_bind_param($stmt, 'ssi', $newUsername, $newEmail, $id_user);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }


        // simpan pilihan foto profil ke session (6 opsi assets)
        $newPhotoChoice = $_POST['profile_photo_choice'] ?? '';
        $allowed = ['man1.png','man2.png','man3.png','women1.png','women2.png','women3.png'];
        if(in_array($newPhotoChoice, $allowed, true)){
            $_SESSION['profile_photo_choice'] = $newPhotoChoice;
        }

        // refresh session user data
        $_SESSION['nama'] = $newUsername;
        $_SESSION['username'] = $newUsername;


        echo "<script>window.location='profil.php';</script>";
        exit;
    }
}
?>

</body>
</html>


