<?php
require 'koneksi.php';
$page_title  = 'Projects';
$active_menu = 'projects';

$aksi = $_GET['aksi'] ?? '';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$_GET['id']]);
    header("Location: projects.php?pesan=dihapus"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $icon_class  = $_POST['icon_class'];
    $link_url    = $_POST['link_url'];

    if (!empty($_POST['id'])) {
        $pdo->prepare("UPDATE projects SET title=?, description=?, icon_class=?, link_url=? WHERE id=?")
            ->execute([$title, $description, $icon_class, $link_url, $_POST['id']]);
        header("Location: projects.php?pesan=diedit");
    } else {
        $pdo->prepare("INSERT INTO projects (title, description, icon_class, link_url) VALUES (?,?,?,?)")
            ->execute([$title, $description, $icon_class, $link_url]);
        header("Location: projects.php?pesan=ditambah");
    }
    exit;
}

$map = ['ditambah'=>'Project berhasil ditambahkan!','dihapus'=>'Project berhasil dihapus.','diedit'=>'Project berhasil diperbarui!'];
if (isset($_GET['pesan'])) $pesan = $map[$_GET['pesan']] ?? '';

$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Projects</h2>
    <p>Kelola kartu project di halaman depan</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
    <i class="lucide lucide-plus"></i> Tambah
  </button>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><span>✓</span> <?= $pesan ?></div><?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:50px">Icon</th>
          <th>Project</th>
          <th>Deskripsi</th>
          <th>Link</th>
          <th style="width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($projects)): ?>
          <tr><td colspan="5"><div class="empty-box"><i class="lucide lucide-folder-open"></i><p>Belum ada project.</p></div></td></tr>
        <?php else: ?>
          <?php foreach ($projects as $p): ?>
          <tr>
            <td style="text-align:center"><i class="<?= htmlspecialchars($p['icon_class']) ?>" style="font-size:18px;color:var(--accent)"></i></td>
            <td style="font-weight:500;color:var(--text-hi)"><?= htmlspecialchars($p['title']) ?></td>
            <td style="color:var(--text-dim);font-size:12px;max-width:200px"><?= htmlspecialchars($p['description']) ?></td>
            <td>
              <?php if ($p['link_url']): ?>
                <a href="<?= htmlspecialchars($p['link_url']) ?>" target="_blank" class="btn btn-ghost btn-sm" style="gap:4px">
                  <i class="lucide lucide-external-link"></i> Buka
                </a>
              <?php else: ?>
                <span style="color:var(--text-dim);font-size:12px">—</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="td-action">
                <button class="btn btn-warn btn-sm btn-icon" onclick="editData(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['title'])) ?>', '<?= htmlspecialchars(addslashes($p['icon_class'])) ?>', '<?= htmlspecialchars(addslashes($p['link_url'])) ?>', `<?= addslashes(htmlspecialchars($p['description'])) ?>`)">
                  <i class="lucide lucide-pencil"></i>
                </button>
                <a href="projects.php?aksi=hapus&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Hapus project ini?')">
                  <i class="lucide lucide-trash-2"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL -->
<div class="modal-backdrop" id="modal-bd">
  <div class="modal">
    <form method="POST">
      <div class="modal-header">
        <span class="modal-title" id="modal-title">Tambah Project</span>
        <button type="button" class="modal-close" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="f-id">
        <div class="form-group">
          <label class="form-label">Nama Project</label>
          <input type="text" name="title" id="f-title" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Icon Class (FontAwesome / Lucide)</label>
          <input type="text" name="icon_class" id="f-icon" class="form-control" placeholder="fas fa-server">
          <div class="form-sub">Contoh: fas fa-server, fas fa-database, fas fa-network-wired</div>
        </div>
        <div class="form-group">
          <label class="form-label">Link URL (opsional)</label>
          <input type="text" name="link_url" id="f-link" class="form-control" placeholder="https://...">
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi Singkat</label>
          <textarea name="description" id="f-desc" class="form-control" rows="3" required></textarea>
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
  document.getElementById('modal-title').textContent='Tambah Project';
  ['f-id','f-title','f-icon','f-link','f-desc'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('modal-bd').classList.add('open');
}
function editData(id,title,icon,link,desc){
  document.getElementById('modal-title').textContent='Edit Project';
  document.getElementById('f-id').value=id;
  document.getElementById('f-title').value=title;
  document.getElementById('f-icon').value=icon;
  document.getElementById('f-link').value=link;
  document.getElementById('f-desc').value=desc;
  document.getElementById('modal-bd').classList.add('open');
}
function closeModal(){document.getElementById('modal-bd').classList.remove('open')}
document.getElementById('modal-bd').addEventListener('click',function(e){if(e.target===this)closeModal()});
</script>
</body></html>
