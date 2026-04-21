<?php
require 'koneksi.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #343a40; color: #fff; border-left: 4px solid #198754; }
    </style>
</head>
<body class="bg-light">

<div class="d-flex">
    <div class="sidebar text-white" style="width: 250px;">
        <div class="p-3 text-center border-bottom border-secondary mb-3">
            <h5 class="m-0">Admin Panel</h5>
            <small class="text-success">Online</small>
        </div>
        <a href="index.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="profil.php"><i class="fas fa-user-edit me-2"></i> Pengaturan Profil</a>
        <a href="#"><i class="fas fa-briefcase me-2"></i> Kelola Experience</a>
        <a href="#"><i class="fas fa-project-diagram me-2"></i> Kelola Projects</a>
        <div class="px-3 mt-4 mb-2 text-muted"><small>SELAWAS VISUAL</small></div>
        <a href="#"><i class="fas fa-folder me-2"></i> Kategori Foto</a>
        <a href="#"><i class="fas fa-images me-2"></i> Galeri Foto</a>
        
        <div class="mt-5">
            <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Dashboard</h4>
            <span>Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-briefcase"></i> Experience</h5>
                        <p class="card-text fs-4">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-project-diagram"></i> Projects</h5>
                        <p class="card-text fs-4">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-images"></i> Foto Portfolio</h5>
                        <p class="card-text fs-4">0</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body">
                <h5>Selamat Datang di Admin Panel</h5>
                <p class="text-muted">Gunakan menu di sebelah kiri untuk mengatur konten website kamu. Semua perubahan di sini akan langsung ter-update di halaman depan.</p>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>