# 🎨 Monolog Story — Portfolio App

> Aplikasi portfolio berbasis PHP & MySQL yang dinamis, dilengkapi panel admin untuk mengelola konten secara mudah.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/status-active-brightgreen?style=flat-square)

---

## 📋 Daftar Isi

- [Tentang Aplikasi](#-tentang-aplikasi)
- [Fitur](#-fitur)
- [Struktur Folder](#-struktur-folder)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi Database](#-konfigurasi-database)
- [Penggunaan](#-penggunaan)
- [Keamanan](#-keamanan)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

---

## 📖 Tentang Aplikasi

**Monolog Story Portfolio** adalah aplikasi web portfolio yang dibangun menggunakan **PHP native** dan **MySQL**. Aplikasi ini memungkinkan pemilik untuk menampilkan karya, proyek, dan informasi diri secara profesional melalui halaman publik, serta mengelola semua konten melalui panel admin yang aman.

---

## ✨ Fitur

### 🌐 Halaman Publik
- Tampilan portfolio yang bersih dan responsif
- Galeri karya / proyek
- Halaman tentang diri
- Form kontak

### 🔐 Panel Admin (`/admin`)
- Login aman dengan autentikasi
- Kelola data portfolio (tambah, edit, hapus)
- Upload & manajemen file gambar (`/uploads`)
- Dashboard statistik

---

## 📁 Struktur Folder

```
profile/
├── admin/              # Panel admin (protected)
│   ├── index.php       # Dashboard admin
│   ├── login.php       # Halaman login
│   └── ...
├── slws/               # Library / helper
├── uploads/            # File gambar yang diupload
├── index.php           # Halaman utama (publik)
├── db_portfolio.sql    # Skema & data awal database
├── .gitattributes
└── README.md
```

---

## ⚙️ Persyaratan Sistem

Pastikan server/lokal kamu memiliki:

| Kebutuhan | Versi Minimum |
|-----------|---------------|
| PHP | >= 7.4 |
| MySQL | >= 5.7 |
| Web Server | Apache / Nginx |
| Extension PHP | `mysqli`, `pdo`, `gd`, `fileinfo` |

> 💡 Disarankan menggunakan **XAMPP**, **Laragon**, atau **WAMP** untuk development lokal.

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/monologstorycom-pixel/profile.git
cd profile
```

### 2. Pindahkan ke Folder Web Server

**XAMPP:**
```bash
# Windows
xcopy /E /I profile C:\xampp\htdocs\profile

# Linux/Mac
cp -r profile /opt/lampp/htdocs/profile
```

**Laragon:**
```
Salin folder ke: C:\laragon\www\profile
```

### 3. Import Database

- Buka **phpMyAdmin** di browser: `http://localhost/phpmyadmin`
- Buat database baru, contoh: `db_portfolio`
- Pilih tab **Import** → pilih file `db_portfolio.sql` → klik **Go**

### 4. Konfigurasi Koneksi Database

Buka file konfigurasi database (biasanya di `slws/` atau root), lalu sesuaikan:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // username MySQL kamu
define('DB_PASS', '');             // password MySQL kamu
define('DB_NAME', 'db_portfolio'); // nama database
?>
```

### 5. Jalankan Aplikasi

Buka browser dan akses:

```
http://localhost/profile
```

Panel admin:
```
http://localhost/profile/admin
```

---

## 🗄️ Konfigurasi Database

Database menggunakan file `db_portfolio.sql`. File ini berisi:
- Skema tabel (struktur database)
- Data awal / dummy data (jika ada)

Untuk **reset database**, cukup drop database lama dan import ulang file `db_portfolio.sql`.

---

## 🖥️ Penggunaan

### Akses Halaman Publik
Buka `http://localhost/profile` untuk melihat tampilan portfolio.

### Login Admin
1. Buka `http://localhost/profile/admin`
2. Masukkan username & password
3. Kelola konten portfolio dari dashboard

> ⚠️ **Penting:** Segera ganti password default setelah pertama kali login!

### Upload File
- File yang diupload tersimpan di folder `/uploads`
- Pastikan folder `/uploads` memiliki **permission write**

```bash
chmod 755 uploads/
```

---

## 🔒 Keamanan

Beberapa hal yang disarankan sebelum deploy ke production:

- [ ] Ganti password admin default
- [ ] Nonaktifkan error reporting PHP di production
- [ ] Batasi akses folder `/admin` dengan `.htaccess`
- [ ] Validasi & sanitasi semua input form
- [ ] Gunakan HTTPS

Contoh `.htaccess` untuk proteksi folder admin:
```apache
AuthType Basic
AuthName "Admin Area"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

---

## 🛠️ Teknologi yang Digunakan

- **PHP** — Backend logic
- **MySQL** — Database
- **HTML/CSS** — Frontend tampilan
- **JavaScript** — Interaksi UI
- **Apache** — Web server

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Berikut caranya:

1. **Fork** repository ini
2. Buat branch baru: `git checkout -b fitur/nama-fitur`
3. Commit perubahan: `git commit -m 'Tambah fitur baru'`
4. Push ke branch: `git push origin fitur/nama-fitur`
5. Buat **Pull Request**

---

## 🐛 Melaporkan Bug

Temukan bug? Silakan buat [Issue baru](https://github.com/monologstorycom-pixel/profile/issues) dengan menyertakan:
- Deskripsi bug
- Langkah untuk mereproduksi
- Screenshot (jika ada)
- Versi PHP & MySQL yang digunakan

---

## 📄 Lisensi

Proyek ini menggunakan lisensi **MIT**. Lihat file [LICENSE](LICENSE) untuk detail lengkap.

---

## 📬 Kontak

**Monolog Story**

- 🌐 Website: [monologstory.com](https://monologstory.com)
- 🐙 GitHub: [@monologstorycom-pixel](https://github.com/monologstorycom-pixel)

---

<div align="center">
  Made with ❤️ by <a href="https://github.com/monologstorycom-pixel">Monolog Story</a>
</div>
