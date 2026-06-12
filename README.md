# 🏨 Aston Hotel - Web App (PHP + MySQL)

## 📁 Struktur Folder

```
aston_hotel/
├── index.php              ← Halaman Login
├── register.php           ← Halaman Register
├── logout.php             ← Proses Logout
├── aston_hotel.sql        ← File Database (import ke phpMyAdmin)
├── css/
│   └── style.css          ← CSS utama
├── includes/
│   └── config.php         ← Koneksi DB & helper functions
├── admin/
│   ├── home.php           ← Dashboard Admin
│   ├── kamar.php          ← Kelola Kamar (CRUD)
│   ├── fasilitas.php      ← Kelola Fasilitas (CRUD)
│   ├── data_pesan.php     ← Kelola Data Pemesanan
│   ├── data_pelanggan.php ← Kelola Data Pelanggan
│   └── data_booking.php   ← Laporan Booking
└── user/
    ├── home.php           ← Beranda User
    ├── kamar.php          ← Lihat Kamar
    ├── fasilitas.php      ← Lihat Fasilitas
    ├── booking.php        ← Form Pesan Kamar
    └── my_booking.php     ← Riwayat Booking Saya
```

---

## 🚀 Cara Install & Menjalankan

### 1. Persiapkan Environment
- Pastikan **XAMPP / WAMP / Laragon** sudah terinstall
- PHP 7.4+ dan MySQL aktif

### 2. Tempatkan File
Salin folder `aston_hotel` ke:
- XAMPP: `C:/xampp/htdocs/aston_hotel/`
- WAMP: `C:/wamp64/www/aston_hotel/`
- Laragon: `C:/laragon/www/aston_hotel/`

### 3. Import Database
1. Buka **phpMyAdmin** → http://localhost/phpmyadmin
2. Klik **"Import"** di menu atas
3. Pilih file `aston_hotel.sql`
4. Klik **"Go"** / **"Impor"**

### 4. Sesuaikan Koneksi Database
Edit file `includes/config.php` jika perlu:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // username MySQL Anda
define('DB_PASS', '');       // password MySQL (kosong = tidak ada)
define('DB_NAME', 'aston_hotel');
```

### 5. Buka di Browser
```
http://localhost/aston_hotel/
```

---

## 👤 Akun Demo

| Username | Password | Role    |
|----------|----------|---------|
| admin    | 123      | Admin   |
| lisa     | 123      | Pelanggan |
| budi     | 123      | Pelanggan |

---

## ✨ Fitur Lengkap

### ADMIN
- ✅ Login / Logout
- ✅ Dashboard dengan statistik (kamar, pelanggan, booking)
- ✅ **Kelola Kamar** — Tambah, Edit, Hapus kamar
- ✅ **Kelola Fasilitas** — Tambah, Edit, Hapus fasilitas
- ✅ **Data Pesan** — Lihat & ubah status pemesanan
- ✅ **Data Pelanggan** — Lihat & hapus akun pelanggan
- ✅ **Data Booking** — Laporan lengkap + filter status

### USER / PELANGGAN
- ✅ Login / Register / Logout
- ✅ Halaman beranda dengan info hotel
- ✅ Lihat daftar kamar + filter per tipe
- ✅ Lihat fasilitas hotel
- ✅ **Pesan Kamar** — Form booking dengan kalkulasi otomatis
- ✅ **Booking Saya** — Riwayat & batalkan pemesanan

---

## 🛠️ Teknologi

- **Backend**: PHP 8+ (Native, tanpa framework)
- **Database**: MySQL via phpMyAdmin
- **Frontend**: HTML5, CSS3 (custom), Vanilla JS
- **Font**: Google Fonts (Playfair Display, DM Sans)
- **Server**: XAMPP/WAMP lokal

---

## 📝 Catatan

- Password disimpan plain text (untuk demo). Pada produksi gunakan `password_hash()` & `password_verify()`
- Tidak menggunakan framework (murni PHP native)
- Semua halaman menggunakan session PHP untuk autentikasi
