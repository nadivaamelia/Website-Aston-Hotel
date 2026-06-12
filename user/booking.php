<?php
require_once '../includes/config.php';
requireLogin();

$msg     = '';
$success = false;
$booking_info = null;

// Preselect kamar dari URL
$preselect_kamar = isset($_GET['kamar_id']) ? (int)$_GET['kamar_id'] : 0;

// Ambil kamar tersedia
$kamar_res = $conn->query("SELECT * FROM kamar WHERE status='tersedia' ORDER BY tipe, harga");
$kamar_all = [];
while ($r = $kamar_res->fetch_assoc()) $kamar_all[] = $r;

// PROSES BOOKING
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $kamar_id  = (int)$_POST['kamar_id'];
    $nama_tamu = sanitize($conn, $_POST['nama_tamu']);
    $tgl_in    = sanitize($conn, $_POST['tanggal_masuk']);
    $tgl_out   = sanitize($conn, $_POST['tanggal_keluar']);
    $catatan   = sanitize($conn, isset($_POST['catatan']) ? $_POST['catatan'] : '');

    // Validasi
    if (!$kamar_id || !$nama_tamu || !$tgl_in || !$tgl_out) {
        $msg = ['type'=>'danger','text'=>'Harap isi semua kolom yang wajib!'];
    } elseif ($tgl_out <= $tgl_in) {
        $msg = ['type'=>'danger','text'=>'Tanggal keluar harus setelah tanggal masuk!'];
    } elseif ($tgl_in < date('Y-m-d')) {
        $msg = ['type'=>'danger','text'=>'Tanggal masuk tidak boleh di masa lalu!'];
    } else {
        // Hitung malam & total
        $dt_in    = new DateTime($tgl_in);
        $dt_out   = new DateTime($tgl_out);
        $malam    = $dt_in->diff($dt_out)->days;
        $kamar    = $conn->query("SELECT * FROM kamar WHERE id=$kamar_id")->fetch_assoc();
        $total    = $malam * $kamar['harga'];
        $user_id  = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO pemesanan (user_id,kamar_id,nama_tamu,tanggal_masuk,tanggal_keluar,jumlah_malam,total_harga,status,catatan) VALUES (?,?,?,?,?,?,?,'aktif',?)");
        $stmt->bind_param("iisssiis", $user_id, $kamar_id, $nama_tamu, $tgl_in, $tgl_out, $malam, $total, $catatan);

        if ($stmt->execute()) {
            $success = true;
            $booking_info = [
                'nama_kamar'     => $kamar['nama_kamar'],
                'nama_tamu'      => $nama_tamu,
                'tanggal_masuk'  => date('d/m/Y', strtotime($tgl_in)),
                'tanggal_keluar' => date('d/m/Y', strtotime($tgl_out)),
                'jumlah_malam'   => $malam,
                'total_harga'    => $total,
            ];
        } else {
            $msg = ['type'=>'danger','text'=>'Gagal melakukan pemesanan, coba lagi.'];
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesan Kamar - Aston Hotel</title>
<link rel="stylesheet" href="../css/style.css">
<style>
  .calc-box {
    background: linear-gradient(135deg,#e0f7fa,#f0fdff);
    border: 2px solid rgba(13,202,240,0.25);
    border-radius: 12px;
    padding: 18px 20px;
    margin-bottom: 20px;
    display: none;
  }
  .calc-box.show { display: block; }
</style>
</head>
<body>

<nav class="navbar">
  <a class="navbar-brand" href="home.php">✦ Aston Hotel</a>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="kamar.php">Kamar</a>
    <a href="fasilitas.php">Fasilitas</a>
    <a href="booking.php" class="active">Pesan Kamar</a>
    <a href="my_booking.php">Booking Saya</a>
    <button onclick="showModalKeluar()" class="btn-keluar" style="color:white; font-size:13px; font-weight:500; padding:6px 13px; border-radius:20px; border:1px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); cursor:pointer; font-family:inherit; opacity:0.9;">Keluar</button>
  </div>
</nav>

<!-- MODAL SUCCESS -->
<?php if ($success && $booking_info): ?>
<div class="modal-overlay show" id="modalSuccess">
  <div class="modal-box">
    <div class="modal-icon">🎉</div>
    <h4>Pemesanan Berhasil!</h4>
    <p>
      <strong><?= htmlspecialchars($booking_info['nama_kamar']) ?></strong> telah dipesan atas nama
      <strong><?= htmlspecialchars($booking_info['nama_tamu']) ?></strong>.<br>
      <?= $booking_info['tanggal_masuk'] ?> → <?= $booking_info['tanggal_keluar'] ?>
      (<?= $booking_info['jumlah_malam'] ?> malam)<br><br>
      <strong style="color:#0aa8cc; font-size:18px;"><?= formatRupiah($booking_info['total_harga']) ?></strong>
    </p>
    <a href="my_booking.php" class="btn btn-success">Lihat Booking Saya</a>
    <a href="booking.php" class="btn btn-secondary" style="margin-left:8px;" onclick="document.getElementById('modalSuccess').classList.remove('show')">Pesan Lagi</a>
  </div>
</div>
<?php endif; ?>

<div class="booking-wrap">

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msg['type'] ?>" style="max-width:700px; margin:0 auto 20px;"><?= $msg['text'] ?></div>
  <?php endif; ?>

  <div class="booking-card">
    <h5>🛎️ Form Pemesanan Kamar</h5>
    <p class="booking-sub">Isi data di bawah untuk memesan kamar Anda</p>

    <form method="POST" id="bookingForm">

      <div class="form-group">
        <label>Pilih Kamar *</label>
        <select name="kamar_id" id="kamarSelect" class="form-control" required onchange="hitungTotal()">
          <option value="">-- Pilih Kamar --</option>
          <?php foreach ($kamar_all as $k): ?>
          <option value="<?= $k['id'] ?>" data-harga="<?= $k['harga'] ?>"
            <?= $preselect_kamar === $k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['nama_kamar']) ?> — <?= formatRupiah($k['harga']) ?>/malam (<?= $k['tipe'] ?>)
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Nama Tamu *</label>
        <input type="text" name="nama_tamu" class="form-control" placeholder="Nama lengkap tamu" required>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Tanggal Masuk *</label>
          <input type="date" name="tanggal_masuk" id="tglIn" class="form-control"
                 min="<?= date('Y-m-d') ?>" required onchange="hitungTotal()">
        </div>
        <div class="form-group">
          <label>Tanggal Keluar *</label>
          <input type="date" name="tanggal_keluar" id="tglOut" class="form-control"
                 min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required onchange="hitungTotal()">
        </div>
      </div>

      <div class="form-group">
        <label>Catatan Khusus</label>
        <textarea name="catatan" class="form-control" placeholder="Permintaan khusus (opsional)..."></textarea>
      </div>

      <!-- LIVE SUMMARY -->
      <div class="calc-box" id="calcBox">
        <h6 style="font-weight:700; margin-bottom:12px; color:#0a6680;">📊 Ringkasan Pemesanan</h6>
        <div class="summary-row"><span>Kamar</span><span id="sumKamar">-</span></div>
        <div class="summary-row"><span>Check In</span><span id="sumIn">-</span></div>
        <div class="summary-row"><span>Check Out</span><span id="sumOut">-</span></div>
        <div class="summary-row"><span>Jumlah Malam</span><span id="sumMalam">-</span></div>
        <div class="summary-row"><span>Harga/malam</span><span id="sumHarga">-</span></div>
        <div class="summary-row"><span>💰 Total</span><span id="sumTotal" style="color:#0aa8cc;">-</span></div>
      </div>

      <button type="submit" name="pesan" class="btn btn-success btn-full" style="font-size:15px; padding:14px;">
        🛎️ Konfirmasi Pemesanan
      </button>
    </form>
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

function formatRp(n) {
  return 'Rp ' + n.toLocaleString('id-ID');
}

function hitungTotal() {
  const sel   = document.getElementById('kamarSelect');
  const tglIn = document.getElementById('tglIn').value;
  const tglOut= document.getElementById('tglOut').value;
  const box   = document.getElementById('calcBox');

  if (!sel.value || !tglIn || !tglOut) { box.classList.remove('show'); return; }

  const harga  = parseInt(sel.options[sel.selectedIndex].dataset.harga);
  const d1     = new Date(tglIn), d2 = new Date(tglOut);
  const malam  = Math.round((d2 - d1) / 86400000);

  if (malam <= 0) { box.classList.remove('show'); return; }

  box.classList.add('show');
  document.getElementById('sumKamar').textContent = sel.options[sel.selectedIndex].text.split(' — ')[0];
  document.getElementById('sumIn').textContent    = new Date(tglIn).toLocaleDateString('id-ID');
  document.getElementById('sumOut').textContent   = new Date(tglOut).toLocaleDateString('id-ID');
  document.getElementById('sumMalam').textContent = malam + ' malam';
  document.getElementById('sumHarga').textContent = formatRp(harga);
  document.getElementById('sumTotal').textContent = formatRp(malam * harga);
}

// Auto-set min tanggal keluar saat pilih masuk
document.getElementById('tglIn').addEventListener('change', function() {
  const next = new Date(this.value);
  next.setDate(next.getDate() + 1);
  document.getElementById('tglOut').min = next.toISOString().split('T')[0];
  hitungTotal();
});

// Run on load jika ada preselect
window.addEventListener('load', hitungTotal);
</script>

</body>
</html>