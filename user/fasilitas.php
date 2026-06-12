<?php
require_once '../includes/config.php';
requireLogin();

$fas_all = safeQuery($conn, "SELECT * FROM fasilitas ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fasilitas - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php" class="active">Fasilitas</a>
    <a href="booking.php">Pesan Kamar</a>
    <a href="my_booking.php">Booking Saya</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<div class="section-wrap" style="background:linear-gradient(135deg,#0a3d62,#0dcaf0);padding:40px 48px;text-align:center;">
  <h4 style="color:white;font-family:'Playfair Display',serif;font-size:32px;margin-bottom:8px;">Fasilitas Hotel</h4>
  <p style="color:rgba(255,255,255,0.8);">Nikmati berbagai fasilitas premium yang kami sediakan</p>
</div>

<div class="section-wrap gray-bg">
  <div class="grid-3">
    <?php foreach ($fas_all as $f): ?>
    <div class="card">
      <div class="card-img bg-default" style="display:flex;align-items:center;justify-content:center;">
        <?php if (!empty($f['gambar'])): ?>
          <img src="../uploads/fasilitas/<?= htmlspecialchars($f['gambar']) ?>"
               alt="<?= htmlspecialchars($f['nama']) ?>"
               style="width:100%; height:100%; object-fit:cover;"
               onerror="this.style.display='none'">
        <?php endif; ?>
      </div>
      <div class="card-body">
        <h5><?= htmlspecialchars($f['nama']) ?></h5>
        <div class="card-desc"><?= htmlspecialchars($f['deskripsi']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<footer><p>&copy; 2026 <span>Aston Hotel</span></p></footer>

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