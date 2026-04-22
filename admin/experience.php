<?php
require 'koneksi.php';
$page_title  = 'Experience';
$active_menu = 'experience';

$aksi = $_GET['aksi'] ?? 'tampil';
$pesan = '';

if ($aksi == 'hapus' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM experiences WHERE id = ?")->execute([$_GET['id']]);
    header("Location: experience.php?pesan=dihapus"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $job_title   = $_POST['job_title'];
    $company     = $_POST['company'];
    $year_range  = $_POST['year_range'];
    $description = $_POST['description'];
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($_POST['id'])) {
        $pdo->prepare("UPDATE experiences SET job_title=?, company=?, year_range=?, description=?, is_active=? WHERE id=?")
            ->execute([$job_title, $company, $year_range, $description, $is_active, $_POST['id']]);
        header("Location: experience.php?pesan=diedit");
    } else {
        $pdo->prepare("INSERT INTO experiences (job_title, company, year_range, description, is_active) VALUES (?,?,?,?,?)")
            ->execute([$job_title, $company, $year_range, $description, $is_active]);
        header("Location: experience.php?pesan=ditambah");
    }
    exit;
}

if (isset($_GET['pesan'])) {
    $map = ['ditambah'=>'Experience berhasil ditambahkan!','dihapus'=>'Experience berhasil dihapus.','diedit'=>'Experience berhasil diperbarui!'];
    $pesan = $map[$_GET['pesan']] ?? '';
}

$experiences = $pdo->query("SELECT * FROM experiences ORDER BY id DESC")->fetchAll();
require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Experience</h2>
    <p>Kelola riwayat pekerjaan</p>
  </div>
  <button class="btn btn-primary" onclick="openModal()">
    <i class="lucide lucide-plus"></i> Tambah
  </button>
</div>

<?php if ($pesan): ?>
<div class="alert alert-success"><span>✓</span> <?= $pesan ?></div>
<?php endif; ?>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Tahun</th>
          <th>Jabatan</th>
          <th>Perusahaan</th>
          <th>Status</th>
          <th style="width:100px">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($experiences)): ?>
          <tr><td colspan="5"><div class="empty-box"><i class="lucide lucide-briefcase"></i><p>Belum ada data experience.</p></div></td></tr>
        <?php else: ?>
          <?php foreach ($experiences as $exp): ?>
          <tr>
            <td style="font-family:var(--mono);font-size:11px;color:var(--text-dim)"><?= htmlspecialchars($exp['year_range']) ?></td>
            <td style="font-weight:500;color:var(--text-hi)"><?= htmlspecialchars($exp['job_title']) ?></td>
            <td><?= htmlspecialchars($exp['company']) ?></td>
            <td>
              <?php if ($exp['is_active']): ?>
                <span class="badge badge-green">● Aktif</span>
              <?php else: ?>
                <span class="badge badge-dim">Masa Lalu</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="td-action">
                <button class="btn btn-warn btn-sm btn-icon" onclick="editData(<?= $exp['id'] ?>, '<?= htmlspecialchars(addslashes($exp['job_title'])) ?>', '<?= htmlspecialchars(addslashes($exp['company'])) ?>', '<?= htmlspecialchars(addslashes($exp['year_range'])) ?>', `<?= addslashes(htmlspecialchars($exp['description'])) ?>`, <?= $exp['is_active'] ?>)" title="Edit">
                  <i class="lucide lucide-pencil"></i>
                </button>
                <a href="experience.php?aksi=hapus&id=<?= $exp['id'] ?>" class="btn btn-danger btn-sm btn-icon" onclick="return confirm('Hapus data ini?')" title="Hapus">
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
  <div class="modal modal-lg">
    <form method="POST">
      <div class="modal-header">
        <span class="modal-title" id="modal-title">Tambah Experience</span>
        <button type="button" class="modal-close" onclick="closeModal()">×</button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="f-id">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Jabatan / Job Title</label>
            <input type="text" name="job_title" id="f-job" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nama Perusahaan</label>
            <input type="text" name="company" id="f-company" class="form-control" required>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Tahun (Range)</label>
            <input type="text" name="year_range" id="f-year" class="form-control" placeholder="2022 — 2024" required>
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px">
            <div class="form-check">
              <input type="checkbox" name="is_active" id="f-active" value="1">
              <label for="f-active">Pekerjaan saat ini</label>
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Deskripsi (pisahkan tiap poin dengan Enter)</label>
          <textarea name="description" id="f-desc" class="form-control" rows="5"></textarea>
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

function openModal() {
  document.getElementById('modal-title').textContent = 'Tambah Experience';
  document.getElementById('f-id').value = '';
  document.getElementById('f-job').value = '';
  document.getElementById('f-company').value = '';
  document.getElementById('f-year').value = '';
  document.getElementById('f-desc').value = '';
  document.getElementById('f-active').checked = false;
  document.getElementById('modal-bd').classList.add('open');
}
function editData(id, job, company, year, desc, isActive) {
  document.getElementById('modal-title').textContent = 'Edit Experience';
  document.getElementById('f-id').value = id;
  document.getElementById('f-job').value = job;
  document.getElementById('f-company').value = company;
  document.getElementById('f-year').value = year;
  document.getElementById('f-desc').value = desc;
  document.getElementById('f-active').checked = (isActive == 1);
  document.getElementById('modal-bd').classList.add('open');
}
function closeModal() { document.getElementById('modal-bd').classList.remove('open'); }
document.getElementById('modal-bd').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body></html>
