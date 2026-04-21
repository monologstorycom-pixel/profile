<?php
require 'koneksi.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$aksi = $_GET['aksi'] ?? 'tampil';
$pesan = '';

// Proses Hapus Foto (Satuan)
if ($aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM slws_photos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $foto = $stmt->fetch();
    
    if ($foto) {
        $file_fisik = '../' . $foto['image_path'];
        if (file_exists($file_fisik)) {
            unlink($file_fisik); 
        }
        $pdo->prepare("DELETE FROM slws_photos WHERE id = ?")->execute([$_GET['id']]);
        
        // Kembalikan ke halaman filter sebelumnya (kalau ada)
        $kembali = isset($_GET['kat']) ? "?kat=" . $_GET['kat'] . "&pesan=dihapus" : "?pesan=dihapus";
        header("Location: galeri.php" . $kembali);
        exit;
    }
}

// Proses Upload & Auto Compress Foto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fotos'])) {
    $category_id = $_POST['category_id'];
    $folder_upload = '../uploads/galeri/';
    
    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    function compressAndResize($source, $destination, $quality) {
        $info = getimagesize($source);
        if (!$info) return false;
        
        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];
        
        $maxWidth = 1920; 
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = floor($height * ($maxWidth / $width));
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }
        
        $image_p = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($mime == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($mime == 'image/png') {
            $image = imagecreatefrompng($source);
            $bg = imagecolorallocate($image_p, 255, 255, 255);
            imagefill($image_p, 0, 0, $bg);
        } else {
            return move_uploaded_file($source, $destination);
        }
        
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $result = imagejpeg($image_p, $destination, $quality);
        
        imagedestroy($image);
        imagedestroy($image_p);
        
        return $result;
    }

    $berhasil = 0;
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['fotos']['error'][$key] == 0) {
            $nama_file_baru = 'slws_' . time() . '_' . uniqid() . '.jpg';
            $path_simpan = $folder_upload . $nama_file_baru;

            if (compressAndResize($tmp_name, $path_simpan, 75)) {
                $image_path = 'uploads/galeri/' . $nama_file_baru;
                $stmt = $pdo->prepare("INSERT INTO slws_photos (category_id, image_path) VALUES (?, ?)");
                $stmt->execute([$category_id, $image_path]);
                $berhasil++;
            }
        }
    }
    header("Location: galeri.php?pesan=diupload&jml=" . $berhasil . "&kat=" . $category_id);
    exit;
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'diupload') $pesan = $_GET['jml'] . " Foto berhasil di-compress dan diupload!";
    if ($_GET['pesan'] == 'dihapus') $pesan = "Foto berhasil dihapus!";
}

// Ambil data kategori untuk Dropdown Filter & Upload
$kategori = $pdo->query("SELECT * FROM slws_categories")->fetchAll();

// Filter Foto Berdasarkan Kategori
$filter_kat = $_GET['kat'] ?? '';
$query = "SELECT p.*, c.name as category_name FROM slws_photos p JOIN slws_categories c ON p.category_id = c.id";
$params = [];

if ($filter_kat != '') {
    $query .= " WHERE p.category_id = ?";
    $params[] = $filter_kat;
}
$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Galeri Foto - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar a { color: #adb5bd; text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #343a40; color: #fff; border-left: 4px solid #198754; }
        .img-grid { width: 100%; height: 200px; object-fit: cover; border-radius: 8px; }
        .foto-card { position: relative; overflow: hidden; border-radius: 8px; border: 1px solid #ddd; }
        .foto-overlay { 
            position: absolute; top: 0; left: 0; right: 0; bottom: 0; 
            background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; 
            opacity: 0; transition: opacity 0.2s ease-in-out; 
        }
        .foto-card:hover .foto-overlay { opacity: 1; }
        .badge-kategori { position: absolute; top: 8px; left: 8px; z-index: 10; }
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
        <a href="kategori.php"><i class="fas fa-folder me-2"></i> Kategori Foto</a>
        <a href="galeri.php" class="active"><i class="fas fa-images me-2"></i> Galeri Foto</a>
        <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>

    <div class="flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Galeri Foto</h4>
            <div class="d-flex gap-2">
                <form method="GET" class="m-0">
                    <select name="kat" class="form-select bg-white border-secondary" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php foreach($kategori as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= $filter_kat == $kat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUpload"><i class="fas fa-upload"></i> Upload Foto</button>
            </div>
        </div>
        <hr>

        <?php if ($pesan): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $pesan ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-3 mt-2">
            <?php foreach ($photos as $img): ?>
            <div class="col-md-3 col-sm-4 col-6">
                <div class="foto-card bg-white shadow-sm">
                    <span class="badge bg-dark badge-kategori"><?= htmlspecialchars($img['category_name']) ?></span>
                    <img src="../<?= htmlspecialchars($img['image_path']) ?>" alt="Foto" class="img-grid">
                    
                    <div class="foto-overlay">
                        <a href="galeri.php?aksi=hapus&id=<?= $img['id'] ?>&kat=<?= $filter_kat ?>" class="btn btn-danger btn-sm shadow" onclick="return confirm('Yakin ingin menghapus foto ini saja?')">
                            <i class="fas fa-trash"></i> Hapus Foto Ini
                        </a>
                    </div>
                    
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($photos)): ?>
                <div class="col-12 text-center text-muted py-5">
                    <i class="fas fa-images fs-1 mb-3 opacity-25"></i>
                    <p>Belum ada foto di kategori ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUpload" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Upload & Auto-Compress Foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <?php if(empty($kategori)): ?>
              <div class="alert alert-warning">Kamu belum membuat Kategori. Silakan buat folder Kategori terlebih dahulu.</div>
          <?php else: ?>
              <div class="mb-3">
                  <label>Pilih Folder / Kategori</label>
                  <select name="category_id" class="form-select" required>
                      <option value="">-- Pilih Kategori --</option>
                      <?php foreach($kategori as $kat): ?>
                          <option value="<?= $kat['id'] ?>" <?= $filter_kat == $kat['id'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($kat['name']) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="mb-3">
                  <label>Pilih Foto <span class="text-success fw-bold">(Otomatis Kompres & Resize)</span></label>
                  <input type="file" name="fotos[]" class="form-control" accept="image/png, image/jpeg, image/jpg" multiple required>
                  <small class="text-muted d-block mt-2">Bisa upload banyak sekaligus. File besar (>5MB) akan otomatis dikecilkan menjadi di bawah ~900KB dengan resolusi web optimal.</small>
              </div>
          <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <?php if(!empty($kategori)): ?>
            <button type="submit" class="btn btn-success"><i class="fas fa-cloud-upload-alt"></i> Upload Sekarang</button>
        <?php endif; ?>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>