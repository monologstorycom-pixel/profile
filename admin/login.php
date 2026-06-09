<?php
require 'koneksi.php';

// Helper CSRF khusus halaman login (tidak require _auth.php karena belum login)
function login_csrf_token(): string {
    if (empty($_SESSION['login_csrf'])) {
        $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['login_csrf'];
}

// Sudah login? langsung ke dashboard.
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: index.php"); exit;
}

$error    = '';
$timeout  = isset($_GET['timeout']);
$now      = time();

// Inisialisasi tracker brute-force
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_locked_until'])) $_SESSION['login_locked_until'] = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // CSRF check
    $tok = $_POST['_csrf'] ?? '';
    if (!is_string($tok) || !hash_equals($_SESSION['login_csrf'] ?? '', $tok)) {
        $error = 'Sesi login kadaluarsa. Coba lagi.';
    }
    // Lockout check
    elseif ($_SESSION['login_locked_until'] > $now) {
        $sisa = $_SESSION['login_locked_until'] - $now;
        $error = "Terlalu banyak percobaan. Coba lagi dalam {$sisa} detik.";
    }
    else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Reset semua tracker brute-force
            $_SESSION['login_attempts']     = 0;
            $_SESSION['login_locked_until'] = 0;
            unset($_SESSION['login_csrf']);

            // Regenerate session ID — cegah session fixation
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username']        = $user['username'];
            $_SESSION['user_id']         = (int)$user['id'];
            $_SESSION['last_activity']   = $now;
            $_SESSION['regen_at']        = $now;

            header("Location: index.php"); exit;
        } else {
            $_SESSION['login_attempts']++;
            // Lock setelah 5 percobaan gagal: 60 detik per kelipatan 5.
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_locked_until'] = $now + 60 * (intdiv($_SESSION['login_attempts'], 5));
            }
            $error = "Username atau password salah.";
        }
    }
}

$csrf = login_csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Admin Panel</title>
<link rel="icon" type="image/svg+xml" href="../favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0a0c0f; --surface:#141720; --border:rgba(255,255,255,.07);
  --border2:rgba(255,255,255,.14);
  --text:#9ba3b5; --text-hi:#e2e8f4; --text-dim:#4a5270;
  --accent:#5b8ef0; --accent2:#3d6fd6;
  --red:#f87171;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Geist',system-ui,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background-image:radial-gradient(circle at 20% 0%,rgba(91,142,240,.06),transparent 40%),radial-gradient(circle at 80% 100%,rgba(167,139,250,.05),transparent 40%)}
.login-card{width:100%;max-width:380px;background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:32px;box-shadow:0 24px 48px rgba(0,0,0,.4)}
.logo{display:flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#5b8ef0,#a78bfa);margin:0 auto 16px;color:#fff;font-weight:700;font-family:'Geist Mono',monospace;font-size:18px}
h1{font-size:18px;color:var(--text-hi);text-align:center;font-weight:600;margin-bottom:6px}
.sub{font-size:12px;color:var(--text-dim);text-align:center;margin-bottom:26px;font-family:'Geist Mono',monospace}
.fg{margin-bottom:14px}
label{display:block;font-size:11px;color:var(--text);margin-bottom:6px;font-weight:500;letter-spacing:.04em;text-transform:uppercase}
input{width:100%;padding:10px 12px;background:#0f1115;border:1px solid var(--border2);border-radius:8px;color:var(--text-hi);font-size:13px;font-family:'Geist',sans-serif;outline:none;transition:border-color .15s,box-shadow .15s}
input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(91,142,240,.15)}
.btn{width:100%;padding:11px;border:none;border-radius:8px;background:var(--accent);color:#fff;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s;margin-top:4px;font-family:'Geist',sans-serif}
.btn:hover{background:var(--accent2)}
.btn:disabled{opacity:.6;cursor:not-allowed}
.alert{padding:10px 12px;border-radius:8px;font-size:12px;margin-bottom:14px;display:flex;gap:8px;align-items:flex-start;line-height:1.5}
.alert-err{background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);color:var(--red)}
.alert-info{background:rgba(91,142,240,.08);border:1px solid rgba(91,142,240,.2);color:var(--accent)}
.foot{text-align:center;margin-top:18px;font-size:11px;color:var(--text-dim);font-family:'Geist Mono',monospace}
.foot a{color:var(--accent);text-decoration:none}
</style>
</head>
<body>
<div class="login-card">
  <div class="logo">RS</div>
  <h1>Admin Panel</h1>
  <p class="sub">// rsby.my.id</p>

  <?php if ($timeout): ?>
    <div class="alert alert-info"><span>ⓘ</span> Sesi berakhir karena tidak ada aktivitas. Silakan login kembali.</div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-err"><span>⚠</span> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" autocomplete="on">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="fg">
      <label for="u">Username</label>
      <input id="u" name="username" type="text" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="fg">
      <label for="p">Password</label>
      <input id="p" name="password" type="password" required>
    </div>
    <button class="btn" type="submit">Sign in</button>
  </form>

  <div class="foot">
    <a href="../">← Kembali ke website</a>
  </div>
</div>
</body>
</html>
