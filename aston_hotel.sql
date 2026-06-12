-- =============================================
-- DATABASE: aston_hotel
-- Import via phpMyAdmin atau: mysql -u root -p < aston_hotel.sql
-- =============================================

CREATE DATABASE IF NOT EXISTS aston_hotel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aston_hotel;

-- ── TABLE: users ──
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  alamat TEXT,
  telepon VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── TABLE: kamar ──
CREATE TABLE kamar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_kamar VARCHAR(100) NOT NULL,
  tipe ENUM('Deluxe','Superior','Standard') NOT NULL,
  harga INT NOT NULL,
  deskripsi TEXT,
  status ENUM('tersedia','tidak tersedia') DEFAULT 'tersedia',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── TABLE: fasilitas ──
CREATE TABLE fasilitas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  icon VARCHAR(10) DEFAULT '⭐',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── TABLE: pemesanan (Data Pesan) ──
CREATE TABLE pemesanan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  kamar_id INT NOT NULL,
  nama_tamu VARCHAR(100) NOT NULL,
  tanggal_masuk DATE NOT NULL,
  tanggal_keluar DATE NOT NULL,
  jumlah_malam INT NOT NULL,
  total_harga INT NOT NULL,
  status ENUM('aktif','selesai','dibatalkan') DEFAULT 'aktif',
  catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (kamar_id) REFERENCES kamar(id) ON DELETE CASCADE
);

-- ── SEED DATA: users ──
INSERT INTO users (username, password, role, alamat, telepon) VALUES
('admin', '123', 'admin', 'Jl. Hotel No.1, Jakarta', '08123456789'),
('lisa', '123', 'pelanggan', 'Jl. Mawar No.5, Bandung', '08987654321'),
('budi', '123', 'pelanggan', 'Jl. Melati No.10, Surabaya', '08112233445');

-- ── SEED DATA: kamar ──
INSERT INTO kamar (nama_kamar, tipe, harga, deskripsi, status) VALUES
('Deluxe Room', 'Deluxe', 850000, 'Kamar mewah dengan pemandangan kota, kasur king size, dan fasilitas premium.', 'tersedia'),
('Superior Room', 'Superior', 600000, 'Kamar nyaman dengan desain modern, cocok untuk pasangan atau perjalanan bisnis.', 'tersedia'),
('Standard Room', 'Standard', 350000, 'Kamar standar yang bersih dan nyaman dengan fasilitas lengkap.', 'tersedia'),
('Deluxe Suite', 'Deluxe', 1200000, 'Suite mewah dengan ruang tamu terpisah dan pemandangan spektakuler.', 'tersedia'),
('Superior Twin', 'Superior', 650000, 'Kamar superior dengan dua kasur single, cocok untuk teman perjalanan.', 'tersedia');

-- ── SEED DATA: fasilitas ──
INSERT INTO fasilitas (nama, deskripsi, icon) VALUES
('WiFi Gratis', 'Koneksi internet cepat di seluruh area hotel 24 jam.', '📶'),
('Swimming Pool', 'Kolam renang outdoor dengan pemandangan indah.', '🏊'),
('Restaurant', 'Restoran dengan menu lokal dan internasional.', '🍽️'),
('Parkir Gratis', 'Area parkir luas dan aman untuk tamu hotel.', '🚗'),
('Laundry', 'Layanan laundry cepat dan profesional.', '👕'),
('AC & TV', 'Setiap kamar dilengkapi AC dan TV layar datar.', '❄️');

-- ── SEED DATA: pemesanan ──
INSERT INTO pemesanan (user_id, kamar_id, nama_tamu, tanggal_masuk, tanggal_keluar, jumlah_malam, total_harga, status, catatan) VALUES
(2, 1, 'Lisa Maharani', '2025-04-05', '2025-04-08', 3, 2550000, 'aktif', 'Request kamar di lantai tinggi'),
(3, 3, 'Budi Santoso', '2025-04-01', '2025-04-03', 2, 700000, 'selesai', '');
