<?php
// _layout.php — shared header + sidebar
// Set $page_title and $active_menu before requiring.

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$menu = [
    ['href' => 'index.php',      'icon' => 'grid-2x2',     'label' => 'Dashboard',   'key' => 'dashboard'],
    ['href' => 'profil.php',     'icon' => 'user-round',   'label' => 'Profil',      'key' => 'profil'],
    ['href' => 'experience.php', 'icon' => 'briefcase',    'label' => 'Experience',  'key' => 'experience'],
    ['href' => 'projects.php',   'icon' => 'code-2',       'label' => 'Projects',    'key' => 'projects'],
    ['href' => 'video.php',      'icon' => 'clapperboard', 'label' => 'Video',       'key' => 'video'],
];
$menu_visual = [
    ['href' => 'kategori.php',   'icon' => 'folder-open',  'label' => 'Kategori',    'key' => 'kategori'],
    ['href' => 'galeri.php',     'icon' => 'images',       'label' => 'Galeri Foto', 'key' => 'galeri'],
];
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? 'Admin' ?> — Panel</title>

<!-- Preconnect only, fonts load async to avoid render-blocking -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet"></noscript>

<!-- Lucide icons as inline SVG sprite via CSS — no external font file -->
<link rel="preload" href="https://unpkg.com/lucide-static@latest/font/lucide.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link href="https://unpkg.com/lucide-static@latest/font/lucide.css" rel="stylesheet"></noscript>

<style>
/* ── THEMES ── */
[data-theme="dark"] {
  --bg:       #0a0c0f;
  --bg2:      #0f1115;
  --surface:  #141720;
  --surface2: #1a1e28;
  --border:   rgba(255,255,255,0.07);
  --border2:  rgba(255,255,255,0.12);
  --text:     #9ba3b5;
  --text-hi:  #e2e8f4;
  --text-dim: #4a5270;
  --accent:   #5b8ef0;
  --accent2:  #3d6fd6;
  --accent-g: linear-gradient(135deg, #5b8ef0, #a78bfa);
  --green:    #34d399;
  --amber:    #fbbf24;
  --red:      #f87171;
  --shadow:   0 4px 24px rgba(0,0,0,0.5);
}
[data-theme="light"] {
  --bg:       #f4f6fb;
  --bg2:      #edf0f7;
  --surface:  #ffffff;
  --surface2: #e8ecf5;
  --border:   rgba(0,0,0,0.08);
  --border2:  rgba(0,0,0,0.14);
  --text:     #4a5568;
  --text-hi:  #1a202c;
  --text-dim: #a0aec0;
  --accent:   #3b74e0;
  --accent2:  #2c5fcb;
  --accent-g: linear-gradient(135deg, #3b74e0, #9061f9);
  --green:    #10b981;
  --amber:    #f59e0b;
  --red:      #ef4444;
  --shadow:   0 4px 24px rgba(0,0,0,0.08);
}

:root {
  --r:        10px;
  --r2:       7px;
  --sidebar:  220px;
  --font:     'Geist', system-ui, sans-serif;
  --mono:     'Geist Mono', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; scroll-behavior: smooth; }
body {
  font-family: var(--font);
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  line-height: 1.6;
  display: flex;
  /* GPU-accelerate background transitions */
  transition: background 0.2s, color 0.2s;
  will-change: background;
}

::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-track { background: var(--bg); }
::-webkit-scrollbar-thumb { background: var(--surface2); border-radius: 4px; }

/* ── THEME TOGGLE BUTTON ── */
.theme-toggle {
  background: var(--surface2);
  border: 1px solid var(--border2);
  border-radius: 99px;
  padding: 5px 10px;
  cursor: pointer;
  display: flex; align-items: center; gap: 6px;
  font-size: 11px; font-weight: 500;
  color: var(--text); font-family: var(--font);
  transition: all 0.15s;
  white-space: nowrap;
}
.theme-toggle:hover { color: var(--text-hi); border-color: var(--accent); }
.theme-toggle i { font-size: 12px; }

/* ── SIDEBAR ── */
.sidebar {
  width: var(--sidebar);
  min-height: 100vh;
  background: var(--bg2);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0;
  z-index: 100;
  transition: transform 0.3s ease, background 0.2s, border-color 0.2s;
}

.sb-logo {
  padding: 20px 18px 16px;
  border-bottom: 1px solid var(--border);
}
.sb-logo-inner {
  display: flex; align-items: center; gap: 9px;
}
.sb-logo-icon {
  width: 32px; height: 32px;
  background: var(--accent-g);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px; color: #fff; flex-shrink: 0;
}
.sb-logo-text { line-height: 1.2; }
.sb-logo-name { font-size: 13px; font-weight: 600; color: var(--text-hi); }
.sb-logo-sub  { font-size: 10px; color: var(--green); font-family: var(--mono); display: flex; align-items: center; gap: 4px; }
.sb-logo-sub::before {
  content: '';
  width: 5px; height: 5px; border-radius: 50%;
  background: var(--green);
  animation: pulse 2s ease infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

.sb-nav { flex: 1; padding: 14px 10px; overflow-y: auto; }
.sb-group { margin-bottom: 6px; }
.sb-group-label {
  font-size: 9.5px; font-weight: 600;
  letter-spacing: 0.1em; text-transform: uppercase;
  color: var(--text-dim); padding: 8px 10px 4px;
}

.sb-link {
  display: flex; align-items: center; gap: 9px;
  padding: 9px 10px; border-radius: var(--r2);
  color: var(--text); text-decoration: none;
  font-size: 13px; font-weight: 400;
  transition: all 0.15s; position: relative;
}
.sb-link:hover { background: var(--surface2); color: var(--text-hi); }
.sb-link.active {
  background: rgba(91,142,240,0.12);
  color: var(--accent);
  font-weight: 500;
}
[data-theme="light"] .sb-link.active {
  background: rgba(59,116,224,0.1);
}
.sb-link.active::before {
  content: '';
  position: absolute; left: 0; top: 25%; bottom: 25%;
  width: 3px; background: var(--accent);
  border-radius: 0 3px 3px 0;
}
.sb-link i { font-size: 15px; opacity: 0.8; }
.sb-link.active i { opacity: 1; }

.sb-footer {
  padding: 12px 10px;
  border-top: 1px solid var(--border);
}
.sb-user {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 10px; border-radius: var(--r2);
  margin-bottom: 4px;
}
.sb-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--accent-g);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 600; color: #fff; flex-shrink: 0;
}
.sb-username { font-size: 12px; color: var(--text-hi); font-weight: 500; }
.sb-role { font-size: 10px; color: var(--text-dim); }

/* ── MAIN ── */
.main {
  margin-left: var(--sidebar);
  flex: 1;
  min-height: 100vh;
  display: flex; flex-direction: column;
}

.topbar {
  height: 52px;
  background: var(--bg2);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center;
  padding: 0 24px;
  gap: 12px;
  position: sticky; top: 0; z-index: 50;
  transition: background 0.2s, border-color 0.2s;
  /* Remove backdrop-filter — it's expensive on scroll */
}
.topbar-title { font-size: 14px; font-weight: 600; color: var(--text-hi); }
.topbar-breadcrumb { font-size: 11px; color: var(--text-dim); font-family: var(--mono); margin-left: 2px; }
.topbar-right { margin-left: auto; display: flex; align-items: center; gap: 8px; }

.hamburger {
  display: none; background: none; border: none;
  color: var(--text); cursor: pointer; font-size: 18px;
  padding: 4px;
}

.content { flex: 1; padding: 28px 28px; }
@media (max-width: 600px) { .content { padding: 18px 16px; } }

/* ── COMPONENTS ── */

/* CARD */
.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
  transition: background 0.2s, border-color 0.2s;
}
.card-body { padding: 20px; }
.card-header {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
}
.card-title { font-size: 13px; font-weight: 600; color: var(--text-hi); }

/* STAT CARD */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 14px; margin-bottom: 24px; }
.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 18px 20px;
  position: relative; overflow: hidden;
  transition: background 0.2s, border-color 0.2s;
}
.stat-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 2px;
}
.stat-card.blue::before { background: linear-gradient(90deg, var(--accent), transparent); }
.stat-card.green::before { background: linear-gradient(90deg, var(--green), transparent); }
.stat-card.amber::before { background: linear-gradient(90deg, var(--amber), transparent); }
.stat-card.purple::before { background: linear-gradient(90deg, #a78bfa, transparent); }
.stat-icon { font-size: 18px; margin-bottom: 12px; }
.stat-card.blue .stat-icon { color: var(--accent); }
.stat-card.green .stat-icon { color: var(--green); }
.stat-card.amber .stat-icon { color: var(--amber); }
.stat-card.purple .stat-icon { color: #a78bfa; }
.stat-val { font-size: 26px; font-weight: 600; color: var(--text-hi); font-family: var(--mono); line-height: 1; margin-bottom: 4px; }
.stat-label { font-size: 11px; color: var(--text-dim); }

/* BUTTON */
.btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 14px; border-radius: var(--r2);
  font-size: 12px; font-weight: 500;
  border: none; cursor: pointer;
  text-decoration: none; transition: all 0.15s;
  font-family: var(--font);
  line-height: 1;
}
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: var(--accent2); }
.btn-ghost {
  background: transparent;
  border: 1px solid var(--border2);
  color: var(--text);
}
.btn-ghost:hover { background: var(--surface2); color: var(--text-hi); border-color: var(--border2); }
.btn-danger { background: rgba(248,113,113,0.12); color: var(--red); border: 1px solid rgba(248,113,113,0.2); }
.btn-danger:hover { background: rgba(248,113,113,0.22); }
.btn-warn { background: rgba(251,191,36,0.1); color: var(--amber); border: 1px solid rgba(251,191,36,0.2); }
.btn-warn:hover { background: rgba(251,191,36,0.2); }
.btn-sm { padding: 5px 10px; font-size: 11px; }
.btn-icon { padding: 7px; }
.btn i { font-size: 14px; }
.btn-sm i { font-size: 12px; }

/* TABLE */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th {
  padding: 11px 14px; text-align: left;
  font-size: 11px; font-weight: 600; letter-spacing: 0.05em;
  text-transform: uppercase; color: var(--text-dim);
  border-bottom: 1px solid var(--border);
  background: var(--bg2);
  white-space: nowrap;
}
tbody td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: 13px; vertical-align: middle; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: rgba(128,128,128,0.04); }
.td-action { display: flex; gap: 6px; }

/* BADGE */
.badge {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 3px 9px; border-radius: 99px;
  font-size: 10px; font-weight: 600; font-family: var(--mono);
}
.badge-green { background: rgba(52,211,153,0.1); color: var(--green); border: 1px solid rgba(52,211,153,0.2); }
.badge-dim   { background: var(--surface2); color: var(--text-dim); border: 1px solid var(--border); }
.badge-blue  { background: rgba(91,142,240,0.1); color: var(--accent); border: 1px solid rgba(91,142,240,0.2); }

/* ALERT */
.alert {
  padding: 12px 16px; border-radius: var(--r2);
  font-size: 12px; margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}
.alert-success { background: rgba(52,211,153,0.08); color: var(--green); border: 1px solid rgba(52,211,153,0.18); }
.alert-danger   { background: rgba(248,113,113,0.08); color: var(--red);   border: 1px solid rgba(248,113,113,0.18); }

/* FORM */
.form-group { margin-bottom: 16px; }
.form-label { display: block; font-size: 12px; color: var(--text); margin-bottom: 6px; font-weight: 500; }
.form-sub { font-size: 11px; color: var(--text-dim); margin-top: 4px; }
.form-control {
  width: 100%; padding: 9px 12px;
  background: var(--bg2); border: 1px solid var(--border2);
  border-radius: var(--r2); color: var(--text-hi);
  font-size: 13px; font-family: var(--font);
  transition: border-color 0.15s, box-shadow 0.15s;
  outline: none;
}
.form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91,142,240,0.12); }
.form-control::placeholder { color: var(--text-dim); }
textarea.form-control { resize: vertical; min-height: 90px; }
.form-check { display: flex; align-items: center; gap: 8px; }
.form-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--accent); cursor: pointer; }
.form-check label { font-size: 12px; color: var(--text); cursor: pointer; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }

/* MODAL */
.modal-backdrop {
  display: none; position: fixed; inset: 0; z-index: 500;
  background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);
  align-items: center; justify-content: center;
  animation: fdIn 0.18s ease;
}
.modal-backdrop.open { display: flex; }
@keyframes fdIn { from{opacity:0} to{opacity:1} }

.modal {
  background: var(--surface);
  border: 1px solid var(--border2);
  border-radius: 14px;
  width: 100%; max-width: 540px;
  max-height: 90vh; overflow-y: auto;
  box-shadow: var(--shadow);
  animation: slideUp 0.22s ease;
}
.modal-lg { max-width: 680px; }
@keyframes slideUp { from{transform:translateY(16px);opacity:0} to{transform:none;opacity:1} }
.modal-header {
  padding: 18px 22px 16px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}
.modal-title { font-size: 14px; font-weight: 600; color: var(--text-hi); }
.modal-body { padding: 22px; }
.modal-footer {
  padding: 14px 22px;
  border-top: 1px solid var(--border);
  display: flex; justify-content: flex-end; gap: 8px;
}
.modal-close { background: none; border: none; color: var(--text-dim); cursor: pointer; font-size: 20px; line-height: 1; padding: 4px; border-radius: 5px; }
.modal-close:hover { background: var(--surface2); color: var(--text); }

/* PAGE TITLE */
.page-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 22px; flex-wrap: wrap; }
.page-head-left h2 { font-size: 18px; font-weight: 600; color: var(--text-hi); }
.page-head-left p { font-size: 12px; color: var(--text-dim); margin-top: 2px; }

/* RESPONSIVE */
@media (max-width: 768px) {
  :root { --sidebar: 0px; }
  .sidebar {
    transform: translateX(-220px);
    width: 220px;
  }
  .sidebar.mobile-open { transform: translateX(0); }
  .main { margin-left: 0; }
  .hamburger { display: block; }
  .topbar-title { font-size: 13px; }
}

.overlay {
  display: none; position: fixed; inset: 0; z-index: 90;
  background: rgba(0,0,0,0.5);
}
.overlay.show { display: block; }

/* EMPTY */
.empty-box { text-align: center; padding: 50px 20px; color: var(--text-dim); }
.empty-box i { font-size: 36px; margin-bottom: 12px; opacity: 0.3; display: block; }
.empty-box p { font-size: 13px; }

/* ANIM */
@keyframes fu { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.fade-in { animation: fu 0.35s ease both; }

/* ── LIGHT MODE OVERRIDES ── */
[data-theme="light"] .sb-link.active { background: rgba(59,116,224,0.1); }
[data-theme="light"] .badge-green { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.25); }
[data-theme="light"] .badge-dim { background: var(--surface2); color: var(--text-dim); }
[data-theme="light"] .badge-blue { background: rgba(59,116,224,0.08); color: var(--accent); border-color: rgba(59,116,224,0.2); }
[data-theme="light"] .btn-warn { background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.25); }
[data-theme="light"] .btn-danger { background: rgba(239,68,68,0.08); border-color: rgba(239,68,68,0.2); }
[data-theme="light"] .alert-success { background: rgba(16,185,129,0.07); border-color: rgba(16,185,129,0.2); }
[data-theme="light"] .alert-danger { background: rgba(239,68,68,0.07); border-color: rgba(239,68,68,0.2); }
[data-theme="light"] tbody tr:hover td { background: rgba(0,0,0,0.02); }
[data-theme="light"] .modal-backdrop { background: rgba(0,0,0,0.45); }
</style>

<!-- Apply saved theme BEFORE render — avoids flash -->
<script>
(function(){
  try {
    var t = localStorage.getItem('admin_theme') || 'dark';
    document.documentElement.setAttribute('data-theme', t);
  } catch(e){}
})();
</script>
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-inner">
      <div class="sb-logo-icon"><i class="lucide lucide-terminal" style="font-size:14px"></i></div>
      <div class="sb-logo-text">
        <div class="sb-logo-name">Admin Panel</div>
        <div class="sb-logo-sub">online</div>
      </div>
    </div>
  </div>

  <nav class="sb-nav">
    <div class="sb-group">
      <div class="sb-group-label">Menu</div>
      <?php foreach ($menu as $m): ?>
        <a href="<?= $m['href'] ?>" class="sb-link <?= ($active_menu ?? '') === $m['key'] ? 'active' : '' ?>">
          <i class="lucide lucide-<?= $m['icon'] ?>"></i>
          <?= $m['label'] ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="sb-group">
      <div class="sb-group-label">Selawas Visual</div>
      <?php foreach ($menu_visual as $m): ?>
        <a href="<?= $m['href'] ?>" class="sb-link <?= ($active_menu ?? '') === $m['key'] ? 'active' : '' ?>">
          <i class="lucide lucide-<?= $m['icon'] ?>"></i>
          <?= $m['label'] ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="sb-username"><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></div>
        <div class="sb-role">Administrator</div>
      </div>
    </div>
    <a href="logout.php" class="sb-link" style="color: var(--red);">
      <i class="lucide lucide-log-out"></i> Logout
    </a>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <button class="hamburger" onclick="toggleSidebar()"><i class="lucide lucide-menu"></i></button>
    <span class="topbar-title"><?= $page_title ?? 'Dashboard' ?></span>
    <span class="topbar-breadcrumb">/ <?= $active_menu ?? 'home' ?></span>
    <div class="topbar-right">
      <!-- ── DARK / LIGHT TOGGLE ── -->
      <button class="theme-toggle" id="themeBtn" onclick="toggleAdminTheme()" aria-label="Toggle tema">
        <i class="lucide lucide-sun" id="themeIcon"></i>
        <span id="themeLabel">Light</span>
      </button>
      <a href="../index.php" target="_blank" class="btn btn-ghost btn-sm" style="gap:5px">
        <i class="lucide lucide-external-link"></i> Lihat Website
      </a>
    </div>
  </div>
  <div class="content fade-in">
<?php
// content starts here — each page closes </div></div></body></html>
// Script below is echoed so it appears in the <body> before .content
echo <<<'SCRIPT'
<script>
/* ── SIDEBAR ── */
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('mobile-open');
  document.getElementById('overlay').classList.toggle('show');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('mobile-open');
  document.getElementById('overlay').classList.remove('show');
}

/* ── THEME ── */
function _applyTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  var icon  = document.getElementById('themeIcon');
  var label = document.getElementById('themeLabel');
  if (icon)  icon.className  = t === 'dark' ? 'lucide lucide-sun' : 'lucide lucide-moon';
  if (label) label.textContent = t === 'dark' ? 'Light' : 'Dark';
  try { localStorage.setItem('admin_theme', t); } catch(e){}
}
function toggleAdminTheme() {
  var cur = document.documentElement.getAttribute('data-theme') || 'dark';
  _applyTheme(cur === 'dark' ? 'light' : 'dark');
}
(function(){
  try {
    var t = localStorage.getItem('admin_theme') || 'dark';
    _applyTheme(t);
  } catch(e){}
})();
</script>
SCRIPT;
?>