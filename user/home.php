<?php
require_once '../includes/config.php';
requireLogin();

// Kamar
$kamar_all = safeQuery($conn, "SELECT * FROM kamar WHERE status='tersedia' LIMIT 3");

// Fasilitas
$fas_all = safeQuery($conn, "SELECT * FROM fasilitas LIMIT 6");

$bg_map = ['Deluxe'=>'bg-deluxe','Superior'=>'bg-sup','Standard'=>'bg-std'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beranda - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php" class="active">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php">Fasilitas</a>
    <a href="booking.php">Pesan Kamar</a>
    <a href="my_booking.php">Booking Saya</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<div class="hero">
  <div class="hero-content">
    <div class="hero-badge">✦ Selamat Datang Di ✦</div>
    <h1>Aston Hotel</h1>
    <p>Kenyamanan &amp; Kemewahan di Setiap Sudut</p>
    <div class="hero-role user">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</div>
  </div>
</div>

<div class="welcome-strip">
  <h3>Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h3>
  <p>Nikmati layanan terbaik dari Aston Hotel</p>
</div>

<div class="about-section">
<div class="about-img-box">
  <img src="../rooms/hotel.jpg"
       alt="Aston Hotel" 
       style="width:100%; height:100%; object-fit:cover; border-radius:12px;">
</div>
  <div class="about-text">
    <div class="gold-line"></div>
    <h4>Tentang Aston Hotel</h4>
    <p>Aston Hotel adalah hotel nyaman dengan fasilitas lengkap, pelayanan ramah, dan harga terjangkau. Cocok untuk liburan maupun perjalanan bisnis. Kami berkomitmen memberikan pengalaman menginap terbaik bagi setiap tamu kami.</p>
    <br>
    <a href="booking.php" class="btn btn-primary">Pesan Kamar Sekarang →</a>
  </div>
</div>

<div class="section-wrap">
  <div class="section-header">
    <h4>Kamar Tersedia</h4>
    <p>Pilih kamar yang sesuai dengan kebutuhan Anda</p>
  </div>
  <div class="grid-3">
  <?php foreach ($kamar_all as $k):
    $bg = isset($bg_map[$k['tipe']]) ? $bg_map[$k['tipe']] : 'bg-default';
  ?>
   <div class="card">
  <div class="card-img <?= $bg ?>">
  <?php if (!empty($k['gambar'])): ?>
    <img src="../uploads/kamar/<?= htmlspecialchars($k['gambar']) ?>" 
         alt="<?= htmlspecialchars($k['nama_kamar']) ?>"
         style="width:100%; height:100%; object-fit:cover;"
         onerror="this.style.display='none'">
  <?php endif; ?>
</div>
    <div class="card-body">
      <h5><?= htmlspecialchars($k['nama_kamar']) ?></h5>
      <div style="font-size:12px; color:#888; margin-bottom:6px;"><?= $k['tipe'] ?></div>
      <div class="price-tag"><?= formatRupiah($k['harga']) ?>/malam</div>
      <div class="card-desc"><?= htmlspecialchars($k['deskripsi']) ?></div>
      <a href="booking.php?kamar_id=<?= $k['id'] ?>" class="btn btn-success btn-sm">🛎️ Pesan Sekarang</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
  <div style="text-align:center;margin-top:24px;">
    <a href="kamar.php" class="btn btn-primary">Lihat Semua Kamar →</a>
  </div>
</div>

<div class="section-wrap gray-bg">
  <div class="section-header">
    <h4>Fasilitas Hotel</h4>
    <p>Nikmati berbagai fasilitas terbaik kami</p>
  </div>
  <div class="grid-3">
    <?php foreach ($fas_all as $f): ?>
    <div class="card">
      <div class="card-img bg-default">
  <img src="../fasilitas/fas<?= $f['id'] ?>.jpg"
       alt="<?= htmlspecialchars($f['nama']) ?>"
       style="width:100%; height:100%; object-fit:cover;"
       onerror="this.style.display='none'">
</div>
      <div class="card-body">
        <h5><?= htmlspecialchars($f['nama']) ?></h5>
        <div class="card-desc"><?= htmlspecialchars($f['deskripsi']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<footer><p>&copy; 2026 <span>Aston Hotel</span> — Semua Hak Dilindungi</p></footer>

<!-- MODAL KELUAR -->
<div id="modal-keluar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:14px; padding:36px 32px; max-width:380px; width:90%; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,0.18); animation:popIn 0.2s ease;">
    <div style="font-size:48px; margin-bottom:12px;">🚪</div>
    <h5 style="font-size:20px; font-weight:800; margin-bottom:8px; color:#111;">Yakin ingin keluar?</h5>
    <p style="color:#666; font-size:14px; margin-bottom:24px;">Pastikan semua perubahan sudah tersimpan.</p>
    <div style="display:flex; gap:12px; justify-content:center;">
      <button onclick="hideModalKeluar()" style="padding:10px 28px; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; border:none; background:#e2e8f0; color:#333;">Tidak</button>
      <a href="../logout.php" style="padding:10px 28px; border-radius:8px; font-size:15px; font-weight:700; border:none; background:#e53e3e; color:#fff; text-decoration:none;">Ya, Keluar</a>
    </div>
  </div>
</div>
<style>
@keyframes popIn {
  from { transform:scale(0.85); opacity:0; }
  to   { transform:scale(1);    opacity:1; }
}
</style>
<script>
function showModalKeluar() {
  document.getElementById('modal-keluar').style.display = 'flex';
}
function hideModalKeluar() {
  document.getElementById('modal-keluar').style.display = 'none';
}
document.getElementById('modal-keluar').addEventListener('click', function(e) {
  if (e.target === this) hideModalKeluar();
});
</script>

</body>
</html>