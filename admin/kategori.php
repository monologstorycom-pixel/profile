<?php
require 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$aksi = $_GET['aksi'] ?? 'tampil';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM slws_categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: kategori.php?pesan=dihapus");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['id_edit']) && $_POST['id_edit'] != '') {
        // PROSES EDIT KATEGORI (Hanya nama dan icon, ID gak diubah biar foto gak error)
        $stmt = $pdo->prepare("UPDATE slws_categories SET name=?, icon=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['icon'], $_POST['id_edit']]);
        header("Location: kategori.php?pesan=diedit");
        exit;
    } else {
        // PROSES TAMBAH KATEGORI
        $id_kategori = strtolower(str_replace(' ', '-', trim($_POST['name'])));
        $name = $_POST['name'];
        $icon = $_POST['icon'];
        
        $cek = $pdo->prepare("SELECT id FROM slws_categories WHERE id = ?");
        $cek->execute([$id_kategori]);
        if ($cek->rowCount() > 0) {
            $pesan_error = "Kategori dengan nama tersebut sudah ada!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO slws_categories (id, name, icon) VALUES (?, ?, ?)");
            $stmt->execute([$id_kategori, $name, $icon]);
            header("Location: kategori.php?pesan=ditambah");
            exit;
        }
    }
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'ditambah') $pesan = "Kategori baru berhasil ditambahkan!";
    if ($_GET['pesan'] == 'dihapus') $pesan = "Kategori berhasil dihapus beserta isinya!";
    if ($_GET['pesan'] == 'diedit') $pesan = "Kategori berhasil diperbarui!";
}

$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as total_foto 
    FROM slws_categories c 
    LEFT JOIN slws_photos p ON c.id = p.category_id 
    GROUP BY c.id
");
$kategori = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kategori Foto - Admin</title>
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
        <a href="projects.php"><i class="fas fa-project-diagram me-2"></i> Kelola Projects</a>
        <div class="px-3 mt-4 mb-2 text-muted"><small>SELAWAS VISUAL</small></div>
        <a href="kategori.php" class="active"><i class="fas fa-folder me-2"></i> Kategori Foto</a>
        <a href="galeri.php"><i class="fas fa-images me-2"></i> Galeri Foto</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Kategori Folder</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-folder-plus"></i> Tambah Kategori</button>
        </div>
        <hr>

        <?php if ($pesan): ?>
            <div class="alert alert-success"><?= $pesan ?></div>
        <?php endif; ?>
        <?php if (isset($pesan_error)): ?>
            <div class="alert alert-danger"><?= $pesan_error ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover m-0">
                    <thead class="table-light">
                        <tr>
                            <th>Icon</th>
                            <th>Nama Kategori</th>
                            <th>ID Kategori</th>
                            <th>Jumlah Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kategori as $kat): ?>
                        <tr>
                            <td class="text-center text-primary"><i class="fas <?= htmlspecialchars($kat['icon']) ?>"></i></td>
                            <td><strong><?= htmlspecialchars($kat['name']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($kat['id']) ?></span></td>
                            <td><?= $kat['total_foto'] ?> foto</td>
                            <td>
                                <button class="btn btn-sm btn-warning text-dark" onclick="editData('<?= $kat['id'] ?>', '<?= htmlspecialchars(addslashes($kat['name'])) ?>', '<?= htmlspecialchars(addslashes($kat['icon'])) ?>')"><i class="fas fa-edit"></i></button>
                                <a href="kategori.php?aksi=hapus&id=<?= $kat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('YAKIN? Menghapus kategori ini juga akan menghapus semua foto di dalamnya!')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($kategori)): ?>
                            <tr><td colspan="5" class="text-center py-3">Belum ada folder kategori.</td></tr>
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
        <h5 class="modal-title" id="modalTitle">Tambah Folder Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id_edit" id="edit_id">
          <div class="mb-3">
              <label>Nama Kategori</label>
              <input type="text" name="name" id="edit_name" class="form-control" required>
          </div>
          <div class="mb-3">
              <label>Icon Class (FontAwesome)</label>
              <div class="input-group">
                  <span class="input-group-text">fas</span>
                  <input type="text" name="icon" id="edit_icon" class="form-control" required>
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Simpan Kategori</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editData(id, name, icon) {
        document.getElementById('modalTitle').innerText = 'Edit Kategori';
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_icon').value = icon;
        
        var modal = new bootstrap.Modal(document.getElementById('modalForm'));
        modal.show();
    }

    document.querySelector('[data-bs-target="#modalTambah"]').addEventListener('click', function() {
        document.getElementById('modalTitle').innerText = 'Tambah Folder Kategori';
        document.getElementById('edit_id').value = '';
        document.getElementById('edit_name').value = '';
        document.getElementById('edit_icon').value = '';
        var modal = new bootstrap.Modal(document.getElementById('modalForm'));
        modal.show();
    });
</script>
</body>
</html>