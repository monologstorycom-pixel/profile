<?php
require 'koneksi.php';
$page_title  = 'Kategori';
$active_menu = 'kategori';

$aksi = $_GET['aksi'] ?? '';
$pesan = ''; $pesan_error = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM slws_categories WHERE id = ?")->execute([$_GET['id']]);
    header("Location: kategori.php?pesan=dihapus"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['id_edit'])) {
        $pdo->prepare("UPDATE slws_categories SET name=?, icon=? WHERE id=?")
            ->execute([$_POST['name'], $_POST['icon'], $_POST['id_edit']]);
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
  <button class="btn btn-primary" onclick="openModal()"><i class="lucide lucide-folder-plus"></i> Tambah</button>
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
          <tr><td colspan="5"><div class="empty-box"><i class="lucide lucide-folder-open"></i><p>Belum ada kategori.</p></div></td></tr>
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
                  <i class="lucide lucide-pencil"></i>
                </button>
                <a href="kategori.php?aksi=hapus&id=<?= $k['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('YAKIN? Ini akan menghapus semua foto di kategori ini!')">
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
