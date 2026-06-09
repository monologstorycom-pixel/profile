# 🎨 Rsby — Portfolio App

> Aplikasi portfolio berbasis PHP & MySQL/MariaDB yang dinamis, dilengkapi panel admin untuk mengelola konten secara mudah.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/status-active-brightgreen?style=flat-square)

---

## ✨ Fitur

### 🌐 Halaman Publik
- Landing page modern dengan dark/light theme persisten
- Section: About, Experience timeline, Projects, Video showcase, Skills, Clients, Contact
- Smooth scroll, sticky nav dengan active section indicator
- WhatsApp & email FAB di mobile
- SEO-ready: JSON-LD Person schema, OG/Twitter meta, sitemap dinamis
- PWA-ready dengan manifest

### 📷 SELAWAS VISUAL (`/slws`)
- Folder kategori foto dengan lazy-load on demand
- Lightbox dengan keyboard nav + swipe mobile
- YouTube video gallery dengan lazy embed (klik untuk play)

### 🔐 Panel Admin (`/admin`)
- Auth gate via `_auth.php` (session + idle timeout 2 jam + regenerate ID)
- CSRF protection di semua form & link delete
- Rate-limit login (5 attempts → lockout)
- Dashboard dengan completeness check & uptime monitor
- CRUD: Profil, Experience, Projects, Skills, Clients, Video, Kategori, Galeri
- Upload galeri dengan client-side compress + server-side resize 1920px
- Upload validation: MIME asli + filename random + path traversal guard

---

## ⚙️ Persyaratan

| Kebutuhan       | Versi Minimum    |
|-----------------|------------------|
| PHP             | 8.0+             |
| MySQL/MariaDB   | 5.7+             |
| Web Server      | Apache / Nginx   |
| Extension PHP   | `pdo_mysql`, `gd`, `fileinfo` |

---

## 🚀 Instalasi

```bash
# 1. Clone
git clone https://github.com/monologstorycom-pixel/profile.git
cd profile

# 2. Import database
mysql -u root -p db_portfolio < db_portfolio.sql

# (jika sudah punya DB lama dari versi sebelumnya, cukup jalankan migrasi)
mysql -u root -p db_portfolio < migrations.sql
```

## 🔧 Konfigurasi DB

Edit `admin/koneksi.php` atau gunakan environment variable:

```bash
DB_HOST=192.168.1.109
DB_NAME=db_portfolio
DB_USER=kasir
DB_PASS=kasir
```

## 🔑 Default Admin

Setelah import database, buka di browser:

```
http://localhost/profile/admin/setup.php
```

Halaman akan minta username + password. **File ini auto-lock setelah user pertama dibuat** dan menyediakan tombol untuk menghapus dirinya sendiri.

> Kalau lupa password, hapus row di tabel `admin_users` lewat phpMyAdmin, lalu setup ulang akan terbuka kembali.

---

## 📁 Struktur

```
profile/
├── admin/                # Panel admin
│   ├── _auth.php         # Auth gate + CSRF helper
│   ├── _layout.php       # Sidebar + topbar shared
│   ├── koneksi.php       # PDO connection
│   ├── login.php         # Login dengan rate-limit
│   └── ...
├── slws/                 # Selawas Visual gallery
├── uploads/              # File upload (PHP execution disabled via .htaccess)
├── index.php             # Landing page publik
├── sitemap.php           # Sitemap dinamis
├── manifest.json         # PWA manifest
├── db_portfolio.sql      # Skema lengkap
└── migrations.sql        # Patch untuk DB lama
```

---

## 🔒 Hardening

- ✅ Prepared statements di semua query (PDO)
- ✅ CSRF token di POST & GET delete
- ✅ Session: HttpOnly, SameSite=Lax, regenerate periodically
- ✅ Login lockout setelah 5x gagal
- ✅ Path traversal guard di file delete
- ✅ MIME validation di upload + ekstensi disesuaikan dari MIME asli
- ✅ `.htaccess` di `/uploads/` blokir eksekusi PHP/CGI
- ✅ Output escape via `htmlspecialchars` / helper `e()`

---

## 📜 Lisensi

MIT License © Rizqi Subagyo
