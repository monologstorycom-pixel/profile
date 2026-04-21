<?php
require 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$aksi = $_GET['aksi'] ?? 'tampil';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM experiences WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: experience.php?pesan=dihapus");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title = $_POST['job_title'];
    $company = $_POST['company'];
    $year_range = $_POST['year_range'];
    $description = $_POST['description'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (isset($_POST['id']) && $_POST['id'] != '') {
        // PROSES EDIT
        $stmt = $pdo->prepare("UPDATE experiences SET job_title=?, company=?, year_range=?, description=?, is_active=? WHERE id=?");
        $stmt->execute([$job_title, $company, $year_range, $description, $is_active, $_POST['id']]);
        header("Location: experience.php?pesan=diedit");
    } else {
        // PROSES TAMBAH
        $stmt = $pdo->prepare("INSERT INTO experiences (job_title, company, year_range, description, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$job_title, $company, $year_range, $description, $is_active]);
        header("Location: experience.php?pesan=ditambah");
    }
    exit;
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'ditambah') $pesan = "Experience baru berhasil ditambahkan!";
    if ($_GET['pesan'] == 'dihapus') $pesan = "Experience berhasil dihapus!";
    if ($_GET['pesan'] == 'diedit') $pesan = "Experience berhasil diperbarui!";
}

$stmt = $pdo->query("SELECT * FROM experiences ORDER BY id DESC");
$experiences = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Experience - Admin</title>
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
        <a href="experience.php" class="active"><i class="fas fa-briefcase me-2"></i> Kelola Experience</a>
        <a href="projects.php"><i class="fas fa-project-diagram me-2"></i> Kelola Projects</a>
        <div class="px-3 mt-4 mb-2 text-muted"><small>SELAWAS VISUAL</small></div>
        <a href="kategori.php"><i class="fas fa-folder me-2"></i> Kategori Foto</a>
        <a href="galeri.php"><i class="fas fa-images me-2"></i> Galeri Foto</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Kelola Experience</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Experience</button>
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
                            <th>Tahun</th>
                            <th>Jabatan</th>
                            <th>Perusahaan</th>
                            <th>Status Active</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($experiences as $exp): ?>
                        <tr>
                            <td><?= htmlspecialchars($exp['year_range']) ?></td>
                            <td><strong><?= htmlspecialchars($exp['job_title']) ?></strong></td>
                            <td><?= htmlspecialchars($exp['company']) ?></td>
                            <td>
                                <?php if($exp['is_active']): ?>
                                    <span class="badge bg-success">Aktif / Pekerjaan Saat Ini</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Masa Lalu</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning text-dark" onclick="editData(<?= $exp['id'] ?>, '<?= htmlspecialchars(addslashes($exp['job_title'])) ?>', '<?= htmlspecialchars(addslashes($exp['company'])) ?>', '<?= htmlspecialchars(addslashes($exp['year_range'])) ?>', `<?= htmlspecialchars(addslashes($exp['description'])) ?>`, <?= $exp['is_active'] ?>)"><i class="fas fa-edit"></i></button>
                                <a href="experience.php?aksi=hapus&id=<?= $exp['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($experiences)): ?>
                            <tr><td colspan="5" class="text-center py-3">Belum ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Riwayat Kerja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">
          <div class="row">
              <div class="col-md-6 mb-3">
                  <label>Jabatan / Job Title</label>
                  <input type="text" name="job_title" id="edit_job" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                  <label>Nama Perusahaan</label>
                  <input type="text" name="company" id="edit_company" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                  <label>Tahun (Range)</label>
                  <input type="text" name="year_range" id="edit_year" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3 d-flex align-items-end">
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active">
                    <label class="form-check-label" for="edit_active">
                      Ini adalah pekerjaan saat ini
                    </label>
                  </div>
              </div>
              <div class="col-12 mb-3">
                  <label>Deskripsi Tugas (Pisahkan tiap poin dengan baris baru / Enter)</label>
                  <textarea name="description" id="edit_desc" class="form-control" rows="5"></textarea>
              </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">Simpan Data</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editData(id, job, company, year, desc, isActive) {
        document.getElementById('modalTitle').innerText = 'Edit Riwayat Kerja';
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_job').value = job;
        document.getElementById('edit_company').value = company;
        document.getElementById('edit_year').value = year;
        document.getElementById('edit_desc').value = desc;
        document.getElementById('edit_active').checked = (isActive == 1);
        
        var modal = new bootstrap.Modal(document.getElementById('modalForm'));
        modal.show();
    }

    // Reset form saat tombol tambah diklik (karena modalnya sama)
    document.querySelector('[data-bs-target="#modalTambah"]').addEventListener('click', function() {
        document.getElementById('modalTitle').innerText = 'Tambah Riwayat Kerja';
        document.getElementById('edit_id').value = '';
        document.getElementById('edit_job').value = '';
        document.getElementById('edit_company').value = '';
        document.getElementById('edit_year').value = '';
        document.getElementById('edit_desc').value = '';
        document.getElementById('edit_active').checked = false;
        var modal = new bootstrap.Modal(document.getElementById('modalForm'));
        modal.show();
    });
</script>
</body>
</html>