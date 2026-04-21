<?php
require 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah data profil sudah ada, kalau belum buat baris kosong
$stmt = $pdo->query("SELECT * FROM profile_settings LIMIT 1");
$profil = $stmt->fetch();
if (!$profil) {
    $pdo->query("INSERT INTO profile_settings (full_name, tagline) VALUES ('Rizqi Subagyo', 'IT Support Specialist | Full-stack Developer')");
    $profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch();
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $tagline = $_POST['tagline'];
    $availability_status = $_POST['availability_status'];
    $email = $_POST['email'];
    $github_link = $_POST['github_link'];
    $linkedin_link = $_POST['linkedin_link'];
    
    $profile_picture = $profil['profile_picture']; // Default pakai foto lama

    // Proses upload foto kalau ada
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $folder_upload = '../uploads/';
        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0777, true); // Buat folder otomatis kalau belum ada
        }
        
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file_baru = 'profil_' . time() . '.' . $ext;
        $path_simpan = $folder_upload . $nama_file_baru;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $path_simpan)) {
            $profile_picture = 'uploads/' . $nama_file_baru; // Path untuk di database
        }
    }

    // Update ke database
    $update = $pdo->prepare("UPDATE profile_settings SET full_name=?, tagline=?, availability_status=?, email=?, github_link=?, linkedin_link=?, profile_picture=? WHERE id=?");
    $update->execute([$full_name, $tagline, $availability_status, $email, $github_link, $linkedin_link, $profile_picture, $profil['id']]);
    
    $pesan = "Data profil berhasil diperbarui!";
    $profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch(); // Refresh data
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Profil - Admin</title>
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
        <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="profil.php" class="active"><i class="fas fa-user-edit me-2"></i> Pengaturan Profil</a>
        <a href="#"><i class="fas fa-briefcase me-2"></i> Kelola Experience</a>
        <a href="#"><i class="fas fa-project-diagram me-2"></i> Kelola Projects</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        <h4>Pengaturan Profil</h4>
        <hr>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?= $pesan ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label>Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($profil['full_name'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Tagline / Posisi</label>
                                <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($profil['tagline'] ?? '') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label>Status Ketersediaan</label>
                                <input type="text" name="availability_status" class="form-control" value="<?= htmlspecialchars($profil['availability_status'] ?? 'Tersedia untuk proyek baru') ?>">
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profil['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Link GitHub</label>
                                    <input type="text" name="github_link" class="form-control" value="<?= htmlspecialchars($profil['github_link'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>Link LinkedIn</label>
                                    <input type="text" name="linkedin_link" class="form-control" value="<?= htmlspecialchars($profil['linkedin_link'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <label class="d-block mb-2">Foto Profil Saat Ini</label>
                            <?php if (!empty($profil['profile_picture'])): ?>
                                <img src="../<?= $profil['profile_picture'] ?>" alt="Profil" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded-circle mx-auto mb-3" style="width: 150px; height: 150px;">Belum Ada Foto</div>
                            <?php endif; ?>
                            
                            <input type="file" name="foto" class="form-control" accept="image/png, image/jpeg, image/jpg">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>