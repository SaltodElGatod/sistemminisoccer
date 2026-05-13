<?php
session_start();
if(!isset($_SESSION['id_user'])){
  header('Location: login.php');
  exit;
}
include 'koneksi.php';

$id_user = $_SESSION['id_user'];
$username = $_SESSION['username'] ?? $_SESSION['nama'] ?? '';

// Ambil parameter dari redirect dashboard
$id_lapangan = $_GET['id_lapangan'] ?? '';
$lapangan = $_GET['lapangan'] ?? '-';
$jadwal = $_GET['jadwal'] ?? '-';
$harga = $_GET['harga'] ?? '0';

// tanggal pembayaran (untuk ditampilkan di struk digital)
$tanggal_bayar = date('Y-m-d');


// simpan untuk dipakai saat POST berisi hidden yang kosong
$_SESSION['last_id_lapangan'] = (int)$id_lapangan;

?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout Booking</title>
  <link rel="stylesheet" href="assets/page-transition.css">
  <script defer src="assets/page-transition.js"></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    body{min-height:100vh;display:flex;justify-content:center;align-items:center;background:linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.6)),url('https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=2070&auto=format&fit=crop');background-size:cover;background-position:center;overflow:hidden;}
    .card{width:min(720px,95vw);background:rgba(13,33,18,0.88);border:1px solid rgba(67,196,101,0.25);border-radius:28px;padding:22px;box-shadow:0 18px 70px rgba(0,0,0,0.55);color:white;}
    h1{color:#9dffb2;margin-bottom:10px;font-size:20px;}
    .row{display:flex;gap:14px;flex-wrap:wrap;margin-top:12px;}
    .box{flex:1 1 220px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.10);border-radius:18px;padding:14px;}
    .label{color:rgba(255,255,255,0.72);font-size:12.5px;font-weight:900;margin-bottom:6px;}
    .val{font-weight:1000;}
    .btn{padding:14px 16px;border:none;border-radius:16px;cursor:pointer;font-weight:1000;}
    .btn-primary{background:linear-gradient(135deg,#43c465,#2f8f4d);color:white;}
    .btn-ghost{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.10);color:#e8ffe8;}
    .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px;}
    .muted{color:rgba(255,255,255,0.72);font-size:13px;line-height:1.5;}
  </style>
</head>
<body>
  <div class="card">
    <h1>Isi Data Booking & Pembayaran</h1>
    <div class="muted">Lakukan pembayaran untuk mengonfirmasi booking jadwal lapangan yang dipilih.</div>

    <div class="row">
      <div class="box">
        <div class="label">Username</div>
        <div class="val"><?php echo htmlspecialchars($username); ?></div>
      </div>
      <div class="box">
        <div class="label">Lapangan</div>
        <div class="val"><?php echo htmlspecialchars($lapangan); ?></div>
      </div>
    </div>

    <div class="row">
      <div class="box">
        <div class="label">Jadwal</div>
        <div class="val"><?php echo htmlspecialchars($jadwal); ?></div>
      </div>
      <div class="box">
        <div class="label">Harga</div>
        <div class="val"><?php echo htmlspecialchars($harga); ?></div>
      </div>
    </div>

    <form method="POST" action="booking_pay.php" style="margin-top:14px;">
      <input type="hidden" name="id_lapangan" value="<?php echo htmlspecialchars($id_lapangan); ?>" />
      <input type="hidden" name="lapangan" value="<?php echo htmlspecialchars($lapangan); ?>" />
      <input type="hidden" name="jadwal" value="<?php echo htmlspecialchars($jadwal); ?>" />
      <input type="hidden" name="harga" value="<?php echo htmlspecialchars($harga); ?>" />

      <div class="actions">
        <a class="btn btn-ghost" href="dashboard.php">Cancel</a>
        <button class="btn btn-primary" type="submit" name="bayar">Bayar Sekarang</button>
      </div>

        <div style="margin-top:14px; padding:12px; border-radius:16px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.10);">
        <div style="font-weight:1000; color:#9dffb2; margin-bottom:8px;">Pembayaran</div>
        <div style="color:rgba(255,255,255,0.75); font-size:13px; line-height:1.45; margin-bottom:10px;">
          Masukkan nominal pembayaran. Jika nominal sesuai harga sewa, booking akan masuk status <b>menunggu verifikasi admin</b>.
        </div>

        <div class="row" style="margin-top:0;">
          <div class="box" style="flex:1 1 280px;">
            <div class="label">Nominal Pembayaran</div>
            <input 
              type="text"
              name="nominal_input"
              placeholder="contoh: <?php echo htmlspecialchars($harga); ?>"
              style="width:100%; padding:12px 12px; border-radius:14px; border:1px solid rgba(255,255,255,0.10); background:rgba(255,255,255,0.04); color:white; outline:none; font-weight:800;"
              required
            />
          </div>
        </div>
      </div>
    </form>

  <script>
    (function(){
      // Ambil harga sewa dari hidden input
      const hargaHidden = document.querySelector('input[name="harga"]');
      const nominalInput = document.querySelector('input[name="nominal_input"]');
      const form = document.querySelector('form[method="POST"]');

      const expected = hargaHidden ? parseInt((hargaHidden.value || '').replace(/[^0-9]/g,''),10) : NaN;

      const getNominal = () => {
        const raw = (nominalInput && nominalInput.value) ? nominalInput.value : '';
        return parseInt(String(raw).replace(/[^0-9]/g,''),10) || 0;
      };

      function submitPayment(redirectTo){
        if(!form) return;
        let redirectInput = form.querySelector('input[name="redirect_to"]');
        if(!redirectInput){
          redirectInput = document.createElement('input');
          redirectInput.type = 'hidden';
          redirectInput.name = 'redirect_to';
          form.appendChild(redirectInput);
        }
        redirectInput.value = redirectTo;
        form.submit();
      }

      // popup loading yang muncul setelah submit.
      let overlay, box, msg, spinner;

      function buildPopup(){
        overlay = document.createElement('div');
        overlay.style.position='fixed';
        overlay.style.inset='0';
        overlay.style.background='rgba(0,0,0,0.55)';
        overlay.style.display='flex';
        overlay.style.alignItems='center';
        overlay.style.justifyContent='center';
        overlay.style.zIndex='99999';

        box = document.createElement('div');
        box.style.width='min(420px,92vw)';
        box.style.background='rgba(13,33,18,0.96)';
        box.style.border='1px solid rgba(67,196,101,0.25)';
        box.style.borderRadius='22px';
        box.style.padding='18px 18px 16px';
        box.style.boxShadow='0 18px 70px rgba(0,0,0,0.6)';
        box.style.color='white';

        const style = document.createElement('style');
        style.textContent = `
          @keyframes bbspin { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
          .bb-spinner { width:44px; height:44px; border-radius:50%; border:4px solid rgba(255,255,255,0.15); border-top-color:#9dffb2; animation: bbspin 0.9s linear infinite; margin: 8px auto 10px; }
          .bb-popup-title { text-align:center; font-weight:1000; color:#9dffb2; margin-bottom:6px; }
          .bb-popup-sub { text-align:center; color:rgba(255,255,255,0.78); font-size:13px; line-height:1.45; }
          .bb-popup-badge { display:inline-flex; align-items:center; gap:8px; margin-top:12px; padding:10px 12px; border-radius:14px; border:1px solid rgba(255,255,255,0.10); background: rgba(255,255,255,0.05); }
          .bb-ok { color:#66ff9a; }
          .bb-bad { color:#ff6b6b; }
        `;
        document.head.appendChild(style);

        const title = document.createElement('div');
        title.className = 'bb-popup-title';
        title.textContent = 'Memproses pembayaran...';

        spinner = document.createElement('div');
        spinner.className = 'bb-spinner';

        msg = document.createElement('div');
        msg.className = 'bb-popup-sub';
        msg.textContent = 'Tunggu sebentar';

        box.appendChild(title);
        box.appendChild(spinner);
        box.appendChild(msg);

        overlay.appendChild(box);
        return overlay;
      }

      function setResult(ok){
        // hilangkan spinner
        if(spinner) spinner.style.display = 'none';

        const badge = document.createElement('div');
        badge.className = 'bb-popup-badge ' + (ok ? 'bb-ok' : 'bb-bad');
        badge.style.textAlign = 'center';
        badge.style.display = 'flex';
        badge.style.justifyContent = 'center';
        badge.style.alignItems = 'center';


        badge.innerHTML = ok
          ? `<span style="font-size:20px;">✅</span><b>Pembayaran terkirim</b>`
          : `<span style="font-size:20px;">❌</span><b>Pembayaran gagal</b>`;


        // ganti teks
        if(msg) msg.textContent = ok ? 'Menunggu verifikasi admin' : 'Nominal yang dimasukkan tidak sesuai';
        box.appendChild(badge);

        // animasi selesai
        box.animate([
          { transform: 'scale(1)', filter:'brightness(1)' },
          { transform: 'scale(1.02)', filter:'brightness(1.1)' },
          { transform: 'scale(1)', filter:'brightness(1)' }
        ], { duration: 650, easing: 'ease-out' });
      }


function buildActionButtons(ok){
        const actions = document.createElement('div');
        actions.style.display='flex';
        actions.style.justifyContent='center';
        actions.style.gap='10px';
        actions.style.marginTop='14px';

        // Tombol kiri: pergi ke riwayat booking
        const btnRiwayat = document.createElement('button');
        btnRiwayat.type = 'button';
        btnRiwayat.style.padding='12px 16px';
        btnRiwayat.style.borderRadius='16px';
        btnRiwayat.style.border='none';
        btnRiwayat.style.cursor='pointer';
        btnRiwayat.style.fontWeight='1000';
        btnRiwayat.style.background = 'linear-gradient(135deg,#2f8f4d,#43c465)';
        btnRiwayat.style.color='white';
        btnRiwayat.textContent = 'Riwayat Booking';
btnRiwayat.addEventListener('click', function(){
          submitPayment('booking_list');
        });

        actions.appendChild(btnRiwayat);

        // Tombol kanan: OK/Kembali (tetap seperti awal)
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.style.padding='12px 16px';
        btn.style.borderRadius='16px';
        btn.style.border='none';
        btn.style.cursor='pointer';
        btn.style.fontWeight='1000';
        btn.style.background = ok
          ? 'linear-gradient(135deg,#43c465,#2f8f4d)'
          : 'linear-gradient(135deg,#ff5b5b,#cc2f2f)';
        btn.style.color='white';

        btn.textContent = ok ? 'OK' : 'Kembali';

        // submit atau back
        btn.addEventListener('click', function(){
          if(ok){
            submitPayment('dashboard');
          } else {
            submitPayment('dashboard');
          }
        });

        actions.appendChild(btn);
        box.appendChild(actions);
      }

      if(form){
        form.addEventListener('submit', function(e){
          if(!Number.isFinite(expected)) return;
          e.preventDefault();

          const popup = buildPopup();
          document.body.appendChild(popup);

          const nominal = getNominal();
          // sukses jika nominal bayar >= harga sewa (ada kembalian jika lebih)
          const ok = Number.isFinite(expected) && nominal >= expected;

          const waitMs = 1200;
          setTimeout(function(){
            setResult(ok);

            // tampilkan struk digital (nota) setelah animasi selesai
            setTimeout(function(){
              try{
                const lapanganNama = <?php echo json_encode($lapangan); ?>;
                const jadwalT = <?php echo json_encode($jadwal); ?>;
                const hargaText = <?php echo json_encode($harga); ?>;
                const hargaAngka = parseInt(String(hargaText).replace(/[^0-9]/g,''),10) || 0;
                const tanggalBayarT = <?php echo json_encode($tanggal_bayar); ?>;


                const inputNominal = getNominal();
                // untuk kasus gagal: tampilkan kekurangan sebagai nilai minus
                const selisih = inputNominal - hargaAngka;
                const kembali = (inputNominal >= hargaAngka) ? Math.max(0, selisih) : selisih;

                const struk = document.createElement('div');
                struk.style.marginTop = '14px';
                struk.style.padding = '12px';
                struk.style.borderRadius = '16px';
                struk.style.background = '#ffffff';
                struk.style.border = '1px solid rgba(255,255,255,0.10)';
                struk.style.color = '#0b0b0b';

                const fmtRp = (n) => {
                  try{ return new Intl.NumberFormat('id-ID').format(Number(n)); }catch(_e){ return String(n); }
                };

                struk.innerHTML = `
                  <div style="font-weight:1000;color:#9dffb2;margin-bottom:10px; text-align:left;">Struk Pembayaran</div>

                  <div style="font-size:13px; line-height:1.85; color:#0b0b0b;"> 
                    <div style="display:flex; justify-content:space-between; gap:12px;">
                      <div>Lapangan</div>
                      <div><b>${lapanganNama}</b></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; gap:12px;">
                      <div>Jadwal</div>
                      <div style="text-align:right;"><b>${jadwalT}</b></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; gap:12px; margin-top:6px;">
                      <div>Tanggal pembayaran</div>
                      <div><b>${tanggalBayarT}</b></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; gap:12px; margin-top:6px;">
                      <div>Jam pembayaran</div>
                      <div><b>${new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})}</b></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; gap:12px; margin-top:6px;">
                      <div>Harga</div>
                      <div><b>Rp${fmtRp(hargaAngka)}</b></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; gap:12px; margin-top:6px;">
                      <div>Nominal bayar</div>
                      <div><b>Rp${fmtRp(inputNominal)}</b></div>
                    </div>

                    <div style="margin-top:8px; border-top:1px solid rgba(0,0,0,0.12);"></div>

                    <div style="display:flex; justify-content:space-between; gap:12px; margin-top:8px;">
                      <div>Kembalian</div>
                      <div><b>Rp${fmtRp(kembali)}</b></div>
                    </div>

                    <!-- rumus pengurangan dihapus -->


                  </div>
                `;

                box.appendChild(struk);
              }catch(_e){}

              buildActionButtons(ok);
            }, 250);
          }, waitMs);
        });
      }


    })();
  </script>
</body>
</html>

