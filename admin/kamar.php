<?php
require_once '../includes/config.php';
requireAdmin();

$msg = '';

// ── TAMBAH KAMAR ──
if (isset($_POST['tambah'])) {
    $tipe  = sanitize($conn, $_POST['tipe']);
    $harga = (int)$_POST['harga'];
    $desc  = sanitize($conn, $_POST['deskripsi']);
    $sts   = sanitize($conn, $_POST['status']);
    $nama  = $tipe . ' Room';

    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $gambar     = time() . '_' . uniqid() . '.' . $ext;
            $upload_dir = '../uploads/kamar/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar);
        } else {
            $msg = ['type'=>'danger', 'text'=>'Format gambar tidak didukung! Gunakan JPG, PNG, atau WEBP.'];
        }
    }

    if (!isset($msg['type']) || $msg['type'] !== 'danger') {
        $stmt = $conn->prepare("INSERT INTO kamar (nama_kamar,tipe,harga,deskripsi,status,gambar) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssisss", $nama, $tipe, $harga, $desc, $sts, $gambar);
        $stmt->execute();
        $stmt->close();
        header("Location: kamar.php?msg=added");
        exit;
    }
}

// ── EDIT KAMAR ──
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id'];
    $tipe  = sanitize($conn, $_POST['tipe']);
    $harga = (int)$_POST['harga'];
    $desc  = sanitize($conn, $_POST['deskripsi']);
    $sts   = sanitize($conn, $_POST['status']);
    $nama  = $tipe . ' Room';

    $old    = safeQueryOne($conn, "SELECT gambar FROM kamar WHERE id=$id");
    $gambar = isset($old['gambar']) ? $old['gambar'] : '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            if ($gambar && file_exists('../uploads/kamar/' . $gambar)) {
                unlink('../uploads/kamar/' . $gambar);
            }
            $gambar     = time() . '_' . uniqid() . '.' . $ext;
            $upload_dir = '../uploads/kamar/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar);
        }
    }

    $stmt = $conn->prepare("UPDATE kamar SET nama_kamar=?,tipe=?,harga=?,deskripsi=?,status=?,gambar=? WHERE id=?");
    $stmt->bind_param("ssisssi", $nama, $tipe, $harga, $desc, $sts, $gambar, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: kamar.php?msg=updated");
    exit;
}

// ── HAPUS KAMAR ──
if (isset($_GET['hapus'])) {
    $id  = (int)$_GET['hapus'];
    $row = safeQueryOne($conn, "SELECT gambar FROM kamar WHERE id=$id");
    if (!empty($row['gambar']) && file_exists('../uploads/kamar/' . $row['gambar'])) {
        unlink('../uploads/kamar/' . $row['gambar']);
    }
    $conn->query("DELETE FROM kamar WHERE id=$id");
    header("Location: kamar.php?msg=deleted");
    exit;
}

// ── PESAN NOTIFIKASI ──
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'updated') $msg = ['type'=>'success', 'text'=>'Kamar berhasil diperbarui!'];
    if ($_GET['msg'] === 'added')   $msg = ['type'=>'success', 'text'=>'Kamar berhasil ditambahkan!'];
    if ($_GET['msg'] === 'deleted') $msg = ['type'=>'danger',  'text'=>'Kamar berhasil dihapus!'];
}

// ── GET DATA EDIT ──
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $id        = (int)$_GET['edit_id'];
    $edit_data = safeQueryOne($conn, "SELECT * FROM kamar WHERE id=$id");
}

// ── AMBIL SEMUA KAMAR ──
$kamar_all = safeQuery($conn, "SELECT * FROM kamar ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kamar - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  .preview-wrap { margin-top: 8px; }
  .preview-wrap img { width: 120px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
  .file-label {
    display: block;
    width: 100%;
    padding: 12px 16px;
    background: #f3f4f6;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
    box-sizing: border-box;
    transition: border-color 0.2s, background 0.2s;
  }
  .file-label:hover { background: #e9eaec; border-color: #6b7280; }
  input[type="file"] { display: none; }

  .tipe-bold {
    font-weight: 800;
    font-size: 14px;
    color: #000;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .form-control option { font-weight: 700; font-size: 14px; }
</style>
</head>
<!-- MODAL KELUAR -->
<div id="modal-keluar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:14px; padding:36px 32px; max-width:380px; width:90%; text-align:center; box-shadow:0 8px 32px rgba(0,0,0,0.18);">
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
<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="kamar.php" class="active">Kamar</a>
    <a href="fasilitas.php">Fasilitas</a>
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

  <!-- FORM TAMBAH / EDIT -->
  <div class="booking-card" style="margin-bottom:30px; max-width:100%;">
    <h5><?= $edit_data ? '✏️ Edit Kamar' : '➕ Tambah Kamar Baru' ?></h5>
    <p class="booking-sub">Isi data kamar di bawah ini</p>

    <form method="POST" enctype="multipart/form-data" id="form-kamar">
      <?php if ($edit_data): ?>
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
      <?php endif; ?>

      <div class="form-row">
        <div class="form-group">
          <label>TIPE</label>
          <select name="tipe" class="form-control" style="font-weight:700; font-size:15px;">
            <?php foreach (['Deluxe','Superior','Standard'] as $t): ?>
              <option value="<?= $t ?>" <?= isset($edit_data['tipe']) && $edit_data['tipe'] === $t ? 'selected' : '' ?>>
                <?= strtoupper($t) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>STATUS</label>
          <select name="status" class="form-control" style="font-weight:700; font-size:15px;">
            <option value="tersedia"       <?= isset($edit_data['status']) && $edit_data['status'] === 'tersedia'       ? 'selected' : '' ?>>Tersedia</option>
            <option value="tidak tersedia" <?= isset($edit_data['status']) && $edit_data['status'] === 'tidak tersedia' ? 'selected' : '' ?>>Tidak Tersedia</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>HARGA / MALAM (RP)</label>
          <input type="number" name="harga" class="form-control"
                 value="<?= isset($edit_data['harga']) ? $edit_data['harga'] : '' ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label>DESKRIPSI</label>
        <textarea name="deskripsi" class="form-control"><?= htmlspecialchars(isset($edit_data['deskripsi']) ? $edit_data['deskripsi'] : '') ?></textarea>
      </div>

      <!-- Field Upload Gambar -->
      <div class="form-group">
        <label>GAMBAR KAMAR</label>
        <br>
        <label class="file-label" for="gambar">📂 PILIH GAMBAR (JPG/PNG/WEBP)</label>
        <input type="file" id="gambar" name="gambar" accept=".jpg,.jpeg,.png,.webp"
               onchange="previewGambar(this)">
        <div class="preview-wrap" id="preview-wrap">
          <?php if (!empty($edit_data['gambar'])): ?>
            <img id="preview-img" src="../uploads/kamar/<?= htmlspecialchars($edit_data['gambar']) ?>"
                 alt="Gambar kamar">
            <br><small id="preview-nama" style="color:#888"><?= htmlspecialchars($edit_data['gambar']) ?></small>
          <?php else: ?>
            <img id="preview-img" src="" alt="" style="display:none">
            <small id="preview-nama" style="color:#888; display:none;"></small>
          <?php endif; ?>
        </div>
      </div>

      <div style="display:flex; gap:10px;">
        <button type="submit" name="<?= $edit_data ? 'edit' : 'tambah' ?>" class="btn btn-success">
          <?= $edit_data ? '💾 Simpan Perubahan' : '➕ Tambah Kamar' ?>
        </button>
        <?php if ($edit_data): ?>
          <a href="kamar.php" class="btn btn-secondary">Batal</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- TABEL KAMAR -->
  <div class="page-title">
    <h5>🛏️ Data Kamar</h5>
    <small style="color:#888"><?= count($kamar_all) ?> kamar terdaftar</small>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Gambar</th>
          <th>Tipe</th>
          <th>Harga</th>
          <th>Status</th>
          <th>Deskripsi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$kamar_all): ?>
          <tr><td colspan="7" style="text-align:center; color:#aaa; padding:30px;">Belum ada data kamar</td></tr>
        <?php endif; ?>
        <?php foreach ($kamar_all as $i => $k): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td>
            <?php if (!empty($k['gambar'])): ?>
              <img src="../uploads/kamar/<?= htmlspecialchars($k['gambar']) ?>"
                   alt="<?= htmlspecialchars($k['tipe']) ?>"
                   style="width:70px;height:50px;object-fit:cover;border-radius:5px;">
            <?php else: ?>
              <span style="color:#bbb;font-size:12px;">Tidak ada</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="tipe-bold"><?= strtoupper(htmlspecialchars($k['tipe'])) ?></span>
          </td>
          <td><?= formatRupiah($k['harga']) ?></td>
          <td>
            <span class="badge badge-<?= $k['status'] === 'tersedia' ? 'tersedia' : 'tidak' ?>">
              <?= $k['status'] ?>
            </span>
          </td>
          <td style="max-width:200px; color:#888;"><?= htmlspecialchars(substr($k['deskripsi'], 0, 60)) ?>...</td>
          <td>
            <a href="kamar.php?edit_id=<?= $k['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
            <a href="kamar.php?hapus=<?= $k['id'] ?>" class="btn btn-danger btn-sm"
               onclick="return confirm('Hapus kamar ini?')">🗑️ Hapus</a>
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
  const img   = document.getElementById('preview-img');
  const nama  = document.getElementById('preview-nama');

  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      img.style.display = 'block';
      nama.textContent   = input.files[0].name;
      nama.style.display = 'inline';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Reset preview & input setelah form submit
document.getElementById('form-kamar').addEventListener('submit', function() {
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