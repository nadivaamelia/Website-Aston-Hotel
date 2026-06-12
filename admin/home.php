<?php
require_once '../includes/config.php';
requireAdmin();

// Ambil statistik
$total_kamar     = $conn->query("SELECT COUNT(*) as c FROM kamar")->fetch_assoc()['c'];
$total_pelanggan = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='pelanggan'")->fetch_assoc()['c'];
$total_booking   = $conn->query("SELECT COUNT(*) as c FROM pemesanan")->fetch_assoc()['c'];
$booking_aktif   = $conn->query("SELECT COUNT(*) as c FROM pemesanan WHERE status='aktif'")->fetch_assoc()['c'];

// Kamar populer (tampil 3 saja di home)
$kamar_res = $conn->query("SELECT * FROM kamar LIMIT 3");
$kamar_all = [];
while ($r = $kamar_res->fetch_assoc()) $kamar_all[] = $r;

// Fasilitas
$fas_res = $conn->query("SELECT * FROM fasilitas LIMIT 6");
$fas_all = [];
while ($r = $fas_res->fetch_assoc()) $fas_all[] = $r;

// Mapping background per tipe
$bg_map = ['Deluxe' => 'bg-deluxe', 'Superior' => 'bg-sup', 'Standard' => 'bg-std'];

// Mapping gambar kamar berdasarkan tipe (sesuai folder rooms/)
$gambar_map = [
    'Deluxe'   => '../rooms/kamar1.jpg',
    'Superior' => '../rooms/kamar2.jpg',
    'Standard' => '../rooms/kamar3.jpg',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Home - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php" class="active">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php">Fasilitas</a>
    <a href="data_pesan.php">Data Pesan</a>
    <a href="data_pelanggan.php">Data Pelanggan</a>
    <a href="data_booking.php">Data Booking</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-content">
    <div class="hero-badge">✦ Selamat Datang Di ✦</div>
    <h1>Aston Hotel</h1>
    <p>Kenyamanan &amp; Kemewahan di Setiap Sudut</p>
    <div class="hero-role">Login sebagai: ADMIN</div>
  </div>
</div>

<!-- WELCOME -->
<div class="welcome-strip">
  <h3>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h3>
  <p>Panel administrasi hotel — kelola semua data dengan mudah</p>
</div>

<!-- STATS -->
<div class="admin-wrap">
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">🛏️</div>
      <div class="stat-info">
        <h3><?php echo $total_kamar; ?></h3>
        <p>Total Kamar</p>
      </div>
    </div>
    <div class="stat-card" style="border-color:#198754">
      <div class="stat-icon">👥</div>
      <div class="stat-info">
        <h3><?php echo $total_pelanggan; ?></h3>
        <p>Pelanggan</p>
      </div>
    </div>
    <div class="stat-card" style="border-color:#c9a84c">
      <div class="stat-icon">📋</div>
      <div class="stat-info">
        <h3><?php echo $total_booking; ?></h3>
        <p>Total Booking</p>
      </div>
    </div>
    <div class="stat-card" style="border-color:#0dcaf0">
      <div class="stat-icon">✅</div>
      <div class="stat-info">
        <h3><?php echo $booking_aktif; ?></h3>
        <p>Booking Aktif</p>
      </div>
    </div>
  </div>
</div>

<!-- ABOUT -->
<div class="about-section">
  <div class="about-img-box">
    <img src="../rooms/hotel.jpg" alt="Aston Hotel"
         style="width:100%; height:100%; object-fit:cover; border-radius:12px;"
         onerror="this.outerHTML='🏨'">
  </div>
  <div class="about-text">
    <div class="gold-line"></div>
    <h4>Tentang Aston Hotel</h4>
    <p>Aston Hotel adalah hotel nyaman dengan fasilitas lengkap, pelayanan ramah, dan harga terjangkau. Cocok untuk liburan maupun perjalanan bisnis. Kami berkomitmen memberikan pengalaman menginap terbaik bagi setiap tamu kami.</p>
  </div>
</div>

<!-- KAMAR POPULER -->
<div class="section-wrap">
  <div class="section-header">
    <h4>Kamar Tersedia</h4>
    <p>Daftar kamar hotel saat ini</p>
  </div>
  <div class="grid-3">
    <?php foreach ($kamar_all as $k):
      $bg  = isset($bg_map[$k['tipe']]) ? $bg_map[$k['tipe']] : 'bg-default';

      if (!empty($k['gambar'])) {
          $img_src = '../uploads/kamar/' . htmlspecialchars($k['gambar']);
      } else {
          $img_src = isset($gambar_map[$k['tipe']]) ? $gambar_map[$k['tipe']] : '';
      }
    ?>
    <div class="card">
      <div class="card-img <?php echo $bg; ?>">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>"
               alt="<?php echo htmlspecialchars($k['tipe']); ?>"
               style="width:100%; height:100%; object-fit:cover;"
               onerror="this.outerHTML='🛏️'">
        <?php else: ?>
          🛏️
        <?php endif; ?>
      </div>
      <div class="card-body">
        <h5 style="color:#000; font-weight:800; font-size:18px; text-transform:uppercase;">
          <?php echo htmlspecialchars($k['tipe']); ?>
        </h5>
        <div class="price-tag"><?php echo formatRupiah($k['harga']); ?>/malam</div>
        <div class="card-desc"><?php echo htmlspecialchars(substr($k['deskripsi'], 0, 80)); ?>...</div>
        <span class="badge badge-<?php echo $k['status'] === 'tersedia' ? 'tersedia' : 'tidak'; ?>">
          <?php echo $k['status']; ?>
        </span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="text-align:center; margin-top:24px;">
    <a href="kamar.php" class="btn btn-primary">Kelola Semua Kamar →</a>
  </div>
</div>

<!-- FASILITAS -->
<div class="section-wrap gray-bg">
  <div class="section-header">
    <h4>Fasilitas Hotel</h4>
    <p>Nikmati berbagai fasilitas terbaik kami</p>
  </div>
  <div class="grid-3">
    <?php foreach ($fas_all as $f): ?>
    <div class="card">
      <div class="card-img bg-default">
        <?php if (!empty($f['gambar'])): ?>
          <img src="../uploads/fasilitas/<?= htmlspecialchars($f['gambar']) ?>"
               alt="<?php echo htmlspecialchars($f['nama']); ?>"
               style="width:100%; height:100%; object-fit:cover;"
               onerror="this.style.display='none'">
        <?php else: ?>
          <img src="../fasilitas/fas<?php echo $f['id']; ?>.jpg"
               alt="<?php echo htmlspecialchars($f['nama']); ?>"
               style="width:100%; height:100%; object-fit:cover;"
               onerror="this.style.display='none'">
        <?php endif; ?>
      </div>
      <div class="card-body">
        <h5><?php echo htmlspecialchars($f['nama']); ?></h5>
        <div class="card-desc"><?php echo htmlspecialchars($f['deskripsi']); ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<footer>
  <p>&copy; 2026 <span>Aston Hotel</span> — Semua Hak Dilindungi</p>
</footer>

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