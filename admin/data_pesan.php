<?php
require_once '../includes/config.php';
requireAdmin();

$msg = '';

// Update status
if (isset($_POST['update_status'])) {
    $id     = (int)$_POST['id'];
    $status = sanitize($conn, $_POST['status']);
    $stmt   = $conn->prepare("UPDATE pemesanan SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    $msg = ['type'=>'success','text'=>'Status berhasil diperbarui!'];
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $conn->query("DELETE FROM pemesanan WHERE id=$id");
    $msg = ['type'=>'danger','text'=>'Data pemesanan berhasil dihapus!'];
}

// Ambil semua pemesanan
$data = safeQuery($conn, "
    SELECT p.*, u.username, k.nama_kamar, k.tipe
    FROM pemesanan p
    JOIN users u ON p.user_id = u.id
    JOIN kamar k ON p.kamar_id = k.id
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pesan - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php">Fasilitas</a>
    <a href="data_pesan.php" class="active">Data Pesan</a>
    <a href="data_pelanggan.php">Data Pelanggan</a>
    <a href="data_booking.php">Data Booking</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<div class="admin-wrap">

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
  <?php endif; ?>

  <div class="page-title">
    <h5>📋 Data Pemesanan</h5>
    <small style="color:#888"><?= count($data) ?> total pemesanan</small>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Tamu</th>
          <th>Username</th>
          <th>Kamar</th>
          <th>Check In</th>
          <th>Check Out</th>
          <th>Malam</th>
          <th>Total</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$data): ?>
          <tr><td colspan="10" style="text-align:center;color:#aaa;padding:30px;">Belum ada data pemesanan</td></tr>
        <?php endif; ?>
        <?php foreach ($data as $i => $d): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><strong><?= htmlspecialchars($d['nama_tamu']) ?></strong></td>
          <td><?= htmlspecialchars($d['username']) ?></td>
          <td><?= htmlspecialchars($d['nama_kamar']) ?> <small style="color:#aaa;">(<?= $d['tipe'] ?>)</small></td>
          <td><?= date('d/m/Y', strtotime($d['tanggal_masuk'])) ?></td>
          <td><?= date('d/m/Y', strtotime($d['tanggal_keluar'])) ?></td>
          <td style="text-align:center"><?= $d['jumlah_malam'] ?></td>
          <td style="font-weight:600; color:#0aa8cc;"><?= formatRupiah($d['total_harga']) ?></td>
          <td>
            <span class="badge badge-<?= $d['status'] ?>">
              <?= $d['status'] ?>
            </span>
          </td>
          <td>
            <form method="POST" style="display:inline-flex; gap:4px; align-items:center;">
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <select name="status" style="padding:4px 8px; border-radius:6px; border:1px solid #ddd; font-size:12px;">
                <option value="aktif"      <?= $d['status']==='aktif'      ?'selected':'' ?>>Aktif</option>
                <option value="selesai"    <?= $d['status']==='selesai'    ?'selected':'' ?>>Selesai</option>
                <option value="dibatalkan" <?= $d['status']==='dibatalkan' ?'selected':'' ?>>Dibatalkan</option>
              </select>
              <button type="submit" name="update_status" class="btn btn-primary btn-sm">✔</button>
            </form>
            <a href="data_pesan.php?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Hapus data ini?')">🗑️</a>
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