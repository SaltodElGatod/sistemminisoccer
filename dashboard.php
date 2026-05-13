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

if($id_user !== ''){
    $q = mysqli_query($conn, "SELECT email FROM users WHERE id_user='$id_user' LIMIT 1");

    if($q && mysqli_num_rows($q) > 0){
        $row = mysqli_fetch_assoc($q);
        $email = $row['email'] ?? '';
    }
}

$saldo = $_SESSION['saldo'] ?? '0';

/* =========================
   DATA LAPANGAN
========================= */

$fields = [

[
'name' => 'Arena Deva',
'lat' => 0.7804503646091839,
'lng' => 127.36636049412493,
'image' => 'https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg',

'alamat' => 'Jl. Jati Metro, Kelurahan Jati, Kecamatan Ternate Selatan.',

'harga' => [
['label'=>'07.00 – 15.00','value'=>'Rp500.000'],
['label'=>'19.00 – 21.00','value'=>'Rp700.000'],
],

'operasional' => '07.00 – 00.00',

'fasilitas' => [
'Ruang ganti',
'Bench pemain'
],

'status' => 'Aktif'
],

[
'name' => 'D’ Gadavi',
'lat' => 0.7616757981849658,
'lng' => 127.37112298728341,
'image' => 'https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg',

'alamat' => 'Jl. Raya Kayu Merah, Kelurahan Kayu Merah, Kecamatan Ternate Selatan.',

'harga' => [
['label'=>'Senin – Jumat 07.00 – 12.00','value'=>'Rp400.000'],
['label'=>'Senin – Jumat 19.00 – 00.00','value'=>'Rp650.000'],
['label'=>'Sabtu – Minggu 19.00 – 00.00','value'=>'Rp750.000'],
],

'operasional' => '07.00 – 00.00',

'fasilitas' => [
'Ruang ganti',
'Kamar mandi',
'Bench pemain'
],

'status' => 'Aktif'
],

[
'name' => 'Mini Soccer Kota Baru',
'lat' => 0.7780776937468303,
'lng' => 127.386026786243,
'image' => 'https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg',

'alamat' => 'Jl. Perikanan, Kelurahan Kota Baru, Kecamatan Ternate Tengah.',

'harga' => [
['label'=>'07.00 – 15.00','value'=>'Rp600.000'],
['label'=>'19.00 – 21.00','value'=>'Rp700.000'],
],

'operasional' => '07.00 – 00.00',

'fasilitas' => [
'Ruang ganti'
],

'status' => 'Aktif'
],

[
'name' => 'Desta Mini Soccer',
'lat' => 0.8133902440688916,
'lng' => 127.38083581102575,
'image' => 'https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg',

'alamat' => 'Jl. Darul Khairaat, Kelurahan Sangaji Utara, Kecamatan Ternate Utara.',

'harga' => [
['label'=>'Senin – Jumat 07.00 – 13.00','value'=>'Rp550.000'],
['label'=>'Senin – Jumat 14.00 – 00.00','value'=>'Rp650.000'],
['label'=>'Sabtu – Minggu 07.00 – 13.00','value'=>'Rp650.000'],
['label'=>'Sabtu – Minggu 19.00 – 00.00','value'=>'Rp750.000'],
],

'operasional' => '07.00 – 00.00',

'fasilitas' => [
'Ruang ganti',
'Kamar mandi',
'Musholla',
'Kantin',
'Kantor'
],

'status' => 'Aktif'
],

[
'name' => 'R26 Mini Soccer',
'lat' => 0.8209853655784017,
'lng' => 127.38262459555246,
'image' => 'https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg',

'alamat' => 'Jl. Batu Angus, Kelurahan Akehuda, Kecamatan Ternate Utara.',

'harga' => [
['label'=>'Senin – Kamis 08.00 – 15.00','value'=>'Rp500.000'],
['label'=>'Senin – Kamis 16.00 – 18.00','value'=>'Rp550.000'],
['label'=>'Senin – Kamis 19.00 – 00.00','value'=>'Rp650.000'],
['label'=>'Sabtu – Minggu 08.00 – 15.00','value'=>'Rp600.000'],
['label'=>'Sabtu – Minggu 16.00 – 18.00','value'=>'Rp650.000'],
['label'=>'Sabtu – Minggu 19.00 – 00.00','value'=>'Rp750.000'],
],

'operasional' => '08.00 – 00.00',

'fasilitas' => [
'Bench pemain',
'Ruang ganti',
'Musholla',
'Kamar mandi',
'Tribun'
],

'status' => 'Aktif'
],

[
'name' => 'Melanesia Sport',
'lat' => 0.7589283387074472,
'lng' => 127.33406523788118,
'image' => 'https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg',

'alamat' => 'Jl. Taman Papua, Kelurahan Gambesi, Kecamatan Ternate Selatan.',

'harga' => [
['label'=>'Senin – Jumat 07.00 – 14.00','value'=>'Rp500.000'],
['label'=>'Senin – Jumat 15.00 – 00.00','value'=>'Rp550.000'],
['label'=>'Sabtu – Minggu 07.00 – 14.00','value'=>'Rp550.000'],
['label'=>'Sabtu – Minggu 15.00 – 00.00','value'=>'Rp600.000'],
],

'operasional' => '08.00 – 00.00',

'fasilitas' => [
'Kamar mandi',
'Ruang ganti',
'Musholla'
],

'status' => 'Aktif'
]

];

?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard FootballZone</title>

<link rel="stylesheet" href="assets/page-transition.css">
<script defer src="assets/page-transition.js"></script>

<link rel="stylesheet" href="assets/dashboard.css">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</head>

<body>

<div id="page" class="page">

<div class="topbar">

<div class="topbar-left">
<h1>⚽ FootballZone</h1>
<p>Peta Lapangan Futsal – Maluku Utara (Ternate)</p>
</div>



</div>

<div class="layout">

<!-- LEFT PANEL -->
<aside class="left-panel">


<div class="left-panel__section">

<div class="left-dd-title" style="margin-bottom:10px;">🏟️ Lapangan</div>

<div class="left-panel__dropdown" id="lapanganDropdown" aria-hidden="false" style="opacity:1;height:auto;">
  <div class="booking-hint" style="margin-bottom:8px;">Pilih salah satu nama untuk diarahkan ke marker peta.</div>
  <div class="lapangan-list" id="lapanganList"></div>
</div>

</div>

<div class="left-panel__quick">





<a class="quick-item" href="profil.php" style="text-decoration:none;">
  <div class="quick-item__title">👤 Profil</div>
  <div class="quick-item__desc">Lihat & edit akun</div>
</a>

</div>

<div class="selected-meta">

<div class="selected-meta__label">
Terpilih:
</div>

<div class="selected-meta__value" id="selectedName">
-
</div>

</div>

</aside>

<!-- MAP -->
<main class="center-map">
<div class="map-frame" id="map"></div>
</main>

<!-- RIGHT PANEL -->
<section class="right-panel">

<div class="card">

<div class="card__img-wrap">
<img
id="detailImage"
class="card__img"
src="https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg"
alt="Lapangan">
</div>

<div class="card__body">

<div class="card__title" id="detailTitle">
Detail Lapangan
</div>

<div class="card__text" id="detailText">
Klik marker di peta untuk melihat detail lapangan.
</div>

<div class="card__actions">
<button class="btn" id="btnBooking" disabled>
Booking
</button>
</div>

</div>

</div>

<div class="right-note">
Titik pusat peta: <b>Ternate</b> – Maluku Utara.
</div>

</section>

</div>

</div>

<!-- MODAL JADWAL -->
<div class="modal" id="modalJadwal" aria-hidden="true">
    <div class="modal__backdrop" data-close="true" style="position:absolute;inset:0"></div>
    <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modalJadwalTitle">
        <div class="modal__title" id="modalJadwalTitle">Pilih Jadwal</div>
        <div class="modal__text">Pilih salah satu jam pemesanan untuk lapangan <b id="modalLapanganName">-</b>.</div>
        <div class="form-grid">
            <div class="field">
                <label style="display:block;margin-bottom:6px;font-weight:900;color:rgba(255,255,255,0.7);font-size:12.5px;">Daftar Jadwal</label>
                <div id="jadwalList" style="display:flex;flex-direction:column;gap:10px;" ></div>
            </div>
        </div>
        <div class="modal__actions">
            <button type="button" class="btn btn--ghost" data-close="true">Cancel</button>
            <button type="button" class="btn btn--primary" id="btnLanjutJadwal">Lanjut</button>
        </div>
    </div>
</div>

<script>
const modalJadwal = document.getElementById('modalJadwal');
    const modalLapanganName = document.getElementById('modalLapanganName');

    function closeModalJadwal(){
        if(!modalJadwal) return;
        modalJadwal.classList.remove('is-open');
        modalJadwal.setAttribute('aria-hidden','true');
    }

    document.querySelectorAll('#modalJadwal [data-close="true"]').forEach(el => {
        el.addEventListener('click', closeModalJadwal);
    });

    modalJadwal && modalJadwal.addEventListener('click', (e)=>{
        if(e.target && e.target.getAttribute('data-close') === 'true') closeModalJadwal();
    });

    const btnLanjutJadwal = document.getElementById('btnLanjutJadwal');
    btnLanjutJadwal && btnLanjutJadwal.addEventListener('click', () => {
        const selected = window.__selectedField;
        if(!selected) return;

        const radio = document.querySelector('#jadwalList input[name="slot"]:checked');
        if(!radio) { alert('Pilih jadwal terlebih dahulu'); return; }
        const idx = Number(radio.value);
        if(window.__slotAvailability && window.__slotAvailability[idx] === false){
            alert('Jadwal ini sudah dibooking');
            return;
        }
        const slot = selected.harga[idx];

        // map id_lapangan & id_user via DB tabel, supaya checkout bisa mengunci relasi
        const lap = selected.name;

        // Biarkan server resolve id_lapangan dari nama lapangan agar tidak salah jika ID database berubah.
        const idLap = '';

        const url = `booking_checkout.php?lapangan=${encodeURIComponent(lap)}&jadwal=${encodeURIComponent(slot.label)}&harga=${encodeURIComponent(slot.value)}&from=dashboard&id_lapangan=${encodeURIComponent(idLap)}`;
        console.log('Redirect to', url);
        window.location = url;




    });
</script>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">

<a href="booking_list.php?from_dashboard=1" class="bottom-nav__item" aria-label="Riwayat">
<span class="bottom-nav__icon">📋</span>
<span class="bottom-nav__text">Riwayat</span>
</a>




<a href="#" class="bottom-nav__item bottom-nav__item--active">
<span class="bottom-nav__icon">🗺️</span>
<span class="bottom-nav__text">Peta</span>
</a>

<a href="profil.php" class="bottom-nav__item" aria-label="Profil">
<span class="bottom-nav__icon">👤</span>
<span class="bottom-nav__text">Profil</span>
</a>

</nav>

<script>

const fields = <?php echo json_encode($fields, JSON_UNESCAPED_UNICODE); ?>;

// =========================
// LAPANGAN DROPDOWN (menu)
// =========================
const btnLapangan = document.getElementById('btnLapangan');
const lapanganDropdown = document.getElementById('lapanganDropdown');
const lapanganList = document.getElementById('lapanganList');

function toggleLapanganDropdown(){
  if(!lapanganDropdown) return;
  const isOpen = lapanganDropdown.getAttribute('aria-hidden') === 'false';
  lapanganDropdown.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
}

btnLapangan && btnLapangan.addEventListener('click', (e) => {
  e.preventDefault();
  toggleLapanganDropdown();
});

if(lapanganList){
  lapanganList.innerHTML = '';
  fields.forEach(field => {
    const item = document.createElement('div');
    item.className = 'lapangan-item';
    item.style.cssText = 'padding:10px 12px; border-radius:16px; border:1px solid rgba(255,255,255,0.07); background:rgba(255,255,255,0.03); color:#e8ffe8; font-weight:900; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px;';
    item.textContent = field.name;
    item.title = field.alamat;

    item.addEventListener('click', () => {
      // arahkan peta ke marker
      setDetail(field);
      try{
        // zoom ke marker
        L.map('map');
      }catch(_){ }

      // karena map dibuat di bawah, simpan reference via window
      if(window.__dashboardMap && window.__dashboardMap.setView){
        window.__dashboardMap.setView([Number(field.lat), Number(field.lng)], 14, { animate: true });
      }

      // tutup dropdown
      lapanganDropdown && lapanganDropdown.setAttribute('aria-hidden','true');
    });

    lapanganList.appendChild(item);
  });
}

const detailTitle = document.getElementById('detailTitle');

const detailText = document.getElementById('detailText');
const detailImage = document.getElementById('detailImage');
const selectedName = document.getElementById('selectedName');
const btnBooking = document.getElementById('btnBooking');

/* =========================
   SET DETAIL
========================= */

function setDetail(field){

    // simpan untuk dipakai saat booking
    window.__selectedField = field;




detailTitle.textContent = field.name;

detailImage.src = field.image;

selectedName.textContent = field.name;

btnBooking.disabled = false;

/* Harga */
let hargaHTML = '';

field.harga.forEach(item => {

hargaHTML += `
<div style="margin-bottom:8px;">
• ${item.label}<br>
<b>${item.value}</b>
</div>
`;

});

/* Fasilitas */
let fasilitasHTML = '';

field.fasilitas.forEach(item => {
fasilitasHTML += `<li>${item}</li>`;
});

/* FINAL HTML */
detailText.innerHTML = `

<div class="detail-subtitle">
⚽ Rincian Lapangan
</div>

<div class="detail-line">
<div class="detail-emoji">📍</div>

<div class="detail-text">
<div style="font-weight:900;color:#9dffb2;margin-bottom:4px;">
Alamat
</div>

${field.alamat}
</div>
</div>

<div class="detail-line">
<div class="detail-emoji">💰</div>

<div class="detail-text">

<div style="font-weight:900;color:#9dffb2;margin-bottom:4px;">
Harga Sewa
</div>

${hargaHTML}

</div>
</div>

<div class="detail-line">

<div class="detail-emoji">🕒</div>

<div class="detail-text">

<div style="font-weight:900;color:#9dffb2;margin-bottom:4px;">
Jam Operasional
</div>

${field.operasional}

</div>

</div>

<div class="detail-line">

<div class="detail-emoji">🧩</div>

<div class="detail-text">

<div style="font-weight:900;color:#9dffb2;margin-bottom:4px;">
Fasilitas
</div>

<ul style="padding-left:18px;">
${fasilitasHTML}
</ul>

</div>

</div>

<div class="detail-tag">
Status: ${field.status}
</div>

`;

}

/* =========================
   MAP
========================= */

const centerLatLng = [
0.8098933214735948,
127.33741541279758
];

const map = L.map('map').setView(centerLatLng, 12);
window.__dashboardMap = map;


L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
maxZoom:19,
attribution:'&copy; OpenStreetMap contributors'
}
).addTo(map);

/* Marker */

fields.forEach(field => {

const marker = L.marker([
Number(field.lat),
Number(field.lng)
]).addTo(map);

marker.bindPopup(`<b>${field.name}</b>`);

marker.on('click', () => {
setDetail(field);
});

});

/* Booking */

btnBooking.addEventListener('click', () => {
    console.log('btnBooking clicked', window.__selectedField);
    const selected = window.__selectedField;

    if(!selected){
        alert('Pilih dulu marker lapangan di peta');
        return;
    }


    // Buka modal jadwal
    const modal = document.getElementById('modalJadwal');
    if(!modal){
        alert('Modal jadwal tidak ditemukan');
        return;
    }
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden','false');

    const modalLap = document.getElementById('modalLapanganName');
    if(modalLap) modalLap.textContent = selected.name;

    // render slot
    const list = document.getElementById('jadwalList');
    if(!list){ alert('jadwalList tidak ditemukan'); return; }
    list.innerHTML = '';

    // ambil status slot yang sudah dibooking dari endpoint booking_slots.php
    // supaya Update (slot sudah terbooking) langsung terhubung ke UI.
    const selectedName = selected.name;

    fetch(`booking_slots.php?lapangan=${encodeURIComponent(selectedName)}`)
      .then(r => r.json())
      .then(data => {
        const bookedMap = {};
        (data.slots || []).forEach(s => { bookedMap[s.jam] = !!s.available === false; });
        window.__slotAvailability = {};

        selected.harga.forEach((slot, idx) => {
          // kita tidak punya jam key yang sama, jadi pakai label sebagai proxy
          const isBooked = (data.slots || []).some(s => s.label === slot.label && s.available === false);
          const div = document.createElement('div');
          div.className = 'slot-item' + (isBooked ? ' slot-item--booked' : '');
          window.__slotAvailability[idx] = !isBooked;

          div.innerHTML = `
            <label style="display:flex;align-items:center;gap:10px;cursor:${isBooked ? 'not-allowed' : 'pointer'};opacity:${isBooked ? '0.55' : '1'};">
              <input type="radio" name="slot" value="${idx}" ${isBooked ? 'disabled' : ''} required />
              <div>
                <div style="font-weight:1000;color:#9dffb2;">${slot.label}</div>
                <div style="font-weight:1000; color:white;">${slot.value}</div>
                ${isBooked ? '<div style="margin-top:4px;color:#ff9b9b;font-weight:1000;font-size:12.5px;">Jadwal ini sudah dibooking</div>' : ''}
              </div>
            </label>
          `;
          if(isBooked){
            div.addEventListener('click', function(){
              alert('Jadwal ini sudah dibooking');
            });
          }
          list.appendChild(div);
        });
      })
      .catch(() => {
        // fallback: tanpa disable slot
        window.__slotAvailability = {};
        selected.harga.forEach((slot, idx) => {
          window.__slotAvailability[idx] = true;
          const div = document.createElement('div');
          div.className = 'slot-item';
          div.innerHTML = `
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
              <input type="radio" name="slot" value="${idx}" required />
              <div>
                <div style="font-weight:1000;color:#9dffb2;">${slot.label}</div>
                <div style="font-weight:1000; color:white;">${slot.value}</div>
              </div>
            </label>
          `;
          list.appendChild(div);
        });
      });
});

</script>

<style>
    /* reuse modal minimal untuk dashboard */
    .modal{ position:fixed; inset:0; display:none; z-index:9999; }
    .modal.is-open{ display:block; }
    .modal__backdrop{ position:absolute; inset:0; background:rgba(0,0,0,0.6); }
    .modal__dialog{ position:relative; width:min(520px,92vw); margin:8vh auto 0 auto; background:rgba(7,22,14,0.98); border:1px solid rgba(67,196,101,0.25); border-radius:24px; padding:18px; box-shadow:0 18px 70px rgba(0,0,0,0.55); }
    .modal__title{ color:#9dffb2; font-weight:1000; font-size:18px; margin-bottom:8px; }
    .modal__text{ color:rgba(255,255,255,0.75); font-size:13.5px; margin-bottom:14px; line-height:1.5; }
    .modal__actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:14px; }
    .btn--ghost{ background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.10); color:#e8ffe8; }
    .btn--primary{ background:linear-gradient(135deg,#43c465,#2f8f4d); color:white; }
    .slot-item{ padding:12px; border-radius:16px; border:1px solid rgba(255,255,255,0.10); background:rgba(255,255,255,0.04); }
    .slot-item--booked{ border-color:rgba(255,91,91,0.35); background:rgba(255,91,91,0.10); }
    .slot-item:hover{ filter:brightness(1.05); transform:translateY(-1px); }
</style>

</body>
</html>
