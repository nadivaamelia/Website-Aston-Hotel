<?php
require_once '../includes/config.php';
requireAdmin();

// Filter status menggunakan prepared statement
$filter = isset($_GET['filter']) ? sanitize($conn, $_GET['filter']) : '';

$allowed_filters = ['aktif', 'selesai', 'dibatalkan'];

if ($filter && in_array($filter, $allowed_filters)) {
    $stmt = $conn->prepare("
        SELECT p.*, u.username, k.nama_kamar, k.tipe, k.harga
        FROM pemesanan p
        JOIN users u ON p.user_id = u.id
        JOIN kamar k ON p.kamar_id = k.id
        WHERE p.status = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($r = $result->fetch_assoc()) $data[] = $r;
    $stmt->close();
} else {
    $filter = '';
    $data = safeQuery($conn, "
        SELECT p.*, u.username, k.nama_kamar, k.tipe, k.harga
        FROM pemesanan p
        JOIN users u ON p.user_id = u.id
        JOIN kamar k ON p.kamar_id = k.id
        ORDER BY p.created_at DESC
    ");
}

// Total pendapatan
$total = array_sum(array_column($data, 'total_harga'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Booking - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php">Fasilitas</a>
    <a href="data_pesan.php">Data Pesan</a>
    <a href="data_pelanggan.php">Data Pelanggan</a>
    <a href="data_booking.php" class="active">Data Booking</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; 
    font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; 
    border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); 
    cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<div class="admin-wrap">

  <div class="page-title">
    <h5>📊 Data Booking Lengkap</h5>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <a href="data_booking.php" class="btn btn-<?= !$filter ? 'primary' : 'secondary' ?> btn-sm">Semua</a>
      <a href="data_booking.php?filter=aktif" class="btn btn-<?= $filter==='aktif' ? 'success' : 'secondary' ?> btn-sm">Aktif</a>
      <a href="data_booking.php?filter=selesai" class="btn btn-<?= $filter==='selesai' ? 'danger' : 'secondary' ?> btn-sm">Selesai</a>
      <a href="data_booking.php?filter=dibatalkan" class="btn btn-<?= $filter==='dibatalkan' ? 'warning' : 'secondary' ?> btn-sm">Dibatalkan</a>
    </div>
  </div>

  <!-- SUMMARY -->
  <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-icon">📋</div>
      <div class="stat-info"><h3><?= count($data) ?></h3><p>Total Ditampilkan</p></div>
    </div>
    <div class="stat-card" style="border-color:#198754">
      <div class="stat-icon">💰</div>
      <div class="stat-info"><h3 style="font-size:16px;"><?= formatRupiah($total) ?></h3><p>Total Pendapatan</p></div>
    </div>
    <div class="stat-card" style="border-color:#c9a84c">
      <div class="stat-icon">🌙</div>
      <div class="stat-info"><h3><?= array_sum(array_column($data,'jumlah_malam')) ?></h3><p>Total Malam</p></div>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Tamu</th>
          <th>Akun</th>
          <th>Kamar</th>
          <th>Check In</th>
          <th>Check Out</th>
          <th>Malam</th>
          <th>Harga/mlm</th>
          <th>Total</th>
          <th>Status</th>
          <th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
          <tr><td colspan="11" style="text-align:center;color:#aaa;padding:30px;">Tidak ada data booking</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $i => $d): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($d['nama_tamu']) ?></strong></td>
          <td style="color:#888;"><?= htmlspecialchars($d['username']) ?></td>
          <td><?= htmlspecialchars($d['nama_kamar']) ?><br><small style="color:#aaa;"><?= $d['tipe'] ?></small></td>
          <td><?= date('d/m/Y', strtotime($d['tanggal_masuk'])) ?></td>
          <td><?= date('d/m/Y', strtotime($d['tanggal_keluar'])) ?></td>
          <td style="text-align:center;"><?= $d['jumlah_malam'] ?></td>
          <td><?= formatRupiah($d['harga']) ?></td>
          <td style="font-weight:700; color:#0aa8cc;"><?= formatRupiah($d['total_harga']) ?></td>
          <td><span class="badge badge-<?= $d['status'] ?>"><?= $d['status'] ?></span></td>
          <td style="color:#888; font-size:12px;"><?= htmlspecialchars($d['catatan'] ?: '-') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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
  const m = document.getElementById('modal-keluar');
  m.style.display = 'flex';
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