<?php
// ── KONFIGURASI DATABASE ──
// Sesuaikan dengan pengaturan MySQL lokal Anda

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Ganti jika berbeda
define('DB_PASS', '');            // Ganti jika ada password
define('DB_NAME', 'aston_hotel');

// ── SESSION (harus sebelum output apapun) ──
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;color:red;padding:20px;">
        ❌ Koneksi Database Gagal: ' . $conn->connect_error . '
        <br><small>Periksa config di includes/config.php</small>
    </div>');
}

$conn->set_charset('utf8mb4');

// ── HELPER FUNCTIONS ──
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit;
    }
}

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) {
        return [];
    }
    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }
    return $rows;
}

function safeQueryOne($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) return null;
    return $result->fetch_assoc();
}

function safeCount($conn, $sql) {
    $result = $conn->query($sql);
    if ($result === false) return 0;
    $row = $result->fetch_assoc();
    return isset($row['c']) ? (int)$row['c'] : 0;
}
?>