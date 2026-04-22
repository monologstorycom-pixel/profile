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

    function serverCompress($tmp_path, $destination) {
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagecreatetruecolor')) {
            return move_uploaded_file($tmp_path, $destination);
        }
        $info = @getimagesize($tmp_path);
        if (!$info) return move_uploaded_file($tmp_path, $destination);
        [$origW, $origH] = $info;
        $mime = $info['mime'];
        $maxW  = 1920;
        $scale = ($origW > $maxW) ? $maxW / $origW : 1;
        $newW  = (int)round($origW * $scale);
        $newH  = (int)round($origH * $scale);
        $canvas = imagecreatetruecolor($newW, $newH);
        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($tmp_path);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($tmp_path);
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

// ── PAGINATION ──
$per_page   = 48; // jumlah foto per halaman
$page_num   = max(1, (int)($_GET['p'] ?? 1));
$filter_kat = $_GET['kat'] ?? '';

// Count total dulu (query ringan)
$count_q = "SELECT COUNT(*) FROM slws_photos";
$count_p = [];
if ($filter_kat !== '') { $count_q .= " WHERE category_id = ?"; $count_p[] = $filter_kat; }
$stmt_count = $pdo->prepare($count_q);
$stmt_count->execute($count_p);
$total_photos = (int)$stmt_count->fetchColumn();
$total_pages  = max(1, (int)ceil($total_photos / $per_page));
$page_num     = min($page_num, $total_pages);
$offset       = ($page_num - 1) * $per_page;

// Query foto dengan LIMIT+OFFSET — hanya ambil yang ditampilkan
$q      = "SELECT p.id, p.image_path, c.name AS category_name
           FROM slws_photos p
           JOIN slws_categories c ON p.category_id = c.id";
$params = [];
if ($filter_kat !== '') { $q .= " WHERE p.category_id = ?"; $params[] = $filter_kat; }
$q .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$stmt = $pdo->prepare($q);
// Bind types correctly for LIMIT/OFFSET (must be int)
$stmt->bindValue(count($params) - 1, $per_page, PDO::PARAM_INT);
$stmt->bindValue(count($params),     $offset,    PDO::PARAM_INT);
// rebind non-int params
if ($filter_kat !== '') {
    $stmt->bindValue(1, $filter_kat);
}
$stmt->execute();
$photos = $stmt->fetchAll();

// ── DATA KATEGORI (untuk filter & modal) ──
$kategori = $pdo->query("SELECT * FROM slws_categories ORDER BY name")->fetchAll();

require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Galeri Foto</h2>
    <p>
      <?= $total_photos ?> foto total
      <?php if ($filter_kat): ?> · filter aktif<?php endif; ?>
      · halaman <?= $page_num ?>/<?= $total_pages ?>
    </p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <form method="GET" style="margin:0;display:flex;gap:6px;align-items:center">
      <select name="kat" class="form-control" style="min-width:160px;padding:8px 12px" onchange="this.form.submit()">
        <option value="">Semua Kategori</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['id'] ?>" <?= $filter_kat == $k['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($k['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="p" value="1">
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
  <!-- FOTO GRID — gambar pakai loading="lazy" native -->
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:8px">
    <?php foreach ($photos as $img): ?>
    <div class="foto-item" style="position:relative;border-radius:9px;overflow:hidden;border:1px solid var(--border);aspect-ratio:4/3;background:var(--surface2)">
      <!-- loading="lazy" + decoding="async" — browser handle, zero JS needed -->
      <img
        src="../<?= htmlspecialchars($img['image_path']) ?>"
        alt="<?= htmlspecialchars($img['category_name']) ?>"
        loading="lazy"
        decoding="async"
        width="310" height="232"
        style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.3s;will-change:transform"
      >
      <div class="foto-overlay">
        <a href="galeri.php?aksi=hapus&id=<?= $img['id'] ?>&kat=<?= urlencode($filter_kat) ?>&p=<?= $page_num ?>"
           onclick="return confirm('Hapus foto ini?')"
           class="btn btn-danger btn-sm" style="backdrop-filter:blur(4px)">
          <i class="lucide lucide-trash-2"></i> Hapus
        </a>
      </div>
      <div style="position:absolute;top:5px;left:5px;pointer-events:none">
        <span class="badge badge-dim" style="font-size:9px"><?= htmlspecialchars($img['category_name']) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── PAGINATION ── -->
  <?php if ($total_pages > 1): ?>
  <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:24px;flex-wrap:wrap">
    <?php
    // Prev
    if ($page_num > 1):
        $prev_url = '?kat=' . urlencode($filter_kat) . '&p=' . ($page_num - 1);
    ?>
      <a href="<?= $prev_url ?>" class="btn btn-ghost btn-sm"><i class="lucide lucide-chevron-left"></i></a>
    <?php endif; ?>

    <?php
    // Page numbers: show max 7 buttons with ellipsis
    $range = 2;
    for ($i = 1; $i <= $total_pages; $i++):
      $show = ($i == 1 || $i == $total_pages || abs($i - $page_num) <= $range);
      $ellipsis_before = ($i == $page_num - $range - 1 && $i > 1);
      $ellipsis_after  = ($i == $page_num + $range + 1 && $i < $total_pages);
      if ($ellipsis_before || $ellipsis_after):
    ?>
        <span style="color:var(--text-dim);padding:0 4px">…</span>
    <?php
      endif;
      if (!$show) continue;
      $url = '?kat=' . urlencode($filter_kat) . '&p=' . $i;
    ?>
      <a href="<?= $url ?>" class="btn btn-sm <?= $i == $page_num ? 'btn-primary' : 'btn-ghost' ?>"
         style="min-width:34px;justify-content:center"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page_num < $total_pages):
        $next_url = '?kat=' . urlencode($filter_kat) . '&p=' . ($page_num + 1);
    ?>
      <a href="<?= $next_url ?>" class="btn btn-ghost btn-sm"><i class="lucide lucide-chevron-right"></i></a>
    <?php endif; ?>
  </div>
  <div style="text-align:center;font-size:11px;color:var(--text-dim);margin-top:10px;font-family:var(--mono)">
    Menampilkan <?= (($page_num-1)*$per_page)+1 ?>–<?= min($page_num*$per_page, $total_photos) ?> dari <?= $total_photos ?> foto
  </div>
  <?php endif; ?>

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

        <div style="display:flex;align-items:flex-start;gap:8px;padding:11px 14px;background:rgba(52,211,153,0.07);border:1px solid rgba(52,211,153,0.18);border-radius:8px;margin-bottom:18px;font-size:12px;color:var(--green);line-height:1.6">
          <i class="lucide lucide-sparkles" style="font-size:15px;margin-top:1px;flex-shrink:0"></i>
          <div>
            <strong>Auto Kompres Aktif</strong> — Setiap foto dikompres di browser <em>sebelum</em> diupload,
            ukuran di bawah <strong>900KB</strong>. File asli tidak berubah.
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

        <div id="preview-list" style="display:none;margin-top:4px">
          <div style="font-size:11px;color:var(--text-dim);margin-bottom:8px;font-family:var(--mono)" id="preview-summary"></div>
          <div id="preview-items" style="display:flex;flex-wrap:wrap;gap:8px"></div>
        </div>

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
/* Hover foto */
.foto-item:hover img { transform: scale(1.05); }
.foto-overlay {
  position: absolute; inset: 0;
  background: transparent;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.2s, opacity 0.2s;
  opacity: 0;
}
.foto-item:hover .foto-overlay { opacity: 1; background: rgba(0,0,0,0.48); }

/* Preview thumbnail di modal upload */
.preview-thumb {
  width: 70px; height: 70px; object-fit: cover;
  border-radius: 7px; border: 1px solid var(--border);
  display: block;
}
.preview-card { position: relative; text-align: center; }
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
function openUploadModal()  { document.getElementById('modal-upload').classList.add('open'); }
function closeUploadModal() { document.getElementById('modal-upload').classList.remove('open'); }
document.getElementById('modal-upload').addEventListener('click', function(e){ if(e.target===this) closeUploadModal(); });

// ── Compress helpers ──
let compressedFiles = [];
const TARGET_SIZE = 900 * 1024;
const MAX_WIDTH   = 1920;

function fmtSize(bytes) {
  if (bytes < 1024)       return bytes + 'B';
  if (bytes < 1048576)    return (bytes/1024).toFixed(0) + 'KB';
  return (bytes/1048576).toFixed(1) + 'MB';
}

function compressFile(file) {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.onload = e => {
      const img = new Image();
      img.onload = () => {
        let w = img.width, h = img.height;
        if (w > MAX_WIDTH) { h = Math.round(h * MAX_WIDTH / w); w = MAX_WIDTH; }
        const canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        // Release image memory
        img.src = '';
        let quality = 0.82;
        (function tryCompress() {
          canvas.toBlob(blob => {
            if (!blob) { resolve({ blob: null, origSize: file.size, finalSize: 0 }); return; }
            if (blob.size <= TARGET_SIZE || quality <= 0.55) {
              resolve({ blob, origSize: file.size, finalSize: blob.size });
            } else {
              quality -= 0.05;
              tryCompress();
            }
          }, 'image/jpeg', quality);
        })();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
}

async function previewFiles(input) {
  const files = Array.from(input.files);
  if (!files.length) return;
  // Revoke old object URLs to free memory
  document.querySelectorAll('#preview-items img').forEach(el => URL.revokeObjectURL(el.src));
  compressedFiles = [];
  document.getElementById('preview-items').innerHTML = '';
  document.getElementById('preview-list').style.display = 'block';
  document.getElementById('preview-summary').textContent = `Mengkompres ${files.length} foto...`;
  document.getElementById('btn-upload').disabled = true;

  let totalOrig = 0, totalFinal = 0;
  for (let i = 0; i < files.length; i++) {
    const result = await compressFile(files[i]);
    if (!result.blob) continue;
    totalOrig  += result.origSize;
    totalFinal += result.finalSize;
    const url     = URL.createObjectURL(result.blob);
    const reduced = Math.round((1 - result.finalSize / result.origSize) * 100);
    const card    = document.createElement('div');
    card.className = 'preview-card';
    card.innerHTML = `
      <img src="${url}" class="preview-thumb" alt="" loading="lazy">
      ${reduced > 0 ? `<span class="compress-badge">-${reduced}%</span>` : ''}
      <span class="size-badge">${fmtSize(result.finalSize)}</span>
    `;
    document.getElementById('preview-items').appendChild(card);
    compressedFiles.push({ name: files[i].name, blob: result.blob });
  }
  const savedPct = totalOrig > 0 ? Math.round((1 - totalFinal / totalOrig) * 100) : 0;
  document.getElementById('preview-summary').textContent =
    `${compressedFiles.length} foto siap — ${fmtSize(totalOrig)} → ${fmtSize(totalFinal)} (hemat ${savedPct}%)`;
  document.getElementById('btn-upload').disabled = (compressedFiles.length === 0);
}

async function startUpload() {
  const catId = document.getElementById('sel-category').value;
  if (!catId)              { alert('Pilih kategori dulu!'); return; }
  if (!compressedFiles.length) { alert('Pilih foto dulu!'); return; }

  document.getElementById('btn-upload').disabled = true;
  document.getElementById('btn-upload').innerHTML = '<i class="lucide lucide-loader-2"></i> Uploading...';
  document.getElementById('upload-progress').style.display = 'block';

  const bar   = document.getElementById('progress-bar');
  const pct   = document.getElementById('progress-pct');
  const label = document.getElementById('progress-label');
  const total = compressedFiles.length;
  let done = 0;

  for (const f of compressedFiles) {
    label.textContent = `Mengupload ${f.name}... (${done+1}/${total})`;
    const fd = new FormData();
    fd.append('category_id', catId);
    fd.append('fotos[]', f.blob, f.name.replace(/\.[^.]+$/, '') + '.jpg');
    try { await fetch('galeri.php', { method: 'POST', body: fd }); }
    catch(err) { console.warn('Upload gagal:', f.name, err); }
    done++;
    const p = Math.round(done / total * 100);
    bar.style.width = p + '%';
    pct.textContent  = p + '%';
  }

  label.textContent = '✓ Selesai! Memuat ulang...';
  bar.style.background = 'var(--green)';
  // Revoke all object URLs before redirect
  document.querySelectorAll('#preview-items img').forEach(el => URL.revokeObjectURL(el.src));
  setTimeout(() => { window.location.href = 'galeri.php?kat=' + encodeURIComponent(catId); }, 700);
}
</script>
</body>
</html>