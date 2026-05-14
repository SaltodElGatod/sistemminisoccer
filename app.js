const FIELD_IMAGE = "https://mertajayamandiri.id/wp-content/uploads/2023/01/merta-file.jpg";
const DEFAULT_USER = { id: "demo", nama: "User Demo", email: "user@futsal.id", password: "123456", saldo: 0, photo: "man1.png" };
const ADMINS = { "admin@futsal.id": "futsalhebat123", "admin1@futsal.id": "futsalhebat321" };
const FIELDS = [
  { name:"Arena Deva",lat:.7804503646091839,lng:127.36636049412493,image:FIELD_IMAGE,alamat:"Jl. Jati Metro, Kelurahan Jati, Kecamatan Ternate Selatan.",harga:[["07.00 - 15.00","Rp500.000"],["19.00 - 21.00","Rp700.000"]],operasional:"07.00 - 00.00",fasilitas:["Ruang ganti","Bench pemain"],status:"Aktif" },
  { name:"D' Gadavi",lat:.7616757981849658,lng:127.37112298728341,image:FIELD_IMAGE,alamat:"Jl. Raya Kayu Merah, Kelurahan Kayu Merah, Kecamatan Ternate Selatan.",harga:[["Senin - Jumat 07.00 - 12.00","Rp400.000"],["Senin - Jumat 19.00 - 00.00","Rp650.000"],["Sabtu - Minggu 19.00 - 00.00","Rp750.000"]],operasional:"07.00 - 00.00",fasilitas:["Ruang ganti","Kamar mandi","Bench pemain"],status:"Aktif" },
  { name:"Mini Soccer Kota Baru",lat:.7780776937468303,lng:127.386026786243,image:FIELD_IMAGE,alamat:"Jl. Perikanan, Kelurahan Kota Baru, Kecamatan Ternate Tengah.",harga:[["07.00 - 15.00","Rp600.000"],["19.00 - 21.00","Rp700.000"]],operasional:"07.00 - 00.00",fasilitas:["Ruang ganti"],status:"Aktif" },
  { name:"Desta Mini Soccer",lat:.8133902440688916,lng:127.38083581102575,image:FIELD_IMAGE,alamat:"Jl. Darul Khairaat, Kelurahan Sangaji Utara, Kecamatan Ternate Utara.",harga:[["Senin - Jumat 07.00 - 13.00","Rp550.000"],["Senin - Jumat 14.00 - 00.00","Rp650.000"],["Sabtu - Minggu 07.00 - 13.00","Rp650.000"],["Sabtu - Minggu 19.00 - 00.00","Rp750.000"]],operasional:"07.00 - 00.00",fasilitas:["Ruang ganti","Kamar mandi","Musholla","Kantin","Kantor"],status:"Aktif" },
  { name:"R26 Mini Soccer",lat:.8209853655784017,lng:127.38262459555246,image:FIELD_IMAGE,alamat:"Jl. Batu Angus, Kelurahan Akehuda, Kecamatan Ternate Utara.",harga:[["Senin - Kamis 08.00 - 15.00","Rp500.000"],["Senin - Kamis 16.00 - 18.00","Rp550.000"],["Senin - Kamis 19.00 - 00.00","Rp650.000"],["Sabtu - Minggu 08.00 - 15.00","Rp600.000"],["Sabtu - Minggu 16.00 - 18.00","Rp650.000"],["Sabtu - Minggu 19.00 - 00.00","Rp750.000"]],operasional:"08.00 - 00.00",fasilitas:["Bench pemain","Ruang ganti","Musholla","Kamar mandi","Tribun"],status:"Aktif" },
  { name:"Melanesia Sport",lat:.7589283387074472,lng:127.33406523788118,image:FIELD_IMAGE,alamat:"Jl. Taman Papua, Kelurahan Gambesi, Kecamatan Ternate Selatan.",harga:[["Senin - Jumat 07.00 - 14.00","Rp500.000"],["Senin - Jumat 15.00 - 00.00","Rp550.000"],["Sabtu - Minggu 07.00 - 14.00","Rp550.000"],["Sabtu - Minggu 15.00 - 00.00","Rp600.000"]],operasional:"08.00 - 00.00",fasilitas:["Kamar mandi","Ruang ganti","Musholla"],status:"Aktif" }
];

const $ = (sel) => document.querySelector(sel);
const store = {
  get users(){ return JSON.parse(localStorage.getItem("fz_users") || "null") || [DEFAULT_USER]; },
  set users(v){ localStorage.setItem("fz_users", JSON.stringify(v)); },
  get current(){ return JSON.parse(localStorage.getItem("fz_current") || "null"); },
  set current(v){ v ? localStorage.setItem("fz_current", JSON.stringify(v)) : localStorage.removeItem("fz_current"); },
  get bookings(){ return JSON.parse(localStorage.getItem("fz_bookings") || "[]"); },
  set bookings(v){ localStorage.setItem("fz_bookings", JSON.stringify(v)); },
  get admin(){ return localStorage.getItem("fz_admin") || ""; },
  set admin(v){ v ? localStorage.setItem("fz_admin", v) : localStorage.removeItem("fz_admin"); }
};

function rupiahNumber(text){ return parseInt(String(text).replace(/[^0-9]/g,""),10) || 0; }
function fmtRp(n){ return "Rp" + new Intl.NumberFormat("id-ID").format(Number(n) || 0); }
function today(){ return new Date().toISOString().slice(0,10); }
function timeNow(){ return new Date().toLocaleTimeString("id-ID",{hour:"2-digit",minute:"2-digit"}); }
function go(route){ location.hash = route; }
function requireUser(){ if(!store.current){ go("/login"); return false; } return true; }
function requireAdmin(){ if(!store.admin){ go("/login"); return false; } return true; }

function render(){
  const route = location.hash.replace(/^#/,"") || "/login";
  if(route === "/login") return loginPage();
  if(route === "/register") return registerPage();
  if(route === "/dashboard") return requireUser() && dashboardPage();
  if(route === "/checkout") return requireUser() && checkoutPage();
  if(route === "/history") return requireUser() && historyPage();
  if(route === "/profile") return requireUser() && profilePage();
  if(route === "/admin") return requireAdmin() && adminPage();
  return go("/login");
}

function authShell(mode){
  const isRegister = mode === "register";
  $("#app").innerHTML = `
    <main class="auth-page"><section class="auth-card">
      <div class="auth-left"><h1>FootballZone</h1><p>${isRegister ? "Gabung bersama komunitas pecinta bola dan booking lapangan favoritmu dengan suasana cozy modern." : "Login dan nikmati vibes stadion malam yang cozy dan modern."}</p></div>
      <div class="auth-right">
        <img class="football" src="https://cdn-icons-png.flaticon.com/512/53/53283.png" alt="">
        <h2>${isRegister ? "Daftar Akun" : "Login Akun"}</h2>
        <form id="authForm">
          ${isRegister ? '<div class="input-box"><label>Nama Lengkap</label><input name="nama" required></div>' : ""}
          <div class="input-box"><label>Email</label><input type="email" name="email" required></div>
          <div class="input-box"><label>Password</label><input type="password" name="password" required></div>
          <button class="btn btn-primary full">${isRegister ? "Daftar Sekarang" : "Masuk Sekarang"}</button>
        </form>
        ${isRegister ? '<div class="bottom">Sudah punya akun? <a href="#/login">Login</a></div>' : '<div class="link"><a href="#/login" onclick="alert(\'Demo statis: gunakan register ulang atau user@futsal.id / 123456\')">Lupa Password?</a></div><div class="bottom">Belum punya akun? <a href="#/register">Daftar</a></div>'}
      </div>
    </section></main>
    <div class="modal" id="adminModal"><div class="modal-box"><div class="modal-title">Login Admin</div><form id="adminForm"><div class="input-box"><label>Email Admin</label><input type="email" name="email" required></div><div class="input-box"><label>Password Admin</label><input type="password" name="password" required></div><div class="modal-actions"><button type="button" class="btn btn-ghost" data-close>Batal</button><button class="btn btn-primary">Masuk Admin</button></div></form></div></div>`;
}
function loginPage(){
  authShell("login");
  $("#authForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    const user = store.users.find(u => u.email === data.email && u.password === data.password);
    if(!user) return alert("Email atau Password salah");
    store.current = { id:user.id, email:user.email };
    alert("Login berhasil");
    go("/dashboard");
  });
  let clicks = 0, timer = null;
  $(".football").addEventListener("click", () => {
    clicks++; clearTimeout(timer); timer = setTimeout(() => clicks = 0, 1400);
    if(clicks >= 5){ clicks = 0; $("#adminModal").classList.add("open"); }
  });
  $("[data-close]").addEventListener("click", () => $("#adminModal").classList.remove("open"));
  $("#adminForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    if(ADMINS[data.email] !== data.password) return alert("Email atau password admin salah");
    store.admin = data.email;
    alert("Login admin berhasil");
    go("/admin");
  });
}
function registerPage(){
  authShell("register");
  $("#authForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    const users = store.users;
    if(users.some(u => u.email === data.email)) return alert("Email sudah digunakan");
    users.push({ id: crypto.randomUUID(), nama:data.nama, email:data.email, password:data.password, saldo:0, photo:"man1.png" });
    store.users = users;
    alert("Registrasi berhasil");
    go("/login");
  });
}
function currentUser(){
  const cur = store.current;
  return store.users.find(u => u.id === cur?.id || u.email === cur?.email) || DEFAULT_USER;
}
function appTop(title, sub){
  return `<div class="topbar"><div><h1>${title}</h1><p>${sub}</p></div></div>`;
}
function bottomNav(active){
  return `<nav class="bottom-nav"><a class="${active==="history"?"active":""}" href="#/history"><span>Riwayat</span></a><a class="${active==="map"?"active":""}" href="#/dashboard"><span>Peta</span></a><a class="${active==="profile"?"active":""}" href="#/profile"><span>Profil</span></a></nav>`;
}
function dashboardPage(){
  $("#app").innerHTML = `${appTop("FootballZone","Peta Lapangan Futsal - Maluku Utara (Ternate)")}
  <div class="layout">
    <aside class="panel left-panel"><div class="section-title">Lapangan</div><p class="muted" style="margin-bottom:10px">Pilih salah satu nama untuk diarahkan ke marker peta.</p><div class="lapangan-list">${FIELDS.map((f,i)=>`<button class="lapangan-item" data-field="${i}">${f.name}</button>`).join("")}</div><a class="soft-box" style="display:block;margin-top:14px" href="#/profile"><b style="color:#9dffb2">Profil</b><br><span class="muted">Lihat & edit akun</span></a><div class="selected-meta"><div>Terpilih:</div><strong id="selectedName">-</strong></div></aside>
    <main class="panel center-map"><div id="map"></div></main>
    <section class="panel right-panel" id="detailPanel">${fieldDetail()}</section>
  </div>${bottomNav("map")}
  <div class="modal" id="slotModal"><div class="modal-box"><div class="modal-title">Pilih Jadwal</div><p class="muted">Pilih salah satu jam pemesanan untuk lapangan <b id="modalField">-</b>.</p><div id="slotList" style="display:flex;flex-direction:column;gap:10px;margin-top:12px"></div><div class="modal-actions"><button class="btn btn-ghost" data-close>Batal</button><button class="btn btn-primary" id="continueSlot">Lanjut</button></div></div></div>`;
  let selected = null;
  const map = L.map("map").setView([0.8098933214735948,127.33741541279758], 12);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:19,attribution:"&copy; OpenStreetMap contributors"}).addTo(map);
  const choose = (field) => {
    selected = field;
    $("#selectedName").textContent = field.name;
    $("#detailPanel").innerHTML = fieldDetail(field);
    $("#bookBtn").addEventListener("click", openSlots);
    map.setView([field.lat, field.lng], 14, { animate:true });
  };
  FIELDS.forEach((field) => L.marker([field.lat,field.lng]).addTo(map).bindPopup(`<b>${field.name}</b>`).on("click", () => choose(field)));
  document.querySelectorAll("[data-field]").forEach(btn => btn.addEventListener("click", () => choose(FIELDS[Number(btn.dataset.field)])));
  $("#detailPanel").addEventListener("click", (e) => { if(e.target.id === "bookBtn") openSlots(); });
  $("[data-close]").addEventListener("click", () => $("#slotModal").classList.remove("open"));
  function openSlots(){
    if(!selected) return alert("Pilih dulu marker lapangan di peta");
    $("#modalField").textContent = selected.name;
    const booked = store.bookings.filter(b => b.lapangan === selected.name && b.status !== "dibatalkan");
    $("#slotList").innerHTML = selected.harga.map((slot,idx) => {
      const taken = booked.some(b => b.jadwal === slot[0] && b.status !== "gagal");
      return `<label class="slot-item ${taken ? "slot-booked" : ""}"><input type="radio" name="slot" value="${idx}" ${taken ? "disabled" : ""}> <b style="color:#9dffb2">${slot[0]}</b><br><strong>${slot[1]}</strong>${taken ? '<div style="color:#ff9b9b;font-weight:1000;margin-top:4px">Jadwal ini sudah dibooking</div>' : ""}</label>`;
    }).join("");
    $("#slotModal").classList.add("open");
  }
  $("#continueSlot").addEventListener("click", () => {
    const checked = document.querySelector('input[name="slot"]:checked');
    if(!checked) return alert("Pilih jadwal terlebih dahulu");
    sessionStorage.setItem("fz_checkout", JSON.stringify({ lapangan:selected.name, jadwal:selected.harga[checked.value][0], harga:selected.harga[checked.value][1] }));
    go("/checkout");
  });
}
function fieldDetail(field){
  if(!field) return `<div class="field-card"><img src="${FIELD_IMAGE}" alt=""><div class="field-card-body"><h2>Detail Lapangan</h2><p class="muted">Klik marker di peta untuk melihat detail lapangan.</p><button class="btn btn-primary" disabled>Booking</button></div></div><p class="muted" style="margin-top:12px">Titik pusat peta: <b>Ternate</b> - Maluku Utara.</p>`;
  return `<div class="field-card"><img src="${field.image}" alt=""><div class="field-card-body"><h2>${field.name}</h2><div class="detail-line"><span>Lokasi</span><div><b>Alamat</b><br>${field.alamat}</div></div><div class="detail-line"><span>Harga</span><div><b>Harga Sewa</b><br>${field.harga.map(h=>`${h[0]}<br><strong>${h[1]}</strong>`).join("<br>")}</div></div><div class="detail-line"><span>Jam</span><div><b>Jam Operasional</b><br>${field.operasional}</div></div><div class="detail-line"><span>Fasilitas</span><div><b>Fasilitas</b><br>${field.fasilitas.join(", ")}</div></div><span class="tag">Status: ${field.status}</span><button class="btn btn-primary" id="bookBtn">Booking</button></div></div>`;
}
function checkoutPage(){
  const data = JSON.parse(sessionStorage.getItem("fz_checkout") || "null");
  if(!data) return go("/dashboard");
  const user = currentUser();
  $("#app").innerHTML = `<main class="auth-page"><section class="modal-box" style="width:min(720px,95vw)"><div class="modal-title">Isi Data Booking & Pembayaran</div><p class="muted">Lakukan pembayaran untuk mengonfirmasi booking jadwal lapangan yang dipilih.</p><div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin:14px 0"><div class="soft-box"><span class="muted">Username</span><br><b>${user.nama}</b></div><div class="soft-box"><span class="muted">Lapangan</span><br><b>${data.lapangan}</b></div><div class="soft-box"><span class="muted">Jadwal</span><br><b>${data.jadwal}</b></div><div class="soft-box"><span class="muted">Harga</span><br><b>${data.harga}</b></div></div><form id="payForm"><div class="soft-box"><b style="color:#9dffb2">Pembayaran</b><p class="muted" style="margin:8px 0">Masukkan nominal pembayaran. Jika nominal sesuai harga sewa, booking akan masuk status menunggu verifikasi admin.</p><div class="field"><label>Nominal Pembayaran</label><input name="nominal" placeholder="contoh: ${data.harga}" required></div></div><div class="modal-actions"><a class="btn btn-ghost" href="#/dashboard">Cancel</a><button class="btn btn-primary">Bayar Sekarang</button></div></form></section></main><div class="modal" id="payModal"><div class="modal-box" id="payBox"><div class="modal-title">Memproses pembayaran...</div><p class="muted">Tunggu sebentar</p></div></div>`;
  $("#payForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const nominal = rupiahNumber(new FormData(e.target).get("nominal"));
    const harga = rupiahNumber(data.harga);
    const ok = nominal >= harga;
    $("#payModal").classList.add("open");
    setTimeout(() => {
      const booking = { id: crypto.randomUUID(), userId:user.id, userName:user.nama, userEmail:user.email, lapangan:data.lapangan, jadwal:data.jadwal, harga, nominal, kembalian: nominal - harga, tanggal:today(), jamBayar:timeNow(), status: ok ? "menunggu" : "gagal" };
      store.bookings = [booking, ...store.bookings];
      $("#payBox").innerHTML = `<div class="modal-title">${ok ? "Pembayaran terkirim" : "Pembayaran gagal"}</div><p class="muted">${ok ? "Menunggu verifikasi admin" : "Nominal yang dimasukkan tidak sesuai"}</p><div class="soft-box" style="background:white;color:#111;margin-top:14px"><b style="color:#2f8f4d">Struk Pembayaran</b><br>Lapangan: <b>${data.lapangan}</b><br>Jadwal: <b>${data.jadwal}</b><br>Tanggal pembayaran: <b>${today()}</b><br>Jam pembayaran: <b>${booking.jamBayar}</b><br>Harga: <b>${fmtRp(harga)}</b><br>Nominal bayar: <b>${fmtRp(nominal)}</b><br>Kembalian: <b>${fmtRp(booking.kembalian)}</b></div><div class="modal-actions"><a class="btn btn-primary" href="#/history">Riwayat Booking</a><a class="btn btn-ghost" href="#/dashboard">OK</a></div>`;
    }, 900);
  });
}
function historyPage(){
  const user = currentUser();
  const rows = store.bookings.filter(b => b.userId === user.id);
  $("#app").innerHTML = `<main class="page-wrap"><div class="page-head"><div><h1 style="color:#9dffb2">Riwayat Booking</h1><p class="muted">Halo, ${user.nama}</p></div><a href="#/dashboard">Kembali ke Dashboard</a></div><div class="notice"><b>Update:</b> Saat booking sudah dibayar, status akan berubah sehingga slot muncul "Sudah dibooking" di popup.</div><div class="table-card"><table><thead><tr><th>Tanggal Pembayaran</th><th>Jam Pembayaran</th><th>Lapangan</th><th>Harga</th><th>Nominal Bayar</th><th>Kembalian</th><th>Status</th><th>Aksi</th></tr></thead><tbody>${rows.length ? rows.map(b => `<tr><td>${b.tanggal}</td><td>${b.jamBayar}</td><td><b>${b.lapangan}</b></td><td>${fmtRp(b.harga)}</td><td>${fmtRp(b.nominal)}</td><td>${fmtRp(b.kembalian)}</td><td><span class="tag">${b.status}</span></td><td><button class="btn btn-danger" data-del="${b.id}">Hapus</button></td></tr>`).join("") : '<tr><td colspan="8" class="muted">Belum ada booking.</td></tr>'}</tbody></table></div></main>${bottomNav("history")}`;
  document.querySelectorAll("[data-del]").forEach(btn => btn.addEventListener("click", () => { if(confirm("Hapus riwayat ini?")){ store.bookings = store.bookings.filter(b => b.id !== btn.dataset.del); historyPage(); } }));
}
function profilePage(){
  const user = currentUser();
  $("#app").innerHTML = `<main class="profile-page"><section class="profile-card"><img class="avatar" src="assets/${user.photo || "man1.png"}" alt=""><div class="profile-name">${user.nama}</div><div class="muted">${user.email}</div><div class="tag" style="margin-top:10px">Saldo: ${fmtRp(user.saldo)}</div><div class="profile-actions"><button class="btn btn-ghost" id="editProfile">Edit Profil</button><button class="btn btn-ghost" disabled>Top Up Saldo</button><button class="btn btn-danger" id="logout">Logout</button><a class="btn btn-ghost" href="#/dashboard">Kembali</a></div></section></main>${bottomNav("profile")}<div class="modal" id="profileModal"><div class="modal-box"><div class="modal-title">Edit Profil</div><form id="profileForm"><div class="field"><label>Username</label><input name="nama" value="${user.nama}" required></div><div class="field"><label>Email</label><input type="email" name="email" value="${user.email}" required></div><label style="display:block;margin:12px 0 8px;color:#d8ffd8;font-weight:900">Pilih Gambar Profil</label><div class="photo-grid">${["man1.png","man2.png","man3.png","women1.png","women2.png","women3.png"].map(p=>`<label class="photo-option ${user.photo===p?"active":""}"><input type="radio" name="photo" value="${p}" ${user.photo===p?"checked":""} hidden><img src="assets/${p}" alt=""></label>`).join("")}</div><div class="modal-actions"><button type="button" class="btn btn-ghost" data-close>Batal</button><button class="btn btn-primary">Simpan</button></div></form></div></div>`;
  $("#logout").addEventListener("click", () => { store.current = null; go("/login"); });
  $("#editProfile").addEventListener("click", () => $("#profileModal").classList.add("open"));
  $("[data-close]").addEventListener("click", () => $("#profileModal").classList.remove("open"));
  document.querySelectorAll(".photo-option").forEach(label => label.addEventListener("click", () => { document.querySelectorAll(".photo-option").forEach(x => x.classList.remove("active")); label.classList.add("active"); }));
  $("#profileForm").addEventListener("submit", (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    const users = store.users.map(u => u.id === user.id ? { ...u, nama:data.nama, email:data.email, photo:data.photo || u.photo } : u);
    store.users = users;
    profilePage();
  });
}
function adminPage(){
  const bookings = store.bookings;
  const sukses = bookings.filter(b => b.status === "sukses");
  const pending = bookings.filter(b => b.status === "menunggu");
  const income = sukses.reduce((sum,b) => sum + b.nominal, 0);
  $("#app").innerHTML = `<main class="page-wrap"><div class="page-head"><div><h1 style="color:#9dffb2">Admin FootballZone</h1><p class="muted">Login sebagai ${store.admin}</p></div><button class="btn btn-ghost" id="adminLogout">Logout</button></div><div class="admin-grid"><div class="panel admin-card"><div>Total Lapangan</div><strong>${FIELDS.length}</strong></div><div class="panel admin-card"><div>Booking Hari Ini</div><strong>${bookings.filter(b=>b.tanggal===today()).length}</strong></div><div class="panel admin-card"><div>Pendapatan</div><strong>${fmtRp(income)}</strong></div><div class="panel admin-card"><div>Booking Menunggu</div><strong>${pending.length}</strong></div></div><section class="panel" style="margin-bottom:14px"><h2 style="color:#9dffb2;margin-bottom:12px">Verifikasi Pembayaran</h2><div class="table-card"><table><thead><tr><th>User</th><th>Lapangan</th><th>Jadwal</th><th>Nominal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>${pending.length ? pending.map(b=>`<tr><td><b>${b.userName}</b><br><span class="muted">${b.userEmail}</span></td><td>${b.lapangan}</td><td>${b.tanggal} ${b.jadwal}</td><td>${fmtRp(b.nominal)}</td><td>${b.status}</td><td><button class="btn btn-primary" data-verify="${b.id}">Verifikasi</button></td></tr>`).join("") : '<tr><td colspan="6" class="muted">Tidak ada pembayaran menunggu.</td></tr>'}</tbody></table></div></section><section class="panel" style="margin-bottom:14px"><h2 style="color:#9dffb2;margin-bottom:12px">Kelola Data Lapangan</h2><div class="table-card"><table><thead><tr><th>Nama</th><th>Lokasi</th><th>Harga</th></tr></thead><tbody>${FIELDS.map(f=>`<tr><td>${f.name}</td><td>${f.alamat}</td><td>${f.harga[0][1]}</td></tr>`).join("")}</tbody></table></div></section><section class="panel"><h2 style="color:#9dffb2;margin-bottom:12px">User Booking Terbaru</h2><div class="table-card"><table><thead><tr><th>User</th><th>Lapangan</th><th>Jadwal</th><th>Nominal</th><th>Status</th></tr></thead><tbody>${bookings.slice(0,8).map(b=>`<tr><td>${b.userName}</td><td>${b.lapangan}</td><td>${b.jadwal}</td><td>${fmtRp(b.nominal)}</td><td>${b.status}</td></tr>`).join("") || '<tr><td colspan="5" class="muted">Belum ada booking.</td></tr>'}</tbody></table></div></section></main>`;
  $("#adminLogout").addEventListener("click", () => { store.admin = ""; go("/login"); });
  document.querySelectorAll("[data-verify]").forEach(btn => btn.addEventListener("click", () => { store.bookings = bookings.map(b => b.id === btn.dataset.verify ? { ...b, status:"sukses" } : b); adminPage(); }));
}

window.addEventListener("hashchange", render);
render();
