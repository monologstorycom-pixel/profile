<?php
require 'koneksi.php';
require '_auth.php';
$page_title  = 'Video Portfolio';
$active_menu = 'video';

$aksi = $_GET['aksi'] ?? '';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    csrf_check_get();
    $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([(int)$_GET['id']]);
    header("Location: video.php?pesan=dihapus"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title']; $url = $_POST['video_url']; $desc = $_POST['description'];
    if (!empty($_POST['id'])) {
        $pdo->prepare("UPDATE videos SET title=?,video_url=?,description=? WHERE id=?")->execute([$title,$url,$desc,$_POST['id']]);
        header("Location: video.php?pesan=diedit");
    } else {
        $pdo->prepare("INSERT INTO videos (title,video_url,description) VALUES (?,?,?)")->execute([$title,$url,$desc]);
        header("Location: video.php?pesan=ditambah");
    }
    exit;
}

$map = ['ditambah'=>'Video berhasil ditambahkan!','dihapus'=>'Video berhasil dihapus.','diedit'=>'Video berhasil diperbarui!'];
if (isset($_GET['pesan'])) $pesan = $map[$_GET['pesan']] ?? '';

$videos = $pdo->query("SELECT * FROM videos ORDER BY id DESC")->fetchAll();
require '_layout.php';

function ytThumb($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match);
    return isset($match[1]) ? "https://img.youtube.com/vi/{$match[1]}/mqdefault.jpg" : '';
}
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Video Portfolio</h2>
    <p>Kelola video YouTube yang tampil di portfolio</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Tambah</button>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><span>✓</span> <?= $pesan ?></div><?php endif; ?>

<?php if (empty($videos)): ?>
  <div class="empty-box" style="padding:80px"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.2 6 3 11l-.9-2.4c-.3-1.1.3-2.2 1.3-2.5l13.5-4c1.1-.3 2.2.3 2.5 1.3Z"/><path d="m6.2 5.3 3.1 3.9"/><path d="m12.4 3.4 3.1 3.9"/><path d="M3 11h18v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg><p>Belum ada video.</p></div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px">
    <?php foreach ($videos as $v): 
      $thumb = ytThumb($v['video_url']);
    ?>
    <div class="card">
      <?php if ($thumb): ?>
        <div style="position:relative;padding-bottom:56.25%;background:#000;overflow:hidden">
          <img src="<?= $thumb ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0.8">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center">
            <div style="width:42px;height:42px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="5 3 19 12 5 21 5 3"/></svg>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="card-body">
        <div style="font-weight:600;color:var(--text-hi);font-size:13px;margin-bottom:4px"><?= htmlspecialchars($v['title']) ?></div>
        <div style="font-size:11px;color:var(--text-dim);margin-bottom:12px"><?= htmlspecialchars($v['description']) ?></div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-warn btn-sm" onclick="editData(<?= $v['id'] ?>, '<?= htmlspecialchars(addslashes($v['title'])) ?>', '<?= htmlspecialchars(addslashes($v['video_url'])) ?>', '<?= htmlspecialchars(addslashes($v['description'])) ?>')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit
          </button>
          <a href="video.php?aksi=hapus&id=<?= $v['id'] ?>&<?= csrf_qs() ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus video ini?')">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- MODAL -->
<div class="modal-backdrop" id="modal-bd">
  <div class="modal">
    <form method="POST">
      <?= csrf_field() ?>
      <div class="modal-header">
        <span class="modal-title" id="modal-title">Tambah Video</span>
        <button type="button" class="modal-close" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="f-id">
        <div class="form-group">
          <label class="form-label">Judul Video</label>
          <input type="text" name="title" id="f-title" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">URL YouTube</label>
          <input type="text" name="video_url" id="f-url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Singkat</label>
          <textarea name="description" id="f-desc" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

</div></div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}
function openModal(){
  document.getElementById('modal-title').textContent='Tambah Video';
  ['f-id','f-title','f-url','f-desc'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('modal-bd').classList.add('open');
}
function editData(id,title,url,desc){
  document.getElementById('modal-title').textContent='Edit Video';
  document.getElementById('f-id').value=id;
  document.getElementById('f-title').value=title;
  document.getElementById('f-url').value=url;
  document.getElementById('f-desc').value=desc;
  document.getElementById('modal-bd').classList.add('open');
}
function closeModal(){document.getElementById('modal-bd').classList.remove('open')}
document.getElementById('modal-bd').addEventListener('click',function(e){if(e.target===this)closeModal()});
</script>
</body></html>
