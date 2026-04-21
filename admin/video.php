<?php
require 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$aksi = $_GET['aksi'] ?? 'tampil';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: video.php?pesan=dihapus");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $url = $_POST['video_url'];
    $description = $_POST['description'];

    if (isset($_POST['id']) && $_POST['id'] != '') {
        $stmt = $pdo->prepare("UPDATE videos SET title=?, video_url=?, description=? WHERE id=?");
        $stmt->execute([$title, $url, $description, $_POST['id']]);
        header("Location: video.php?pesan=diedit");
    } else {
        $stmt = $pdo->prepare("INSERT INTO videos (title, video_url, description) VALUES (?, ?, ?)");
        $stmt->execute([$title, $url, $description]);
        header("Location: video.php?pesan=ditambah");
    }
    exit;
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'ditambah') $pesan = "Video berhasil ditambahkan!";
    if ($_GET['pesan'] == 'dihapus') $pesan = "Video berhasil dihapus!";
    if ($_GET['pesan'] == 'diedit') $pesan = "Video berhasil diperbarui!";
}

$videos = $pdo->query("SELECT * FROM videos ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Video - Admin</title>
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
        </div>
        <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="profil.php"><i class="fas fa-user-edit me-2"></i> Profil</a>
        <a href="experience.php"><i class="fas fa-briefcase me-2"></i> Experience</a>
        <a href="projects.php"><i class="fas fa-project-diagram me-2"></i> Projects</a>
        <a href="video.php" class="active"><i class="fas fa-video me-2"></i> Video Portfolio</a>
        <div class="px-3 mt-4 mb-2 text-muted"><small>SELAWAS VISUAL</small></div>
        <a href="kategori.php"><i class="fas fa-folder me-2"></i> Kategori Foto</a>
        <a href="galeri.php"><i class="fas fa-images me-2"></i> Galeri Foto</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Video Portfolio</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalVideo"><i class="fas fa-plus"></i> Tambah Video</button>
        </div>
        <hr>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?= $pesan ?></div>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($videos as $v): ?>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6><?= htmlspecialchars($v['title']) ?></h6>
                        <p class="small text-muted mb-2"><?= htmlspecialchars($v['description']) ?></p>
                        <div class="btn-group w-100">
                            <button class="btn btn-sm btn-warning" onclick="editVideo(<?= $v['id'] ?>, '<?= htmlspecialchars(addslashes($v['title'])) ?>', '<?= htmlspecialchars(addslashes($v['video_url'])) ?>', '<?= htmlspecialchars(addslashes($v['description'])) ?>')">Edit</button>
                            <a href="video.php?aksi=hapus&id=<?= $v['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus video ini?')">Hapus</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVideo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="vModalTitle">Tambah Video</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="v_id">
          <div class="mb-3">
              <label>Judul Video</label>
              <input type="text" name="title" id="v_title" class="form-control" required>
          </div>
          <div class="mb-3">
              <label>URL Video (YouTube Link)</label>
              <input type="text" name="video_url" id="v_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
          </div>
          <div class="mb-3">
              <label>Deskripsi Singkat</label>
              <textarea name="description" id="v_desc" class="form-control" rows="3"></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Simpan Video</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editVideo(id, title, url, desc) {
        document.getElementById('vModalTitle').innerText = 'Edit Video';
        document.getElementById('v_id').value = id;
        document.getElementById('v_title').value = title;
        document.getElementById('v_url').value = url;
        document.getElementById('v_desc').value = desc;
        new bootstrap.Modal(document.getElementById('modalVideo')).show();
    }
</script>
</body>
</html>