<?php
require 'koneksi.php';
$page_title  = 'Galeri Foto';
$active_menu = 'galeri';

$aksi = $_GET['aksi'] ?? '';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM slws_photos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $foto = $stmt->fetch();
    if ($foto) {
        $file = '../' . $foto['image_path'];
        if (file_exists($file)) unlink($file);
        $pdo->prepare("DELETE FROM slws_photos WHERE id = ?")->execute([$_GET['id']]);
    }
    $kat = isset($_GET['kat']) ? "?kat=".$_GET['kat']."&pesan=dihapus" : "?pesan=dihapus";
    header("Location: galeri.php".$kat); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fotos'])) {
    $category_id = $_POST['category_id'];
    $folder = '../uploads/galeri/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    function compressAndResize($source, $destination, $quality) {
        $info = getimagesize($source);
        if (!$info) return false;
        [$width, $height] = $info;
        $mime = $info['mime'];
        $maxW = 1920;
        if ($width > $maxW) { $newW = $maxW; $newH = floor($height * ($maxW / $width)); }
        else { $newW = $width; $newH = $height; }
        $out = imagecreatetruecolor($newW, $newH);
        if ($mime == 'image/jpeg') { $src = imagecreatefromjpeg($source); }
        elseif ($mime == 'image/png') {
            $src = imagecreatefrompng($source);
            imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
        } else { return move_uploaded_file($source, $destination); }
        imagecopyresampled($out, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        $result = imagejpeg($out, $destination, $quality);
        imagedestroy($src); imagedestroy($out);
        return $result;
    }

    $berhasil = 0;
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {
        if ($_FILES['fotos']['error'][$key] == 0) {
            $fname = 'slws_' . time() . '_' . uniqid() . '.jpg';
            if (compressAndResize($tmp, $folder . $fname, 75)) {
                $pdo->prepare("INSERT INTO slws_photos (category_id, image_path) VALUES (?,?)")
                    ->execute([$category_id, 'uploads/galeri/' . $fname]);
                $berhasil++;
            }
        }
    }
    header("Location: galeri.php?pesan=diupload&jml=$berhasil&kat=$category_id"); exit;
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'diupload') $pesan = ($_GET['jml'] ?? 0) . " foto berhasil diupload!";
    if ($_GET['pesan'] == 'dihapus')  $pesan = "Foto berhasil dihapus.";
}

$kategori = $pdo->query("SELECT * FROM slws_categories")->fetchAll();
$filter_kat = $_GET['kat'] ?? '';
$q = "SELECT p.*, c.name as category_name FROM slws_photos p JOIN slws_categories c ON p.category_id = c.id";
$params = [];
if ($filter_kat !== '') { $q .= " WHERE p.category_id = ?"; $params[] = $filter_kat; }
$q .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($q); $stmt->execute($params);
$photos = $stmt->fetchAll();
require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Galeri Foto</h2>
    <p><?= count($photos) ?> foto<?= $filter_kat ? ' di kategori ini' : ' total' ?></p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <form method="GET" style="margin:0">
      <select name="kat" class="form-control" style="min-width:160px;padding:8px 12px" onchange="this.form.submit()">
        <option value="">Semua Kategori</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['id'] ?>" <?= $filter_kat == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
    <button class="btn btn-primary" onclick="document.getElementById('modal-upload').classList.add('open')">
      <i class="lucide lucide-upload"></i> Upload Foto
    </button>
  </div>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><span>✓</span> <?= $pesan ?></div><?php endif; ?>

<?php if (empty($photos)): ?>
  <div class="empty-box" style="padding:80px 20px">
    <i class="lucide lucide-images"></i>
    <p>Belum ada foto<?= $filter_kat ? ' di kategori ini' : '' ?>.</p>
  </div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
    <?php foreach ($photos as $img): ?>
    <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--border);aspect-ratio:4/3;background:var(--surface);cursor:pointer" class="foto-item">
      <img src="../<?= htmlspecialchars($img['image_path']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.3s">
      <div class="foto-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,0);display:flex;align-items:center;justify-content:center;transition:background 0.2s;opacity:0">
        <a href="galeri.php?aksi=hapus&id=<?= $img['id'] ?>&kat=<?= $filter_kat ?>"
           onclick="return confirm('Hapus foto ini?')"
           class="btn btn-danger btn-sm" style="backdrop-filter:blur(4px)">
          <i class="lucide lucide-trash-2"></i> Hapus
        </a>
      </div>
      <div style="position:absolute;top:6px;left:6px">
        <span class="badge badge-dim" style="font-size:9px"><?= htmlspecialchars($img['category_name']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- UPLOAD MODAL -->
<div class="modal-backdrop" id="modal-upload">
  <div class="modal">
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-header">
        <span class="modal-title">Upload Foto</span>
        <button type="button" class="modal-close" onclick="document.getElementById('modal-upload').classList.remove('open')">×</button>
      </div>
      <div class="modal-body">
        <?php if (empty($kategori)): ?>
          <div class="alert alert-danger"><span>⚠</span> Buat kategori terlebih dahulu!</div>
        <?php else: ?>
          <div class="form-group">
            <label class="form-label">Kategori / Folder</label>
            <select name="category_id" class="form-control" required>
              <option value="">-- Pilih --</option>
              <?php foreach ($kategori as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $filter_kat == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Pilih Foto <span style="color:var(--green)">— Multi-upload, Auto Compress</span></label>
            <input type="file" name="fotos[]" class="form-control" accept="image/png,image/jpeg,image/jpg" multiple required>
            <div class="form-sub">Bisa upload banyak sekaligus. Gambar besar otomatis dikompres.</div>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-upload').classList.remove('open')">Batal</button>
        <?php if (!empty($kategori)): ?>
          <button type="submit" class="btn btn-primary"><i class="lucide lucide-cloud-upload"></i> Upload</button>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

</div></div>
<style>
.foto-item:hover img { transform: scale(1.05); }
.foto-item:hover .foto-overlay { opacity: 1 !important; background: rgba(0,0,0,0.5) !important; }
</style>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}
document.getElementById('modal-upload').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')});
</script>
</body></html>
