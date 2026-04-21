<?php
require 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$aksi = $_GET['aksi'] ?? 'tampil';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: projects.php?pesan=dihapus");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $icon_class = $_POST['icon_class'];
    $link_url = $_POST['link_url'];

    if (isset($_POST['id']) && $_POST['id'] != '') {
        // PROSES EDIT
        $stmt = $pdo->prepare("UPDATE projects SET title=?, description=?, icon_class=?, link_url=? WHERE id=?");
        $stmt->execute([$title, $description, $icon_class, $link_url, $_POST['id']]);
        header("Location: projects.php?pesan=diedit");
    } else {
        // PROSES TAMBAH
        $stmt = $pdo->prepare("INSERT INTO projects (title, description, icon_class, link_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon_class, $link_url]);
        header("Location: projects.php?pesan=ditambah");
    }
    exit;
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'ditambah') $pesan = "Project baru berhasil ditambahkan!";
    if ($_GET['pesan'] == 'dihapus') $pesan = "Project berhasil dihapus!";
    if ($_GET['pesan'] == 'diedit') $pesan = "Project berhasil diperbarui!";
}

$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Projects - Admin</title>
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
        <a href="profil.php"><i class="fas fa-user-edit me-2"></i> Pengaturan Profil</a>
        <a href="experience.php"><i class="fas fa-briefcase me-2"></i> Kelola Experience</a>
        <a href="projects.php" class="active"><i class="fas fa-project-diagram me-2"></i> Kelola Projects</a>
        <div class="px-3 mt-4 mb-2 text-muted"><small>SELAWAS VISUAL</small></div>
        <a href="kategori.php"><i class="fas fa-folder me-2"></i> Kategori Foto</a>
        <a href="galeri.php"><i class="fas fa-images me-2"></i> Galeri Foto</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Kelola Projects</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Project</button>
        </div>
        <hr>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?= $pesan ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover m-0">
                    <thead class="table-light">
                        <tr>
                            <th>Icon</th>
                            <th>Nama Project</th>
                            <th>Deskripsi</th>
                            <th>Link URL</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $proj): ?>
                        <tr>
                            <td class="text-center fs-4 text-primary"><i class="<?= htmlspecialchars($proj['icon_class']) ?>"></i></td>
                            <td><strong><?= htmlspecialchars($proj['title']) ?></strong></td>
                            <td><small><?= htmlspecialchars($proj['description']) ?></small></td>
                            <td>
                                <?php if($proj['link_url']): ?>
                                    <a href="<?= htmlspecialchars($proj['link_url']) ?>" target="_blank" class="btn btn-sm btn-outline-info">Buka Link</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning text-dark" onclick="editData(<?= $proj['id'] ?>, '<?= htmlspecialchars(addslashes($proj['title'])) ?>', '<?= htmlspecialchars(addslashes($proj['icon_class'])) ?>', '<?= htmlspecialchars(addslashes($proj['link_url'])) ?>', `<?= htmlspecialchars(addslashes($proj['description'])) ?>`)"><i class="fas fa-edit"></i></button>
                                <a href="projects.php?aksi=hapus&id=<?= $proj['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus project ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($projects)): ?>
                            <tr><td colspan="5" class="text-center py-3">Belum ada data project.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
              <label>Nama Project</label>
              <input type="text" name="title" id="edit_title" class="form-control" required>
          </div>
          <div class="mb-3">
              <label>Icon Class (FontAwesome)</label>
              <input type="text" name="icon_class" id="edit_icon" class="form-control" required>
          </div>
          <div class="mb-3">
              <label>Link URL (Opsional)</label>
              <input type="text" name="link_url" id="edit_link" class="form-control">
          </div>
          <div class="mb-3">
              <label>Deskripsi Singkat</label>
              <textarea name="description" id="edit_desc" class="form-control" rows="3" required></textarea>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Simpan Project</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editData(id, title, icon, link, desc) {
        document.getElementById('modalTitle').innerText = 'Edit Project';
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_title').value = title;
        document.getElementById('edit_icon').value = icon;
        document.getElementById('edit_link').value = link;
        document.getElementById('edit_desc').value = desc;
        
        var modal = new bootstrap.Modal(document.getElementById('modalForm'));
        modal.show();
    }

    document.querySelector('[data-bs-target="#modalTambah"]').addEventListener('click', function() {
        document.getElementById('modalTitle').innerText = 'Tambah Project';
        document.getElementById('edit_id').value = '';
        document.getElementById('edit_title').value = '';
        document.getElementById('edit_icon').value = '';
        document.getElementById('edit_link').value = '';
        document.getElementById('edit_desc').value = '';
        var modal = new bootstrap.Modal(document.getElementById('modalForm'));
        modal.show();
    });
</script>
</body>
</html>