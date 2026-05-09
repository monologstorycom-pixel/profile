<?php
require 'koneksi.php';
$page_title  = 'Skills';
$active_menu = 'skills';

$pesan = '';
$error = '';

// HAPUS
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM skills WHERE id=?")->execute([(int)$_GET['hapus']]);
    header("Location: skills.php?ok=hapus"); exit;
}

// TAMBAH / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = trim($_POST['group_name'] ?? '');
    $skill = trim($_POST['skill_name'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 0);
    $id    = (int)($_POST['id'] ?? 0);

    if ($group === '' || $skill === '') {
        $error = 'Nama kelompok dan skill tidak boleh kosong.';
    } elseif ($id > 0) {
        $pdo->prepare("UPDATE skills SET group_name=?, skill_name=?, sort_order=? WHERE id=?")
            ->execute([$group, $skill, $order, $id]);
        header("Location: skills.php?ok=edit"); exit;
    } else {
        $pdo->prepare("INSERT INTO skills (group_name, skill_name, sort_order) VALUES (?,?,?)")
            ->execute([$group, $skill, $order]);
        header("Location: skills.php?ok=tambah"); exit;
    }
}

// Edit mode
$edit = null;
if (isset($_GET['edit'])) {
    $edit = $pdo->prepare("SELECT * FROM skills WHERE id=?");
    $edit->execute([(int)$_GET['edit']]);
    $edit = $edit->fetch();
}

// List semua, group by group_name
$skills_raw = $pdo->query("SELECT * FROM skills ORDER BY group_name, sort_order, skill_name")->fetchAll();
$groups = [];
foreach ($skills_raw as $s) {
    $groups[$s['group_name']][] = $s;
}

require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Skills</h2>
    <p>Kelola daftar skill yang tampil di halaman portfolio</p>
  </div>
</div>

<?php if (isset($_GET['ok'])): ?>
  <div class="alert alert-success"><span>✓</span>
    <?= $_GET['ok']==='tambah' ? 'Skill berhasil ditambahkan!' : ($_GET['ok']==='edit' ? 'Skill berhasil diperbarui!' : 'Skill berhasil dihapus!') ?>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><span>✕</span> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:16px;align-items:start">

  <!-- Tabel list -->
  <div class="card">
    <div class="card-header"><span class="card-title">Daftar Skill</span></div>
    <div style="overflow-x:auto">
      <?php if (empty($skills_raw)): ?>
        <p style="padding:20px;color:var(--text-dim)">Belum ada skill. Tambahkan di form sebelah kanan.</p>
      <?php else: ?>
        <?php foreach ($groups as $gname => $items): ?>
          <div style="padding:10px 16px 4px;font-size:10px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--text-dim)">
            <?= htmlspecialchars($gname) ?>
          </div>
          <table class="table" style="margin-bottom:0">
            <tbody>
              <?php foreach ($items as $s): ?>
              <tr>
                <td style="padding-left:20px">
                  <span class="badge badge-dim"><?= htmlspecialchars($s['skill_name']) ?></span>
                </td>
                <td style="color:var(--text-dim);font-size:12px">urutan: <?= $s['sort_order'] ?></td>
                <td style="text-align:right;white-space:nowrap">
                  <a href="skills.php?edit=<?= $s['id'] ?>" class="btn btn-ghost btn-sm">
                    <i class="lucide lucide-pencil"></i> Edit
                  </a>
                  <a href="skills.php?hapus=<?= $s['id'] ?>" class="btn btn-danger btn-sm"
                     onclick="return confirm('Hapus skill <?= htmlspecialchars($s['skill_name']) ?>?')">
                    <i class="lucide lucide-trash-2"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Form tambah / edit -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?= $edit ? 'Edit Skill' : 'Tambah Skill' ?></span>
      <?php if ($edit): ?>
        <a href="skills.php" class="btn btn-ghost btn-sm"><i class="lucide lucide-x"></i> Batal</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <form method="POST">
        <?php if ($edit): ?>
          <input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nama Kelompok</label>
          <input type="text" name="group_name" class="form-control"
                 placeholder="cth: Programming, Networking, Infrastructure"
                 value="<?= htmlspecialchars($edit['group_name'] ?? '') ?>" required>
          <?php if (!empty($groups)): ?>
            <div class="form-sub" style="margin-top:5px">Kelompok yang ada:
              <?= implode(', ', array_map('htmlspecialchars', array_keys($groups))) ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Nama Skill</label>
          <input type="text" name="skill_name" class="form-control"
                 placeholder="cth: PHP, Docker, MikroTik"
                 value="<?= htmlspecialchars($edit['skill_name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Urutan</label>
          <input type="number" name="sort_order" class="form-control" min="0"
                 value="<?= htmlspecialchars($edit['sort_order'] ?? 0) ?>">
          <div class="form-sub">Angka kecil tampil lebih dulu</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
          <i class="lucide lucide-<?= $edit ? 'save' : 'plus' ?>"></i>
          <?= $edit ? 'Simpan Perubahan' : 'Tambah Skill' ?>
        </button>
      </form>
    </div>
  </div>

</div>

</div></div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}
</script>
</body></html>