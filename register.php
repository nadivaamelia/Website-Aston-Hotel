<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header('Location: index.php'); exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, isset($_POST['username']) ? $_POST['username'] : '');
    $password = sanitize($conn, isset($_POST['password']) ? $_POST['password'] : '');
    $alamat   = sanitize($conn, isset($_POST['alamat']) ? $_POST['alamat'] : '');
    $telepon  = sanitize($conn, isset($_POST['telepon']) ? $_POST['telepon'] : '');

    if (!$username || !$password || !$alamat || !$telepon) {
        $error = 'Semua kolom wajib diisi!';
    } else {
        // Cek username sudah ada
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Username sudah digunakan, pilih yang lain!';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, alamat, telepon) VALUES (?, ?, 'pelanggan', ?, ?)");
            $stmt->bind_param("ssss", $username, $password, $alamat, $telepon);
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal registrasi, coba lagi.';
            }
            $stmt->close();
        }
        $cek->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Aston Hotel</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <h3>📝 Form Register</h3>
    <div class="auth-subtitle">Daftar sebagai pelanggan baru</div>
    <div class="auth-divider"></div>

    <?php if ($error): ?>
      <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control" placeholder="Buat username unik..." required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Buat password..." required>
      </div>
      <div class="form-group">
        <label>Alamat Lengkap</label>
        <textarea name="alamat" class="form-control" placeholder="Masukkan alamat lengkap..." required></textarea>
      </div>
      <div class="form-group">
        <label>Nomor Telepon</label>
        <input type="text" name="telepon" class="form-control" placeholder="Contoh: 08123456789" required>
      </div>

      <button type="submit" class="btn btn-success btn-full">DAFTAR</button>
      <a href="index.php" class="btn btn-secondary btn-full">Kembali ke Login</a>
    </form>

    <div style="text-align:center; margin-top:14px; font-size:13px; color:#888;">
      Sudah punya akun? <a href="index.php" style="color:#0aa8cc; font-weight:600;">Login di sini</a>
    </div>
  </div>
</div>

</body>
</html>