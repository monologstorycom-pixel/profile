<?php
require 'koneksi.php';
$page_title  = 'Clients';
$active_menu = 'clients';

$pesan = '';
$error = '';

// HAPUS
if (isset($_GET['hapus'])) {
    $pdo->prepare("DELETE FROM clients WHERE id=?")->execute([(int)$_GET['hapus']]);
    header("Location: clients.php?ok=hapus"); exit;
}

// TAMBAH / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $loc   = trim($_POST['location'] ?? '');
    $url   = trim($_POST['url'] ?? '');
    $icon  = trim($_POST['icon_class'] ?? 'fas fa-building');
    $order = (int)($_POST['sort_order'] ?? 0);
    $id    = (int)($_POST['id'] ?? 0);

    if ($name === '') {
        $error = 'Nama client tidak boleh kosong.';
    } elseif ($id > 0) {
        $pdo->prepare("UPDATE clients SET name=?, location=?, url=?, icon_class=?, sort_order=? WHERE id=?")
            ->execute([$name, $loc, $url ?: null, $icon, $order, $id]);
        header("Location: clients.php?ok=edit"); exit;
    } else {
        $pdo->prepare("INSERT INTO clients (name, location, url, icon_class, sort_order) VALUES (?,?,?,?,?)")
            ->execute([$name, $loc, $url ?: null, $icon, $order]);
        header("Location: clients.php?ok=tambah"); exit;
    }
}

// Edit mode
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$clients = $pdo->query("SELECT * FROM clients ORDER BY sort_order, id")->fetchAll();

require '_layout.php';
?>

<div class="page-head">
  <div class="page-head-left">
    <h2>Clients</h2>
    <p>Kelola daftar klien yang tampil di halaman portfolio</p>
  </div>
</div>

<?php if (isset($_GET['ok'])): ?>
  <div class="alert alert-success"><span>✓</span>
    <?= $_GET['ok']==='tambah' ? 'Client berhasil ditambahkan!' : ($_GET['ok']==='edit' ? 'Client berhasil diperbarui!' : 'Client berhasil dihapus!') ?>
  </div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><span>✕</span> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start">

  <!-- Tabel list -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Daftar Client</span>
      <span class="badge badge-dim"><?= count($clients) ?> klien</span>
    </div>
    <div style="overflow-x:auto">
      <?php if (empty($clients)): ?>
        <p style="padding:20px;color:var(--text-dim)">Belum ada client. Tambahkan di form sebelah kanan.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Icon</th>
              <th>Nama</th>
              <th>Lokasi</th>
              <th>URL</th>
              <th>Urutan</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
              <td><i class="<?= htmlspecialchars($c['icon_class']) ?>" style="font-size:16px;color:var(--accent)"></i></td>
              <td style="font-weight:500;color:var(--text-hi)"><?= htmlspecialchars($c['name']) ?></td>
              <td style="color:var(--text-dim);font-size:12px"><?= htmlspecialchars($c['location'] ?? '-') ?></td>
              <td style="font-size:12px">
                <?php if ($c['url']): ?>
                  <a href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener"
                     style="color:var(--accent);text-decoration:none">
                    <i class="lucide lucide-external-link" style="font-size:11px"></i> Link
                  </a>
                <?php else: ?>
                  <span style="color:var(--text-dim)">—</span>
                <?php endif; ?>
              </td>
              <td style="color:var(--text-dim);font-size:12px"><?= $c['sort_order'] ?></td>
              <td style="text-align:right;white-space:nowrap">
                <a href="clients.php?edit=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">
                  <i class="lucide lucide-pencil"></i> Edit
                </a>
                <a href="clients.php?hapus=<?= $c['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus client <?= htmlspecialchars($c['name']) ?>?')">
                  <i class="lucide lucide-trash-2"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- Form -->
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?= $edit ? 'Edit Client' : 'Tambah Client' ?></span>
      <?php if ($edit): ?>
        <a href="clients.php" class="btn btn-ghost btn-sm"><i class="lucide lucide-x"></i> Batal</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <form method="POST">
        <?php if ($edit): ?>
          <input type="hidden" name="id" value="<?= $edit['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nama Client <span style="color:var(--red)">*</span></label>
          <input type="text" name="name" class="form-control"
                 placeholder="cth: PT Behaestex"
                 value="<?= htmlspecialchars($edit['name'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Lokasi</label>
          <input type="text" name="location" class="form-control"
                 placeholder="cth: Wonopringgo, Kab. Pekalongan"
                 value="<?= htmlspecialchars($edit['location'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">URL Website</label>
          <input type="text" name="url" class="form-control"
                 placeholder="https://... (kosongkan jika tidak ada)"
                 value="<?= htmlspecialchars($edit['url'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Icon (Font Awesome class)</label>
          <input type="text" name="icon_class" class="form-control"
                 placeholder="cth: fas fa-hospital"
                 value="<?= htmlspecialchars($edit['icon_class'] ?? 'fas fa-building') ?>">
          <div class="form-sub" style="margin-top:5px">
            Preview: <i class="<?= htmlspecialchars($edit['icon_class'] ?? 'fas fa-building') ?>" id="icon-preview" style="color:var(--accent)"></i>
            &nbsp;·&nbsp; Cari icon: <a href="https://fontawesome.com/icons" target="_blank" style="color:var(--accent)">fontawesome.com/icons</a>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Urutan Tampil</label>
          <input type="number" name="sort_order" class="form-control" min="0"
                 value="<?= htmlspecialchars($edit['sort_order'] ?? 0) ?>">
          <div class="form-sub">Angka kecil tampil lebih dulu</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%">
          <i class="lucide lucide-<?= $edit ? 'save' : 'plus' ?>"></i>
          <?= $edit ? 'Simpan Perubahan' : 'Tambah Client' ?>
        </button>
      </form>
    </div>
  </div>

</div>

</div></div>
<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}

// Live icon preview
const iconInput = document.querySelector('input[name="icon_class"]');
const iconPreview = document.getElementById('icon-preview');
if (iconInput && iconPreview) {
    iconInput.addEventListener('input', () => {
        iconPreview.className = iconInput.value.trim();
        iconPreview.style.color = 'var(--accent)';
    });
}
</script>
</body></html>