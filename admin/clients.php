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

<style>
.two-col-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 16px;
    align-items: start;
}
@media (max-width: 768px) {
    .two-col-layout {
        grid-template-columns: 1fr;
    }
    /* Di mobile, form tambah muncul duluan (lebih penting dari tabel) */
    .two-col-layout .card-table { order: 2; }
    .two-col-layout .card-form  { order: 1; }
}

/* Tabel client: sembunyikan kolom kurang penting di mobile */
@media (max-width: 600px) {
    .col-urutan, .col-url { display: none; }
}
</style>

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

<div class="two-col-layout">

  <!-- Tabel list -->
  <div class="card card-table">
    <div class="card-header">
      <span class="card-title">Daftar Client</span>
      <span class="badge badge-dim"><?= count($clients) ?> klien</span>
    </div>
    <div style="overflow-x:auto">
      <?php if (empty($clients)): ?>
        <p style="padding:20px;color:var(--text-dim)">Belum ada client. Tambahkan di form di atas.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Icon</th>
              <th>Nama</th>
              <th>Lokasi</th>
              <th class="col-url">URL</th>
              <th class="col-urutan">Urutan</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
              <td><i class="<?= htmlspecialchars($c['icon_class']) ?>" style="font-size:16px;color:var(--accent)"></i></td>
              <td style="font-weight:500;color:var(--text-hi)"><?= htmlspecialchars($c['name']) ?></td>
              <td style="color:var(--text-dim);font-size:12px"><?= htmlspecialchars($c['location'] ?? '-') ?></td>
              <td class="col-url" style="font-size:12px">
                <?php if ($c['url']): ?>
                  <a href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener"
                     style="color:var(--accent);text-decoration:none">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg> Link
                  </a>
                <?php else: ?>
                  <span style="color:var(--text-dim)">—</span>
                <?php endif; ?>
              </td>
              <td class="col-urutan" style="color:var(--text-dim);font-size:12px"><?= $c['sort_order'] ?></td>
              <td style="text-align:right;white-space:nowrap">
                <a href="clients.php?edit=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg> Edit
                </a>
                <a href="clients.php?hapus=<?= $c['id'] ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Hapus client <?= htmlspecialchars($c['name']) ?>?')">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
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
  <div class="card card-form">
    <div class="card-header">
      <span class="card-title"><?= $edit ? 'Edit Client' : 'Tambah Client' ?></span>
      <?php if ($edit): ?>
        <a href="clients.php" class="btn btn-ghost btn-sm"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Batal</a>
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
          <?= $edit ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>' : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>' ?>
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
