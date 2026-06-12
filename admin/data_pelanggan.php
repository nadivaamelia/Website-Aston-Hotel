<?php
require_once '../includes/config.php';
requireAdmin();

$msg = '';

if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id !== (int)$_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='pelanggan'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $msg = ['type'=>'danger','text'=>'Pelanggan berhasil dihapus!'];
    } else {
        $msg = ['type'=>'danger','text'=>'Tidak dapat menghapus akun sendiri!'];
    }
}

$data = safeQuery($conn, "SELECT u.*, COUNT(p.id) as total_booking
    FROM users u
    LEFT JOIN pemesanan p ON u.id = p.user_id
    WHERE u.role = 'pelanggan'
    GROUP BY u.id
    ORDER BY u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pelanggan - Aston Hotel</title>
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
    <a href="data_pelanggan.php" class="active">Data Pelanggan</a>
    <a href="data_booking.php">Data Booking</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<div class="admin-wrap">

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
  <?php endif; ?>

  <div class="page-title">
    <h5>👥 Data Pelanggan</h5>
    <small style="color:#888"><?= count($data) ?> pelanggan terdaftar</small>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Username</th>
          <th>Role</th>
          <th>Alamat</th>
          <th>Telepon</th>
          <th>Total Booking</th>
          <th>Daftar</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
          <tr><td colspan="8" style="text-align:center;color:#aaa;padding:30px;">Belum ada pelanggan terdaftar</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $i => $u): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong>👤 <?= htmlspecialchars($u['username']) ?></strong></td>
          <td><span class="badge badge-pelanggan">Pelanggan</span></td>
          <td style="color:#888; max-width:180px;"><?= htmlspecialchars($u['alamat']) ?></td>
          <td><?= htmlspecialchars($u['telepon']) ?></td>
          <td style="text-align:center;">
            <span class="badge badge-aktif"><?= $u['total_booking'] ?> booking</span>
          </td>
          <td style="color:#aaa; font-size:12px;"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="data_pelanggan.php?hapus=<?= $u['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Hapus pelanggan ini? Semua booking terkait juga akan terhapus!')">
               🗑️ Hapus
            </a>
          </td>
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