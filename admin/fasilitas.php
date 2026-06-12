<?php
require_once '../includes/config.php';
requireAdmin();

$msg = '';
$upload_dir = '../uploads/fasilitas/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

function uploadGambar($file, $upload_dir, $old_gambar = '') {
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) return $old_gambar;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed)) return $old_gambar;
    $filename = uniqid('fas_') . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
    if ($old_gambar && file_exists($upload_dir . $old_gambar)) unlink($upload_dir . $old_gambar);
    return $filename;
}

if (isset($_POST['tambah'])) {
    $nama = sanitize($conn, $_POST['nama']);
    $desc = sanitize($conn, $_POST['deskripsi']);
    $gambar = uploadGambar($_FILES['gambar'], $upload_dir);
    $stmt = $conn->prepare("INSERT INTO fasilitas (nama, deskripsi, gambar) VALUES (?,?,?)");
    $stmt->bind_param("sss", $nama, $desc, $gambar);
    $stmt->execute(); $stmt->close();
    header("Location: fasilitas.php?msg=added");
    exit;
}

if (isset($_POST['edit'])) {
    $id   = (int)$_POST['id'];
    $nama = sanitize($conn, $_POST['nama']);
    $desc = sanitize($conn, $_POST['deskripsi']);
    $old  = safeQueryOne($conn, "SELECT gambar FROM fasilitas WHERE id=$id");
    $gambar = uploadGambar($_FILES['gambar'], $upload_dir, $old['gambar'] ?? '');
    $stmt = $conn->prepare("UPDATE fasilitas SET nama=?,deskripsi=?,gambar=? WHERE id=?");
    $stmt->bind_param("sssi", $nama, $desc, $gambar, $id);
    $stmt->execute(); $stmt->close();
    header("Location: fasilitas.php?msg=updated");
    exit;
}

if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $old = safeQueryOne($conn, "SELECT gambar FROM fasilitas WHERE id=$id");
    if (!empty($old['gambar']) && file_exists($upload_dir . $old['gambar'])) unlink($upload_dir . $old['gambar']);
    $conn->query("DELETE FROM fasilitas WHERE id=$id");
    header("Location: fasilitas.php?msg=deleted");
    exit;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'updated') $msg = ['type'=>'success', 'text'=>'Fasilitas berhasil diperbarui!'];
    if ($_GET['msg'] === 'added')   $msg = ['type'=>'success', 'text'=>'Fasilitas berhasil ditambahkan!'];
    if ($_GET['msg'] === 'deleted') $msg = ['type'=>'danger',  'text'=>'Fasilitas berhasil dihapus!'];
}

$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id = (int)$_GET['edit_id'];
    $edit_data = safeQueryOne($conn, "SELECT * FROM fasilitas WHERE id=$id");
}

$fas_all = safeQuery($conn, "SELECT * FROM fasilitas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Fasilitas - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  .file-upload-box {
    display: flex; align-items: center; gap: 10px;
    width: 100%; padding: 12px 16px;
    border: 1.5px solid #d1d5db; border-radius: 8px;
    background: #f3f4f6; cursor: pointer;
    font-size: 14px; color: #374151; box-sizing: border-box;
    transition: border-color 0.2s, background 0.2s;
  }
  .file-upload-box:hover { border-color: #6b7280; background: #e9eaec; }
  #gambar { display: none; }
  #preview-img { max-width: 200px; border-radius: 8px; border: 1px solid #ddd; margin-top: 10px; }
  #preview-nama { display:block; margin-top:4px; font-size:12px; color:#888; }
  .tbl-img { width: 80px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }

  @keyframes modalFadeIn {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
  }
  @keyframes modalFadeOut {
    from { opacity: 1; transform: scale(1); }
    to   { opacity: 0; transform: scale(0.85); }
  }
</style>
</head>

<!-- MODAL KELUAR -->
<div id="modal-keluar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
  <div id="modal-keluar-box" style="background:#fff; border-radius:14px; padding:36px 32px; max-width:380px; width:90%; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,0.18);">
    <div style="font-size:48px; margin-bottom:12px;">🚪</div>
    <h5 style="font-size:20px; font-weight:800; margin-bottom:8px; color:#111;">Yakin ingin keluar?</h5>
    <p style="color:#666; font-size:14px; margin-bottom:24px;">Pastikan semua perubahan sudah tersimpan.</p>
    <div style="display:flex; gap:12px; justify-content:center;">
      <button onclick="hideModalKeluar()" style="padding:10px 28px; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; border:none; background:#e2e8f0; color:#333;">Tidak</button>
      <a href="../logout.php" style="padding:10px 28px; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; border:none; background:#e53e3e; color:#fff; text-decoration:none;">Ya, Keluar</a>
    </div>
  </div>
</div>

<script>
function showModalKeluar() {
  const m   = document.getElementById('modal-keluar');
  const box = document.getElementById('modal-keluar-box');
  m.style.display     = 'flex';
  box.style.animation = 'modalFadeIn 0.25s ease forwards';
}
function hideModalKeluar() {
  const m   = document.getElementById('modal-keluar');
  const box = document.getElementById('modal-keluar-box');
  box.style.animation = 'modalFadeOut 0.2s ease forwards';
  setTimeout(() => {
    m.style.display     = 'none';
    box.style.animation = '';
  }, 200);
}
document.getElementById('modal-keluar').addEventListener('click', function(e) {
  if (e.target === this) hideModalKeluar();
});
</script>

<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php" class="active">Fasilitas</a>
    <a href="data_pesan.php">Data Pesan</a>
    <a href="data_pelanggan.php">Data Pelanggan</a>
    <a href="data_booking.php">Data Booking</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white;
      font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px;
      border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12);
      cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<div class="admin-wrap">

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
  <?php endif; ?>

  <div class="booking-card" style="margin-bottom:30px; max-width:100%;">
    <h5><?= $edit_data ? '✏️ Edit Fasilitas' : '➕ Tambah Fasilitas' ?></h5>
    <p class="booking-sub">Isi data fasilitas hotel</p>

    <form method="POST" enctype="multipart/form-data" id="form-fasilitas">
      <?php if ($edit_data): ?>
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
      <?php endif; ?>

      <div class="form-group">
        <label>Nama Fasilitas</label>
        <input type="text" name="nama" class="form-control"
               value="<?= htmlspecialchars($edit_data['nama'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control"><?= htmlspecialchars($edit_data['deskripsi'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Gambar Fasilitas</label><br>
        <label class="file-upload-box" for="gambar">
          📂 PILIH GAMBAR (JPG/PNG/WEBP)
        </label>
        <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp"
               onchange="previewGambar(this)">
        <div id="preview-wrap">
          <?php if (!empty($edit_data['gambar'])): ?>
            <img id="preview-img" src="../uploads/fasilitas/<?= htmlspecialchars($edit_data['gambar']) ?>" alt="Preview">
            <small id="preview-nama"><?= htmlspecialchars($edit_data['gambar']) ?></small>
          <?php else: ?>
            <img id="preview-img" src="" alt="" style="display:none">
            <small id="preview-nama" style="display:none;"></small>
          <?php endif; ?>
        </div>
      </div>

      <div style="display:flex; gap:10px;">
        <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>" class="btn btn-success">
          <?= $edit_data ? '💾 Simpan' : '➕ Tambah' ?>
        </button>
        <?php if ($edit_data): ?>
          <a href="fasilitas.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="page-title"><h5>✨ Data Fasilitas</h5></div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Gambar</th><th>Nama Fasilitas</th><th>Deskripsi</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (!$fas_all): ?>
          <tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">Belum ada fasilitas</td></tr>
        <?php endif; ?>
        <?php foreach ($fas_all as $i => $f): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td>
            <?php if (!empty($f['gambar'])): ?>
              <img src="../uploads/fasilitas/<?= htmlspecialchars($f['gambar']) ?>"
                   alt="<?= htmlspecialchars($f['nama']) ?>" class="tbl-img">
            <?php else: ?>
              <span style="color:#aaa;font-size:12px;">No image</span>
            <?php endif; ?>
          </td>
          <td><strong><?= htmlspecialchars($f['nama']) ?></strong></td>
          <td style="color:#888;"><?= htmlspecialchars($f['deskripsi']) ?></td>
          <td>
            <a href="fasilitas.php?edit_id=<?= $f['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
            <a href="fasilitas.php?hapus=<?= $f['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Hapus fasilitas ini?')">🗑️ Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<footer><p>&copy; 2026 <span>Aston Hotel</span></p></footer>

<script>
function previewGambar(input) {
  const img  = document.getElementById('preview-img');
  const nama = document.getElementById('preview-nama');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      img.style.display = 'block';
      nama.textContent   = input.files[0].name;
      nama.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

document.getElementById('form-fasilitas').addEventListener('submit', function() {
  setTimeout(() => {
    const fileInput = document.getElementById('gambar');
    const img       = document.getElementById('preview-img');
    const nama      = document.getElementById('preview-nama');
    fileInput.value    = '';
    img.src            = '';
    img.style.display  = 'none';
    nama.textContent   = '';
    nama.style.display = 'none';
  }, 100);
});
</script>
</body>
</html>