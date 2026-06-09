<?php
require 'koneksi.php';
require '_auth.php';
$page_title  = 'Kategori';
$active_menu = 'kategori';

$aksi = $_GET['aksi'] ?? '';
$pesan = ''; $pesan_error = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    csrf_check_get();
    // Validasi slug agar tidak bisa lewatkan path traversal
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', (string)$_GET['id']);
    if ($slug !== '') {
        $row = $pdo->prepare("SELECT name FROM slws_categories WHERE id=?"); $row->execute([$slug]); $kn = $row->fetchColumn();
        $pdo->prepare("DELETE FROM slws_categories WHERE id = ?")->execute([$slug]);
        log_activity($pdo, 'Menghapus', 'Kategori', $slug, (string)$kn);
    }
    header("Location: kategori.php?pesan=dihapus"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['id_edit'])) {
        $pdo->prepare("UPDATE slws_categories SET name=?, icon=? WHERE id=?")
            ->execute([$_POST['name'], $_POST['icon'], $_POST['id_edit']]);
        log_activity($pdo, 'Mengedit', 'Kategori', $_POST['id_edit'], $_POST['name']);
        header("Location: kategori.php?pesan=diedit"); exit;
    } else {
        $id_kategori = strtolower(str_replace(' ', '-', trim($_POST['name'])));
        $cek = $pdo->prepare("SELECT id FROM slws_categories WHERE id = ?");
        $cek->execute([$id_kategori]);
        if ($cek->rowCount() > 0) {
            $pesan_error = "Kategori dengan nama tersebut sudah ada!";
        } else {
            $pdo->prepare("INSERT INTO slws_categories (id, name, icon) VALUES (?,?,?)")
                ->execute([$id_kategori, $_POST['name'], $_POST['icon']]);
            log_activity($pdo, 'Menambah', 'Kategori', $id_kategori, $_POST['name']);
            header("Location: kategori.php?pesan=ditambah"); exit;
        }
    }
}

$map = ['ditambah'=>'Kategori berhasil ditambahkan!','dihapus'=>'Kategori dihapus.','diedit'=>'Kategori diperbarui!'];
if (isset($_GET['pesan'])) $pesan = $map[$_GET['pesan']] ?? '';

$kategori = $pdo->query("SELECT c.*, COUNT(p.id) as total FROM slws_categories c LEFT JOIN slws_photos p ON c.id = p.category_id GROUP BY c.id")->fetchAll();
require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Kategori Foto</h2>
    <p>Folder pengelompokan foto galeri</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg> Tambah</button>
</div>

<?php if ($pesan): ?><div class="alert alert-success"><span>✓</span> <?= $pesan ?></div><?php endif; ?>
<?php if ($pesan_error): ?><div class="alert alert-danger"><span>⚠</span> <?= $pesan_error ?></div><?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Icon</th>
          <th>Nama Kategori</th>
          <th>ID Slug</th>
          <th>Foto</th>
          <th style="width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($kategori)): ?>
          <tr><td colspan="5"><div class="empty-box"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg><p>Belum ada kategori.</p></div></td></tr>
        <?php else: ?>
          <?php foreach ($kategori as $k): ?>
          <tr>
            <td style="text-align:center"><i class="fas <?= htmlspecialchars($k['icon']) ?>" style="font-size:16px;color:var(--accent)"></i></td>
            <td style="font-weight:500;color:var(--text-hi)"><?= htmlspecialchars($k['name']) ?></td>
            <td><span class="badge badge-dim"><?= htmlspecialchars($k['id']) ?></span></td>
            <td style="font-family:var(--mono);font-size:12px;color:var(--text-dim)"><?= $k['total'] ?></td>
            <td>
              <div class="td-action">
                <button class="btn btn-warn btn-sm btn-icon" onclick="editData('<?= $k['id'] ?>', '<?= htmlspecialchars(addslashes($k['name'])) ?>', '<?= htmlspecialchars(addslashes($k['icon'])) ?>')">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <a href="kategori.php?aksi=hapus&id=<?= urlencode($k['id']) ?>&<?= csrf_qs() ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('YAKIN? Ini akan menghapus semua foto di kategori ini!')">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
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
      <?= csrf_field() ?>
      <div class="modal-header">
        <span class="modal-title" id="modal-title">Tambah Kategori</span>
        <button type="button" class="modal-close" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_edit" id="f-id">
        <div class="form-group">
          <label class="form-label">Nama Kategori</label>
          <input type="text" name="name" id="f-name" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Icon (FontAwesome class)</label>
          <input type="text" name="icon" id="f-icon" class="form-control" placeholder="fa-camera">
          <div class="form-sub">Contoh: fa-camera, fa-heart, fa-ring, fa-star</div>
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
  document.getElementById('modal-title').textContent='Tambah Kategori';
  document.getElementById('f-id').value='';
  document.getElementById('f-name').value='';
  document.getElementById('f-icon').value='';
  document.getElementById('modal-bd').classList.add('open');
}
function editData(id,name,icon){
  document.getElementById('modal-title').textContent='Edit Kategori';
  document.getElementById('f-id').value=id;
  document.getElementById('f-name').value=name;
  document.getElementById('f-icon').value=icon;
  document.getElementById('modal-bd').classList.add('open');
}
function closeModal(){document.getElementById('modal-bd').classList.remove('open')}
document.getElementById('modal-bd').addEventListener('click',function(e){if(e.target===this)closeModal()});
</script>
</body></html>
