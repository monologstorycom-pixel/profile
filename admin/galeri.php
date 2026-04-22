<?php
// Tangkap POST terlalu besar sebelum session/output apapun
$post_size_error = false;
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_SERVER['CONTENT_LENGTH']) &&
    (int)$_SERVER['CONTENT_LENGTH'] > 0 &&
    empty($_POST) && empty($_FILES)
) {
    $post_size_error = true;
}

require 'koneksi.php';
$page_title  = 'Galeri Foto';
$active_menu = 'galeri';

$aksi        = $_GET['aksi'] ?? '';
$pesan       = '';
$pesan_error = '';

if ($post_size_error) {
    $max = ini_get('post_max_size') ?: '8M';
    $pesan_error = "Upload gagal! Total file melebihi batas server ($max). Coba upload lebih sedikit foto per batch.";
}

// ── HAPUS ──
if (!$post_size_error && $aksi == 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM slws_photos WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $foto = $stmt->fetch();
    if ($foto) {
        $file = '../' . $foto['image_path'];
        if (file_exists($file)) unlink($file);
        $pdo->prepare("DELETE FROM slws_photos WHERE id = ?")->execute([$_GET['id']]);
    }
    $kembali = isset($_GET['kat']) ? "?kat=".$_GET['kat']."&pesan=dihapus" : "?pesan=dihapus";
    header("Location: galeri.php" . $kembali); exit;
}

// ── UPLOAD ──
if (!$post_size_error && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['fotos'])) {
    $category_id = $_POST['category_id'];
    $folder      = '../uploads/galeri/';
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    /**
     * Kompres & resize pakai GD (server-side backup).
     * Target: ≤ 900KB, max lebar 1920px, kualitas mulai 82 turun sampai 60.
     */
    function serverCompress($tmp_path, $destination) {
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagecreatetruecolor')) {
            // GD tidak aktif — simpan langsung
            return move_uploaded_file($tmp_path, $destination);
        }

        $info = @getimagesize($tmp_path);
        if (!$info) return move_uploaded_file($tmp_path, $destination);

        [$origW, $origH] = $info;
        $mime = $info['mime'];

        // Buat canvas
        $maxW  = 1920;
        $scale = ($origW > $maxW) ? $maxW / $origW : 1;
        $newW  = (int)round($origW * $scale);
        $newH  = (int)round($origH * $scale);

        $canvas = imagecreatetruecolor($newW, $newH);

        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($tmp_path);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($tmp_path);
            // Flatten PNG transparency ke putih
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($tmp_path);
        } else {
            imagedestroy($canvas);
            return move_uploaded_file($tmp_path, $destination);
        }

        if (!$src) { imagedestroy($canvas); return move_uploaded_file($tmp_path, $destination); }

        imagecopyresampled($canvas, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        // Turunkan quality sampai file < 900KB
        $target = 900 * 1024;
        $quality = 82;
        do {
            ob_start();
            imagejpeg($canvas, null, $quality);
            $blob = ob_get_clean();
            if (strlen($blob) <= $target || $quality <= 60) break;
            $quality -= 4;
        } while (true);

        imagedestroy($canvas);
        return (bool) file_put_contents($destination, $blob);
    }

    $berhasil = 0;
    $dilewati = 0;
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp) {
        if ($_FILES['fotos']['error'][$key] !== UPLOAD_ERR_OK) { $dilewati++; continue; }

        $orig_ext = strtolower(pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION));
        // Output selalu .jpg (hasil compress)
        $fname = 'slws_' . time() . '_' . uniqid() . '.jpg';

        if (serverCompress($tmp, $folder . $fname)) {
            $pdo->prepare("INSERT INTO slws_photos (category_id, image_path) VALUES (?,?)")
                ->execute([$category_id, 'uploads/galeri/' . $fname]);
            $berhasil++;
        } else {
            $dilewati++;
        }
    }

    $msg = "$berhasil foto berhasil diupload & dikompres";
    if ($dilewati > 0) $msg .= " ($dilewati dilewati)";
    $_SESSION['flash_ok'] = $msg;
    header("Location: galeri.php?kat=$category_id"); exit;
}

// ── PESAN ──
if (isset($_SESSION['flash_ok'])) {
    $pesan = $_SESSION['flash_ok'];
    unset($_SESSION['flash_ok']);
}
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == 'dihapus') $pesan = "Foto berhasil dihapus.";
}

// ── DATA ──
$kategori   = $pdo->query("SELECT * FROM slws_categories")->fetchAll();
$filter_kat = $_GET['kat'] ?? '';
$q      = "SELECT p.*, c.name AS category_name FROM slws_photos p JOIN slws_categories c ON p.category_id = c.id";
$params = [];
if ($filter_kat !== '') { $q .= " WHERE p.category_id = ?"; $params[] = $filter_kat; }
$q .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($q); $stmt->execute($params);
$photos = $stmt->fetchAll();

$server_upload_limit = ini_get('upload_max_filesize') ?: '8M';

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
          <option value="<?= $k['id'] ?>" <?= $filter_kat == $k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
    <button class="btn btn-primary" onclick="openUploadModal()">
      <i class="lucide lucide-upload"></i> Upload Foto
    </button>
  </div>
</div>

<?php if ($pesan_error): ?>
  <div class="alert alert-danger"><span>⚠</span> <?= htmlspecialchars($pesan_error) ?></div>
<?php endif; ?>
<?php if ($pesan): ?>
  <div class="alert alert-success"><span>✓</span> <?= htmlspecialchars($pesan) ?></div>
<?php endif; ?>

<?php if (empty($photos)): ?>
  <div class="empty-box" style="padding:80px 20px">
    <i class="lucide lucide-images"></i>
    <p>Belum ada foto<?= $filter_kat ? ' di kategori ini' : '' ?>.</p>
  </div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
    <?php foreach ($photos as $img): ?>
    <div class="foto-item" style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--border);aspect-ratio:4/3;background:var(--surface)">
      <img src="../<?= htmlspecialchars($img['image_path']) ?>" alt="" loading="lazy"
           style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.3s">
      <div class="foto-overlay" style="position:absolute;inset:0;background:rgba(0,0,0,0);display:flex;align-items:center;justify-content:center;transition:all 0.2s;opacity:0">
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

<!-- ── UPLOAD MODAL ── -->
<div class="modal-backdrop" id="modal-upload">
  <div class="modal modal-lg">
    <div class="modal-header">
      <span class="modal-title">Upload &amp; Kompres Foto</span>
      <button type="button" class="modal-close" onclick="closeUploadModal()">×</button>
    </div>
    <div class="modal-body">
      <?php if (empty($kategori)): ?>
        <div class="alert alert-danger"><span>⚠</span> Buat kategori terlebih dahulu!</div>
      <?php else: ?>

        <!-- Info bar -->
        <div style="display:flex;align-items:flex-start;gap:8px;padding:11px 14px;background:rgba(52,211,153,0.07);border:1px solid rgba(52,211,153,0.18);border-radius:8px;margin-bottom:18px;font-size:12px;color:var(--green);line-height:1.6">
          <i class="lucide lucide-sparkles" style="font-size:15px;margin-top:1px;flex-shrink:0"></i>
          <div>
            <strong>Auto Kompres Aktif</strong> — Setiap foto dikompres di browser kamu <em>sebelum</em> diupload
            sehingga ukurannya di bawah <strong>900KB</strong>. File asli tidak berubah.
            Server juga mengkompres ulang sebagai backup.
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Kategori / Folder</label>
          <select id="sel-category" class="form-control" required>
            <option value="">-- Pilih --</option>
            <?php foreach ($kategori as $k): ?>
              <option value="<?= $k['id'] ?>" <?= $filter_kat == $k['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Pilih Foto (bisa banyak sekaligus)</label>
          <input type="file" id="file-input" class="form-control"
                 accept="image/png,image/jpeg,image/jpg,image/webp" multiple
                 onchange="previewFiles(this)">
        </div>

        <!-- Preview & progress list -->
        <div id="preview-list" style="display:none;margin-top:4px">
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:8px;font-family:var(--mono)" id="preview-summary"></div>
          <div id="preview-items" style="display:flex;flex-wrap:wrap;gap:8px"></div>
        </div>

        <!-- Upload progress bar -->
        <div id="upload-progress" style="display:none;margin-top:16px">
          <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-dim);margin-bottom:6px">
            <span id="progress-label">Mengkompres & mengupload...</span>
            <span id="progress-pct">0%</span>
          </div>
          <div style="background:var(--surface2);border-radius:99px;height:5px;overflow:hidden">
            <div id="progress-bar" style="background:linear-gradient(90deg,var(--accent),#a78bfa);height:100%;width:0%;transition:width 0.3s;border-radius:99px"></div>
          </div>
        </div>

      <?php endif; ?>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeUploadModal()">Batal</button>
      <?php if (!empty($kategori)): ?>
        <button type="button" id="btn-upload" class="btn btn-primary" onclick="startUpload()" disabled>
          <i class="lucide lucide-cloud-upload"></i> Upload Sekarang
        </button>
      <?php endif; ?>
    </div>
  </div>
</div>

</div><!-- .content -->
</div><!-- .main -->

<style>
.foto-item:hover img { transform: scale(1.05); }
.foto-item:hover .foto-overlay { opacity: 1 !important; background: rgba(0,0,0,0.5) !important; }
.preview-thumb {
  width: 72px; height: 72px; object-fit: cover;
  border-radius: 7px; border: 1px solid var(--border);
  display: block;
}
.preview-card {
  position: relative; text-align: center;
}
.preview-card .size-badge {
  position: absolute; bottom: 3px; left: 50%; transform: translateX(-50%);
  background: rgba(0,0,0,0.65); color: #fff;
  font-size: 9px; font-family: var(--mono);
  padding: 1px 5px; border-radius: 4px; white-space: nowrap;
}
.preview-card .compress-badge {
  position: absolute; top: 3px; right: 3px;
  background: rgba(52,211,153,0.85); color: #000;
  font-size: 8px; padding: 1px 4px; border-radius: 3px;
}
</style>

<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}
function openUploadModal(){ document.getElementById('modal-upload').classList.add('open'); }
function closeUploadModal(){ document.getElementById('modal-upload').classList.remove('open'); }
document.getElementById('modal-upload').addEventListener('click',function(e){ if(e.target===this) closeUploadModal(); });

// ── State ──
let compressedFiles = []; // Array of {name, blob} setelah kompres

const TARGET_SIZE = 900 * 1024; // 900 KB
const MAX_WIDTH   = 1920;

function fmtSize(bytes) {
  if (bytes < 1024) return bytes + 'B';
  if (bytes < 1024*1024) return (bytes/1024).toFixed(0) + 'KB';
  return (bytes/(1024*1024)).toFixed(1) + 'MB';
}

// Kompres 1 File pakai Canvas
function compressFile(file) {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img = new Image();
      img.onload = function() {
        let w = img.width, h = img.height;
        if (w > MAX_WIDTH) { h = Math.round(h * MAX_WIDTH / w); w = MAX_WIDTH; }

        const canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        const ctx = canvas.getContext('2d');
        // Flatten ke putih (untuk PNG transparan)
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);

        // Iterasi turunkan quality sampai < 900KB
        let quality = 0.82;
        function tryCompress() {
          canvas.toBlob(function(blob) {
            if (!blob) { resolve({ blob: null, origSize: file.size, finalSize: 0 }); return; }
            if (blob.size <= TARGET_SIZE || quality <= 0.55) {
              resolve({ blob, origSize: file.size, finalSize: blob.size });
            } else {
              quality -= 0.05;
              tryCompress();
            }
          }, 'image/jpeg', quality);
        }
        tryCompress();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
}

async function previewFiles(input) {
  const files = Array.from(input.files);
  if (!files.length) return;

  compressedFiles = [];
  document.getElementById('preview-items').innerHTML = '';
  document.getElementById('preview-list').style.display = 'block';
  document.getElementById('preview-summary').textContent = `Mengkompres ${files.length} foto...`;
  document.getElementById('btn-upload').disabled = true;

  let totalOrig = 0, totalFinal = 0;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const result = await compressFile(file);
    if (!result.blob) continue;

    totalOrig  += result.origSize;
    totalFinal += result.finalSize;

    const url  = URL.createObjectURL(result.blob);
    const reduced = Math.round((1 - result.finalSize / result.origSize) * 100);

    const card = document.createElement('div');
    card.className = 'preview-card';
    card.innerHTML = `
      <img src="${url}" class="preview-thumb" alt="">
      ${reduced > 0 ? `<span class="compress-badge">-${reduced}%</span>` : ''}
      <span class="size-badge">${fmtSize(result.finalSize)}</span>
    `;
    document.getElementById('preview-items').appendChild(card);

    compressedFiles.push({ name: file.name, blob: result.blob });
  }

  const savedPct = Math.round((1 - totalFinal / totalOrig) * 100);
  document.getElementById('preview-summary').textContent =
    `${compressedFiles.length} foto siap diupload — ${fmtSize(totalOrig)} → ${fmtSize(totalFinal)} (hemat ${savedPct}%)`;
  document.getElementById('btn-upload').disabled = (compressedFiles.length === 0);
}

async function startUpload() {
  const catId = document.getElementById('sel-category').value;
  if (!catId) { alert('Pilih kategori dulu!'); return; }
  if (!compressedFiles.length) { alert('Pilih foto dulu!'); return; }

  document.getElementById('btn-upload').disabled = true;
  document.getElementById('btn-upload').innerHTML = '<i class="lucide lucide-loader-2"></i> Uploading...';
  document.getElementById('upload-progress').style.display = 'block';

  const bar   = document.getElementById('progress-bar');
  const pct   = document.getElementById('progress-pct');
  const label = document.getElementById('progress-label');
  const total = compressedFiles.length;
  let done = 0;

  // Upload satu per satu pakai fetch agar bisa track progress
  for (const f of compressedFiles) {
    label.textContent = `Mengupload ${f.name}... (${done+1}/${total})`;

    const fd = new FormData();
    fd.append('category_id', catId);
    fd.append('fotos[]', f.blob, f.name.replace(/\.[^.]+$/, '') + '.jpg');

    try {
      await fetch('galeri.php', { method: 'POST', body: fd });
    } catch(err) {
      console.warn('Upload gagal:', f.name, err);
    }

    done++;
    const p = Math.round(done / total * 100);
    bar.style.width = p + '%';
    pct.textContent = p + '%';
  }

  label.textContent = '✓ Semua selesai! Memuat ulang...';
  bar.style.background = 'var(--green)';
  setTimeout(() => {
    window.location.href = 'galeri.php?kat=' + catId;
  }, 800);
}
</script>
</body>
</html>