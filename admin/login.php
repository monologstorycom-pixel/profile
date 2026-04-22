<?php
require 'koneksi.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php"); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        header("Location: index.php"); exit;
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Admin Panel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --bg:     #0a0c0f;
  --surface:#141720;
  --border: rgba(255,255,255,0.08);
  --text:   #9ba3b5;
  --text-hi:#e2e8f4;
  --text-dim:#4a5270;
  --accent: #5b8ef0;
  --red:    #f87171;
  --font:   'Geist', system-ui, sans-serif;
  --mono:   'Geist Mono', monospace;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body {
  font-family: var(--font); background: var(--bg); color: var(--text);
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  padding: 20px;
}

/* Grid noise BG */
body::before {
  content:''; position:fixed; inset:0; pointer-events:none;
  background-image:
    linear-gradient(rgba(91,142,240,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(91,142,240,0.04) 1px, transparent 1px);
  background-size: 48px 48px;
}

/* Glow blob */
body::after {
  content:''; position:fixed;
  width:500px; height:500px;
  background: radial-gradient(circle, rgba(91,142,240,0.06) 0%, transparent 70%);
  top: 50%; left: 50%; transform: translate(-50%, -60%);
  pointer-events: none;
}

.login-box {
  width: 100%; max-width: 380px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 36px 32px;
  position: relative; z-index: 1;
  box-shadow: 0 24px 64px rgba(0,0,0,0.6);
  animation: up 0.4s ease both;
}
@keyframes up { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:none} }

.login-logo {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 28px;
}
.login-logo-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #5b8ef0, #a78bfa);
  display: flex; align-items: center; justify-content: center;
  font-size: 16px; color: #fff;
}
.login-logo h1 { font-size: 16px; font-weight: 600; color: var(--text-hi); }
.login-logo p  { font-size: 11px; color: var(--text-dim); font-family: var(--mono); }

.form-group { margin-bottom: 14px; }
.form-label { display: block; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--text-dim); margin-bottom: 7px; }
.form-control {
  width: 100%; padding: 10px 13px;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  border-radius: 8px; color: var(--text-hi);
  font-size: 13px; font-family: var(--font);
  outline: none; transition: border-color 0.15s, box-shadow 0.15s;
}
.form-control:focus { border-color: #5b8ef0; box-shadow: 0 0 0 3px rgba(91,142,240,0.12); }
.form-control::placeholder { color: var(--text-dim); }

.error-msg {
  display: flex; align-items: center; gap: 8px;
  padding: 11px 14px; border-radius: 8px; font-size: 12px;
  background: rgba(248,113,113,0.08); color: var(--red);
  border: 1px solid rgba(248,113,113,0.18); margin-bottom: 16px;
}

.btn-login {
  width: 100%; padding: 11px;
  background: #5b8ef0;
  border: none; border-radius: 8px;
  color: #fff; font-size: 13px; font-weight: 600;
  font-family: var(--font); cursor: pointer;
  transition: background 0.15s, transform 0.1s;
  margin-top: 4px;
}
.btn-login:hover { background: #3d6fd6; }
.btn-login:active { transform: scale(0.99); }

.login-footer {
  text-align: center; margin-top: 22px;
  font-size: 11px; color: var(--text-dim); font-family: var(--mono);
}
</style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <div class="login-logo-icon">⚡</div>
    <div>
      <h1>Admin Panel</h1>
      <p>// secure login</p>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="error-msg">⚠ <?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" placeholder="admin" autofocus required>
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn-login">Masuk →</button>
  </form>

  <div class="login-footer">rizqisubagyo · portfolio admin</div>
</div>
</body>
</html>
