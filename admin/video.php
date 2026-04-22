<?php
require 'koneksi.php';
$page_title  = 'Video Portfolio';
$active_menu = 'video';

$aksi = $_GET['aksi'] ?? '';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$_GET['id']]);
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
  <button class="btn btn-primary" onclick="openModal()"><i class="lucide lucide-plus"></i> Tambah</button>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><span>✓</span> <?= $pesan ?></div><?php endif; ?>

<?php if (empty($videos)): ?>
  <div class="empty-box" style="padding:80px"><i class="lucide lucide-clapperboard"></i><p>Belum ada video.</p></div>
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
              <i class="lucide lucide-play" style="color:#fff;font-size:16px;margin-left:2px"></i>
            </div>
          </div>
        </div>
      <?php endif; ?>
      <div class="card-body">
        <div style="font-weight:600;color:var(--text-hi);font-size:13px;margin-bottom:4px"><?= htmlspecialchars($v['title']) ?></div>
        <div style="font-size:11px;color:var(--text-dim);margin-bottom:12px"><?= htmlspecialchars($v['description']) ?></div>
        <div style="display:flex;gap:8px">
          <button class="btn btn-warn btn-sm" onclick="editData(<?= $v['id'] ?>, '<?= htmlspecialchars(addslashes($v['title'])) ?>', '<?= htmlspecialchars(addslashes($v['video_url'])) ?>', '<?= htmlspecialchars(addslashes($v['description'])) ?>')">
            <i class="lucide lucide-pencil"></i> Edit
          </button>
          <a href="video.php?aksi=hapus&id=<?= $v['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus video ini?')">
            <i class="lucide lucide-trash-2"></i>
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
