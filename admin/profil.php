<?php
require 'koneksi.php';
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
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $fname = 'profil_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $fname)) {
            $profile_picture = 'uploads/' . $fname;
        }
    }
    $pdo->prepare("UPDATE profile_settings SET full_name=?,tagline=?,availability_status=?,email=?,github_link=?,linkedin_link=?,profile_picture=? WHERE id=?")
        ->execute([$_POST['full_name'],$_POST['tagline'],$_POST['availability_status'],$_POST['email'],$_POST['github_link'],$_POST['linkedin_link'],$profile_picture,$profil['id']]);
    $pesan = 'Profil berhasil diperbarui!';
    $profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch();
}
require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Pengaturan Profil</h2>
    <p>Ubah informasi yang tampil di halaman portfolio</p>
  </div>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><span>✓</span> <?= $pesan ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 240px;gap:16px;align-items:start">

  <!-- Left -->
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

  <!-- Right: Photo -->
  <div class="card">
    <div class="card-header"><span class="card-title">Foto Profil</span></div>
    <div class="card-body" style="text-align:center">
      <?php if (!empty($profil['profile_picture'])): ?>
        <img src="../<?= $profil['profile_picture'] ?>" alt="Profil"
          style="width:110px;height:110px;object-fit:cover;border-radius:50%;border:2px solid var(--border2);margin-bottom:14px;display:block;margin-inline:auto">
      <?php else: ?>
        <div style="width:110px;height:110px;border-radius:50%;background:var(--surface2);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:36px;color:var(--text-dim)">
          👤
        </div>
      <?php endif; ?>
      <input type="file" name="foto" class="form-control" accept="image/png,image/jpeg,image/jpg">
      <div class="form-sub" style="text-align:center;margin-top:6px">Kosongkan jika tidak ingin mengubah foto</div>
    </div>
  </div>

</div>

<div style="margin-top:16px">
  <button type="submit" class="btn btn-primary"><i class="lucide lucide-save"></i> Simpan Perubahan</button>
</div>

</form>

</div></div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}
</script>

</body></html>
