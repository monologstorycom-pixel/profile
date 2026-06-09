<?php
/**
 * setup.php — One-time admin user creator.
 * Akses: http://localhost/profile/admin/setup.php
 *
 * Aturan:
 * - Hanya bisa diakses kalau tabel admin_users masih KOSONG.
 * - Kalau sudah ada user, file ini menolak akses & minta dihapus.
 * - Setelah berhasil membuat user, file ini akan menampilkan tombol untuk
 *   self-destruct (hapus dirinya sendiri).
 */

require 'koneksi.php';

// Cek jumlah user
try {
    $count = (int)$pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
} catch (Exception $e) {
    die("Tabel admin_users belum ada. Jalankan db_portfolio.sql dulu.");
}

$msg   = '';
$err   = '';
$done  = false;

// ── HANDLE SELF-DESTRUCT ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_me'])) {
    if (@unlink(__FILE__)) {
        header('Location: login.php?setup=ok'); exit;
    } else {
        $err = 'Gagal hapus file. Hapus admin/setup.php secara manual lewat file manager.';
    }
}

// ── HANDLE FORM CREATE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_me'])) {
    if ($count > 0) {
        $err = 'Sudah ada user admin. Halaman ini dikunci. Hapus file ini sekarang!';
    } else {
        $u = trim($_POST['username'] ?? '');
        $p = (string)($_POST['password'] ?? '');
        if (strlen($u) < 3 || strlen($u) > 50) {
            $err = 'Username harus 3-50 karakter.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $u)) {
            $err = 'Username hanya boleh huruf, angka, titik, underscore, dash.';
        } elseif (strlen($p) < 8) {
            $err = 'Password minimal 8 karakter.';
        } else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$u, $hash])) {
                $done  = true;
                $msg   = "User <strong>$u</strong> berhasil dibuat. Sekarang hapus file ini agar tidak disalahgunakan.";
                $count = 1;
            } else {
                $err = 'Gagal insert ke database.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Setup Admin — One-time only</title>
<link rel="icon" type="image/svg+xml" href="../favicon.svg">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#0d1014;color:#b4bac6;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;font-size:14px;line-height:1.6;background-image:radial-gradient(circle at 20% 0%,rgba(233,185,122,.06),transparent 40%)}
.card{width:100%;max-width:440px;background:#161b22;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:32px;box-shadow:0 24px 48px rgba(0,0,0,.5)}
.logo{display:grid;place-items:center;width:46px;height:46px;border-radius:11px;background:#e9b97a;color:#0d1014;margin:0 auto 14px;font-family:'JetBrains Mono',monospace;font-weight:700;font-size:16px}
h1{font-size:18px;color:#e6e9ef;text-align:center;margin-bottom:6px;font-weight:600}
.sub{font-family:'JetBrains Mono',monospace;font-size:11px;color:#6c7589;text-align:center;margin-bottom:24px;letter-spacing:.05em}
.fg{margin-bottom:14px}
label{display:block;font-size:11px;color:#b4bac6;margin-bottom:6px;font-weight:500;letter-spacing:.05em;text-transform:uppercase}
input{width:100%;padding:10px 12px;background:#0d1014;border:1px solid rgba(255,255,255,.13);border-radius:8px;color:#e6e9ef;font-size:13px;font-family:'Inter',sans-serif;outline:none;transition:border-color .15s ease,box-shadow .15s ease}
input:focus{border-color:#e9b97a;box-shadow:0 0 0 3px rgba(233,185,122,.15)}
.help{font-size:11px;color:#6c7589;margin-top:4px}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:11px;border:none;border-radius:9px;background:#e9b97a;color:#0d1014;font-size:13px;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;transition:background .15s ease}
.btn:hover{background:#f4cc94}
.btn-danger{background:#f87171;color:#fff}
.btn-danger:hover{background:#dc6464}
.btn-ghost{background:transparent;color:#b4bac6;border:1px solid rgba(255,255,255,.12)}
.btn-ghost:hover{background:rgba(255,255,255,.04);color:#e6e9ef}
.alert{padding:11px 14px;border-radius:8px;font-size:12.5px;margin-bottom:14px;line-height:1.55}
.alert-err{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:#f87171}
.alert-ok{background:rgba(124,194,138,.08);border:1px solid rgba(124,194,138,.2);color:#7cc28a}
.alert-info{background:rgba(125,169,201,.08);border:1px solid rgba(125,169,201,.2);color:#7da9c9}
.locked{text-align:center;padding:8px 0}
.locked-icon{font-size:32px;margin-bottom:10px;color:#f87171}
hr{border:0;border-top:1px solid rgba(255,255,255,.08);margin:18px 0}
.foot{text-align:center;font-size:11px;color:#6c7589;font-family:'JetBrains Mono',monospace;margin-top:16px}
.foot a{color:#e9b97a;text-decoration:none}
</style>
</head>
<body>

<div class="card">
  <div class="logo">RS</div>
  <h1>Setup Admin</h1>
  <p class="sub">// one-time setup · admin/setup.php</p>

  <?php if ($err): ?>
    <div class="alert alert-err">⚠ <?= $err ?></div>
  <?php endif; ?>

  <?php if ($done): ?>

    <div class="alert alert-ok">✓ <?= $msg ?></div>
    <div class="alert alert-info">
      <strong>Langkah selanjutnya:</strong><br>
      Klik tombol di bawah untuk menghapus file <code>setup.php</code> ini secara otomatis,
      lalu masuk ke halaman login.
    </div>
    <form method="POST">
      <input type="hidden" name="delete_me" value="1">
      <button type="submit" class="btn btn-danger">🗑 Hapus file ini &amp; lanjut ke login</button>
    </form>
    <hr>
    <p class="foot">Kalau penghapusan otomatis gagal, hapus manual lewat file manager / FTP.</p>

  <?php elseif ($count > 0): ?>

    <div class="locked">
      <div class="locked-icon">🔒</div>
      <strong style="color:#e6e9ef;display:block;margin-bottom:6px">Setup sudah selesai</strong>
      <p style="font-size:12.5px;color:#6c7589;margin-bottom:18px">
        Sudah ada <?= $count ?> user admin di database. Setup hanya boleh dijalankan sekali.
        <strong style="color:#f87171">Hapus file <code>admin/setup.php</code> sekarang juga.</strong>
      </p>
    </div>
    <form method="POST">
      <input type="hidden" name="delete_me" value="1">
      <button type="submit" class="btn btn-danger">🗑 Hapus file ini sekarang</button>
    </form>
    <hr>
    <a href="login.php" class="btn btn-ghost" style="display:flex;text-decoration:none">→ Lanjut ke halaman login</a>

  <?php else: ?>

    <div class="alert alert-info">
      ℹ Belum ada user admin di database. Buat sekarang. File ini akan menolak akses setelah user pertama dibuat.
    </div>
    <form method="POST">
      <div class="fg">
        <label for="u">Username</label>
        <input id="u" name="username" type="text" required autofocus
               minlength="3" maxlength="50" pattern="[a-zA-Z0-9_.\-]+"
               value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
        <div class="help">3–50 karakter · huruf, angka, _ . -</div>
      </div>
      <div class="fg">
        <label for="p">Password</label>
        <input id="p" name="password" type="password" required minlength="8">
        <div class="help">Minimal 8 karakter · simpan baik-baik, tidak bisa diambil ulang</div>
      </div>
      <button type="submit" class="btn">✓ Buat user admin</button>
    </form>

  <?php endif; ?>

  <p class="foot">© <?= date('Y') ?> · Setup file akan auto-locked setelah dipakai</p>
</div>

</body>
</html>
