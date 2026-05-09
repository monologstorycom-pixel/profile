<?php
require 'koneksi.php';

// Auth check SEBELUM memproses POST apapun
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php'); exit;
}

$page_title  = 'Profil';
$active_menu = 'profil';

$stmt = $pdo->query("SELECT * FROM profile_settings LIMIT 1");
$profil = $stmt->fetch();
if (!$profil) {
    $pdo->query("INSERT INTO profile_settings (full_name, tagline) VALUES ('Rizqi Subagyo', 'IT Support Specialist')");
    $profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch();
}

$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $profile_picture = $profil['profile_picture'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $folder = '../uploads/';
        if (!is_dir($folder)) mkdir($folder, 0755, true);
        $allowed_mime = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $real_mime = mime_content_type($_FILES['foto']['tmp_name']);
        $mime_to_ext = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!in_array($real_mime, $allowed_mime)) {
            $pesan = 'Error: Hanya file gambar (JPG, PNG, WEBP) yang diizinkan.';
        } else {
            $ext   = $mime_to_ext[$real_mime];
            $fname = 'profil_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $fname)) {
                $profile_picture = 'uploads/' . $fname;
            }
        }
    }
    // --- FAVICON: upload file atau pakai URL ---
    $favicon_url = $profil['favicon_url'] ?? '';
    if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] == 0) {
        $fav_allowed = ['image/png','image/x-icon','image/vnd.microsoft.icon','image/svg+xml','image/webp','image/jpeg'];
        $fav_mime    = mime_content_type($_FILES['favicon_file']['tmp_name']);
        $fav_ext_map = ['image/png'=>'png','image/x-icon'=>'ico','image/vnd.microsoft.icon'=>'ico','image/svg+xml'=>'svg','image/webp'=>'webp','image/jpeg'=>'jpg'];
        if (!in_array($fav_mime, $fav_allowed)) {
            $pesan = 'Error: Format favicon tidak didukung. Gunakan PNG, ICO, SVG, atau WEBP.';
        } else {
            $fav_ext  = $fav_ext_map[$fav_mime];
            $fav_name = 'favicon.' . $fav_ext;
            $fav_path = '../' . $fav_name;
            if (move_uploaded_file($_FILES['favicon_file']['tmp_name'], $fav_path)) {
                $favicon_url = $fav_name;
            }
        }
    } elseif (!empty($_POST['favicon_url_input'])) {
        // Pakai URL eksternal yang dipaste
        $favicon_url = trim($_POST['favicon_url_input']);
    } elseif (isset($_POST['favicon_clear']) && $_POST['favicon_clear'] === '1') {
        $favicon_url = '';
    }

    $wa = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');
    $pdo->prepare("UPDATE profile_settings SET full_name=?,tagline=?,availability_status=?,email=?,github_link=?,linkedin_link=?,whatsapp=?,profile_picture=?,favicon_url=? WHERE id=?")
        ->execute([$_POST['full_name'],$_POST['tagline'],$_POST['availability_status'],$_POST['email'],$_POST['github_link'],$_POST['linkedin_link'],$wa,$profile_picture,$favicon_url,$profil['id']]);
    if (!$pesan) $pesan = 'Profil berhasil diperbarui!';
    $profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch();
}
require '_layout.php';
?>

<style>
/* Layout dua kolom: form kiri, foto kanan */
.profil-layout {
    display: grid;
    grid-template-columns: 1fr 240px;
    gap: 16px;
    align-items: start;
}
@media (max-width: 768px) {
    .profil-layout {
        grid-template-columns: 1fr;
    }
    /* Di mobile foto muncul duluan biar langsung kelihatan */
    .profil-foto-card { order: -1; }
}

/* Favicon preview box */
.favicon-preview {
    width: 48px; height: 48px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--surface);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; flex-shrink: 0;
}
.favicon-preview img { width: 32px; height: 32px; object-fit: contain; }
.fav-tab-btn {
    padding: 5px 14px; border-radius: 6px; border: 1px solid var(--border);
    background: transparent; color: var(--text-dim); cursor: pointer;
    font-size: 12px; transition: all .15s;
}
.fav-tab-btn.active {
    background: var(--accent-bg); color: var(--accent);
    border-color: var(--accent);
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-head">
  <div class="page-head-left">
    <h2>Pengaturan Profil</h2>
    <p>Ubah informasi yang tampil di halaman portfolio</p>
  </div>
</div>

<?php if ($pesan): ?>
  <div class="alert alert-<?= str_starts_with($pesan, 'Error') ? 'danger' : 'success' ?>">
    <span><?= str_starts_with($pesan, 'Error') ? '✕' : '✓' ?></span> <?= htmlspecialchars($pesan) ?>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="profil-layout">

  <!-- Kiri: informasi utama -->
  <div class="card">
    <div class="card-header"><span class="card-title">Informasi Utama</span></div>
    <div class="card-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($profil['full_name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Tagline / Posisi</label>
          <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($profil['tagline'] ?? '') ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Status Ketersediaan</label>
        <input type="text" name="availability_status" class="form-control" value="<?= htmlspecialchars($profil['availability_status'] ?? 'Tersedia untuk proyek baru') ?>">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profil['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Nomor WhatsApp</label>
          <input type="text" name="whatsapp" class="form-control" placeholder="cth: 6281234567890" value="<?= htmlspecialchars($profil['whatsapp'] ?? '') ?>">
          <div class="form-sub" style="margin-top:4px">Format internasional tanpa + (62xxx)</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">GitHub Link</label>
          <input type="text" name="github_link" class="form-control" value="<?= htmlspecialchars($profil['github_link'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">LinkedIn Link</label>
          <input type="text" name="linkedin_link" class="form-control" value="<?= htmlspecialchars($profil['linkedin_link'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- Kanan: foto profil -->
  <div class="card profil-foto-card">
    <div class="card-header"><span class="card-title">Foto Profil</span></div>
    <div class="card-body" style="text-align:center">
      <?php if (!empty($profil['profile_picture'])): ?>
        <img src="../<?= htmlspecialchars($profil['profile_picture']) ?>" alt="Profil"
          style="width:110px;height:110px;object-fit:cover;border-radius:50%;border:2px solid var(--border2);margin-bottom:14px;display:block;margin-inline:auto">
      <?php else: ?>
        <div style="width:110px;height:110px;border-radius:50%;background:var(--surface2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:36px;color:var(--text-dim)">
          👤
        </div>
      <?php endif; ?>
      <input type="file" name="foto" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp">
      <div class="form-sub" style="text-align:center;margin-top:6px">Kosongkan jika tidak ingin mengubah foto</div>
    </div>
  </div>

</div>

<!-- ===== FAVICON CARD ===== -->
<div class="card" style="margin-top:16px">
  <div class="card-header">
    <span class="card-title">Favicon</span>
    <span class="form-sub" style="margin-left:8px">Ikon kecil yang muncul di tab browser</span>
  </div>
  <div class="card-body">
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
      <!-- Preview favicon saat ini -->
      <div class="favicon-preview" id="fav-preview-box">
        <?php
          $fav = $profil['favicon_url'] ?? '';
          $fav_src = '';
          if ($fav) {
              // path lokal atau URL eksternal
              $fav_src = (str_starts_with($fav,'http') ? $fav : '../'.$fav);
          }
        ?>
        <?php if ($fav_src): ?>
          <img src="<?= htmlspecialchars($fav_src) ?>" id="fav-img-preview" alt="favicon">
        <?php else: ?>
          <span id="fav-img-preview" style="font-size:22px">🌐</span>
        <?php endif; ?>
      </div>
      <div>
        <div style="font-size:13px;color:var(--text-hi);font-weight:500">
          <?= $fav ? htmlspecialchars($fav) : '<span style="color:var(--text-dim)">Belum ada favicon</span>' ?>
        </div>
        <div class="form-sub" style="margin-top:2px">PNG, ICO, SVG, atau WEBP. Disarankan ukuran 32×32 atau 64×64 px.</div>
      </div>
    </div>

    <!-- Tab switch: Upload vs URL -->
    <div style="display:flex;gap:6px;margin-bottom:12px">
      <button type="button" class="fav-tab-btn active" id="tab-upload" onclick="switchFavTab('upload')">⬆ Upload File</button>
      <button type="button" class="fav-tab-btn" id="tab-url" onclick="switchFavTab('url')">🔗 Paste URL</button>
    </div>

    <!-- Panel: Upload -->
    <div id="panel-upload">
      <div class="form-group" style="margin-bottom:0">
        <input type="file" name="favicon_file" id="favicon_file" class="form-control"
               accept="image/png,image/x-icon,image/svg+xml,image/webp,image/jpeg"
               onchange="previewFavicon(this)">
        <div class="form-sub" style="margin-top:4px">File akan disimpan sebagai <code>favicon.{ext}</code> di root project</div>
      </div>
    </div>

    <!-- Panel: URL -->
    <div id="panel-url" style="display:none">
      <div class="form-group" style="margin-bottom:0">
        <input type="url" name="favicon_url_input" id="favicon_url_input" class="form-control"
               placeholder="https://contoh.com/favicon.ico"
               value="<?= (str_starts_with($fav,'http') ? htmlspecialchars($fav) : '') ?>"
               oninput="previewFaviconUrl(this.value)">
        <div class="form-sub" style="margin-top:4px">Gunakan URL gambar yang bisa diakses publik</div>
      </div>
    </div>

    <?php if ($fav): ?>
    <div style="margin-top:12px">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;color:var(--text-dim)">
        <input type="checkbox" name="favicon_clear" value="1" style="accent-color:var(--accent)">
        Hapus favicon (kembali ke default)
      </label>
    </div>
    <?php endif; ?>
  </div>
</div>
<!-- ===== END FAVICON CARD ===== -->

<div style="margin-top:16px">
  <button type="submit" class="btn btn-primary"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Perubahan</button>
</div>

</form>

</div></div>
<script>
function switchFavTab(tab) {
    document.getElementById('panel-upload').style.display = tab === 'upload' ? '' : 'none';
    document.getElementById('panel-url').style.display    = tab === 'url'    ? '' : 'none';
    document.getElementById('tab-upload').classList.toggle('active', tab === 'upload');
    document.getElementById('tab-url').classList.toggle('active', tab === 'url');
    // Reset input yang tidak aktif
    if (tab === 'upload') document.getElementById('favicon_url_input').value = '';
    else document.getElementById('favicon_file').value = '';
}
function previewFavicon(input) {
    if (!input.files || !input.files[0]) return;
    const url = URL.createObjectURL(input.files[0]);
    updateFavPreview(url);
}
function previewFaviconUrl(url) {
    if (url) updateFavPreview(url);
}
function updateFavPreview(src) {
    const box = document.getElementById('fav-preview-box');
    box.innerHTML = '<img src="' + src + '" style="width:32px;height:32px;object-fit:contain" onerror="this.parentNode.innerHTML=\'❌\'">';
}

{document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}
</script>

</body></html>
