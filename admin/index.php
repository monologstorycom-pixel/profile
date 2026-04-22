<?php
require 'koneksi.php';
$page_title  = 'Dashboard';
$active_menu = 'dashboard';
require '_layout.php';

$jml_experience = $pdo->query("SELECT COUNT(*) FROM experiences")->fetchColumn();
$jml_projects   = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$jml_foto       = $pdo->query("SELECT COUNT(*) FROM slws_photos")->fetchColumn();
$jml_video      = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Dashboard</h2>
    <p>Selamat datang kembali, <?= htmlspecialchars($_SESSION['username']) ?> 👋</p>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card blue">
    <div class="stat-icon">💼</div>
    <div class="stat-val"><?= $jml_experience ?></div>
    <div class="stat-label">Experience</div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon">⚙️</div>
    <div class="stat-val"><?= $jml_projects ?></div>
    <div class="stat-label">Projects</div>
  </div>
  <div class="stat-card amber">
    <div class="stat-icon">🖼️</div>
    <div class="stat-val"><?= $jml_foto ?></div>
    <div class="stat-label">Foto Galeri</div>
  </div>
  <div class="stat-card purple">
    <div class="stat-icon">🎬</div>
    <div class="stat-val"><?= $jml_video ?></div>
    <div class="stat-label">Video</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <span class="card-title">Quick Actions</span>
  </div>
  <div class="card-body" style="display:flex;flex-wrap:wrap;gap:10px;">
    <a href="experience.php" class="btn btn-ghost"><i class="lucide lucide-briefcase"></i> Tambah Experience</a>
    <a href="projects.php"   class="btn btn-ghost"><i class="lucide lucide-code-2"></i> Tambah Project</a>
    <a href="galeri.php"     class="btn btn-ghost"><i class="lucide lucide-images"></i> Upload Foto</a>
    <a href="video.php"      class="btn btn-ghost"><i class="lucide lucide-clapperboard"></i> Tambah Video</a>
    <a href="profil.php"     class="btn btn-ghost"><i class="lucide lucide-user-round"></i> Edit Profil</a>
  </div>
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-header"><span class="card-title">Info</span></div>
  <div class="card-body" style="font-size:13px; color: var(--text); line-height:1.8;">
    Semua perubahan yang dilakukan di panel ini akan langsung tampil di halaman website.<br>
    Gunakan menu di sidebar kiri untuk mengelola konten portfolio kamu.
  </div>
</div>

</div><!-- .content -->
</div><!-- .main -->

<script>
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('mobile-open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('mobile-open');
  document.getElementById('overlay').classList.remove('show');
}
</script>
</body>
</html>
