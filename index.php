<?php
require_once 'includes/config.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/home.php');
    } else {
        header('Location: user/home.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($conn, isset($_POST['username']) ? $_POST['username'] : '');
    $password = sanitize($conn, isset($_POST['password']) ? $_POST['password'] : '');

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin/home.php');
            } else {
                header('Location: user/home.php');
            }
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
        $stmt->close();
    } else {
        $error = 'Harap isi semua kolom!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Aston Hotel</title>
<link rel="stylesheet" href="css/style.css">
<style>
.input-group { position: relative; }
.toggle-pass {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    user-select: none;
    color: #888;
}
</style>
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <h3>🏨 Aston Hotel</h3>
    <div class="auth-subtitle">Silakan login untuk melanjutkan</div>
    <div class="auth-divider"></div>

    <?php if ($error): ?>
      <div class="alert alert-danger">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <div class="input-group">
          <span class="input-icon">👤</span>
          <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
        </div>
      </div>

      <div class="form-group">
        <label>Password</label>
        <div class="input-group">
          <span class="input-icon">🔒</span>
          <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Masukkan password..." required>
          <span class="toggle-pass" onclick="togglePassword()">👁️</span>
        </div>
      </div>

      <button type="submit" class="btn btn-success btn-full">LOGIN</button>
    </form>

    <a href="register.php" class="btn btn-info btn-full" style="margin-top:0;">REGISTER</a>

  </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('passwordInput');
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}
</script>

</body>
</html>