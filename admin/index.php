<?php
require 'koneksi.php';
require '_auth.php';
$page_title  = 'Dashboard';
$active_menu = 'dashboard';
require '_layout.php';

// --- STAT COUNTS ---
$jml_experience = $pdo->query("SELECT COUNT(*) FROM experiences")->fetchColumn();
$jml_projects   = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$jml_foto       = $pdo->query("SELECT COUNT(*) FROM slws_photos")->fetchColumn();
$jml_video      = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$jml_skills     = 0; $jml_clients = 0;
try { $jml_skills  = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn(); } catch(Exception $e){}
try { $jml_clients = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn(); } catch(Exception $e){}

// --- PROFIL untuk completeness check ---
$profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch();

// --- COMPLETENESS CHECKS ---
$checks = [
    ['label' => 'Foto profil diisi',    'ok' => !empty($profil['profile_picture'])],
    ['label' => 'Email diisi',          'ok' => !empty($profil['email'])],
    ['label' => 'Nomor WhatsApp diisi', 'ok' => !empty($profil['whatsapp'] ?? '')],
    ['label' => 'GitHub link diisi',    'ok' => !empty($profil['github_link'])],
    ['label' => 'LinkedIn link diisi',  'ok' => !empty($profil['linkedin_link'])],
    ['label' => 'Minimal 1 Experience', 'ok' => $jml_experience > 0],
    ['label' => 'Minimal 1 Project',    'ok' => $jml_projects > 0],
    ['label' => 'Minimal 3 Skills',     'ok' => $jml_skills >= 3],
    ['label' => 'Minimal 1 Client',     'ok' => $jml_clients > 0],
    ['label' => 'Minimal 1 Foto Galeri','ok' => $jml_foto > 0],
];
$done  = count(array_filter($checks, fn($c) => $c['ok']));
$total = count($checks);
$pct   = round($done / $total * 100);

// --- ACTIVITY LOG ---
$logs = [];
try { $logs = $pdo->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 8")->fetchAll(); } catch(Exception $e){}

// --- PROJECT URLS untuk uptime check ---
$project_urls = [];
try {
    $rows = $pdo->query("SELECT title, link_url FROM projects WHERE link_url IS NOT NULL AND link_url != '' AND link_url NOT LIKE '/%' ORDER BY id")->fetchAll();
    foreach ($rows as $r) $project_urls[] = ['title' => $r['title'], 'url' => $r['link_url']];
} catch(Exception $e){}

// --- HEALTH CHECKS config ---
$health_checks = [
  ['id'=>'hc-sitemap',   'label'=>'sitemap.xml / sitemap.php', 'url'=>'https://rsby.my.id/sitemap.xml'],
  ['id'=>'hc-robots',    'label'=>'robots.txt',                'url'=>'https://rsby.my.id/robots.txt'],
  ['id'=>'hc-manifest',  'label'=>'manifest.json (PWA)',       'url'=>'https://rsby.my.id/manifest.json'],
  ['id'=>'hc-home',      'label'=>'Homepage online',           'url'=>'https://rsby.my.id/'],
];
?>

<style>
.stats-grid-new{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:16px}
@media(max-width:900px){.stats-grid-new{grid-template-columns:repeat(3,1fr)}}
@media(max-width:520px){.stats-grid-new{grid-template-columns:repeat(2,1fr)}}
.scard{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;display:flex;flex-direction:column;gap:4px;transition:border-color .15s}
.scard:hover{border-color:var(--border2)}
.scard-val{font-size:26px;font-weight:700;color:var(--text-hi);font-family:var(--mono);line-height:1}
.scard-label{font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:.07em}
.scard-icon{font-size:20px;margin-bottom:4px;line-height:1;display:flex;align-items:center}

.dash-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:16px}
@media(max-width:900px){.dash-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.dash-grid{grid-template-columns:1fr}}

/* Completeness */
.prog-wrap{background:var(--surface2);border-radius:99px;height:8px;overflow:hidden;margin:8px 0 4px}
.prog-bar{height:100%;border-radius:99px;background:var(--accent-g);transition:width .7s cubic-bezier(.4,0,.2,1)}
.check-list{display:flex;flex-direction:column;gap:5px;margin-top:10px}
.check-item{display:flex;align-items:center;gap:8px;font-size:12.5px}
.ci{width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;flex-shrink:0}
.ci.ok{background:rgba(52,211,153,.15);color:var(--green);border:1px solid rgba(52,211,153,.3)}
.ci.nok{background:rgba(248,113,113,.1);color:var(--red);border:1px solid rgba(248,113,113,.25)}

/* Health */
.health-list{display:flex;flex-direction:column;gap:7px}
.health-item{display:flex;align-items:center;gap:10px;font-size:12.5px;padding:8px 10px;border-radius:var(--r2);background:var(--surface2)}
.health-badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:99px;margin-left:auto;white-space:nowrap}
.hb-ok{background:rgba(52,211,153,.15);color:var(--green)}
.hb-fail{background:rgba(248,113,113,.12);color:var(--red)}
.hb-loading{background:var(--surface2);color:var(--text-dim);animation:blink2 1.2s infinite}
@keyframes blink2{0%,100%{opacity:1}50%{opacity:.4}}

/* Uptime */
.uptime-list{display:flex;flex-direction:column;gap:7px}
.uptime-item{display:flex;align-items:center;gap:10px;font-size:12.5px;padding:8px 10px;border-radius:var(--r2);background:var(--surface2)}
.udot{width:8px;height:8px;border-radius:50%;flex-shrink:0;background:var(--text-dim)}
.udot.up{background:var(--green);box-shadow:0 0 0 3px rgba(52,211,153,.2)}
.udot.down{background:var(--red);box-shadow:0 0 0 3px rgba(248,113,113,.2)}
.udot.checking{background:var(--amber);animation:pulse 1s infinite}
.uptime-ms{font-size:11px;color:var(--text-dim);margin-left:auto;font-family:var(--mono)}

/* Activity */
.act-list{display:flex;flex-direction:column}
.act-item{display:flex;gap:10px;padding:9px 0;border-bottom:1px solid var(--border);align-items:flex-start}
.act-item:last-child{border-bottom:none}
.act-icon{width:28px;height:28px;border-radius:8px;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;color:var(--accent)}
.act-text{font-size:12.5px;color:var(--text);line-height:1.4}
.act-time{font-size:11px;color:var(--text-dim);margin-top:2px;font-family:var(--mono)}

/* Preview */
.preview-wrap{position:relative;width:100%;padding-top:55%;overflow:hidden;border-radius:var(--r2);border:1px solid var(--border2);background:#000}
.preview-wrap iframe{position:absolute;top:0;left:0;width:200%;height:200%;transform:scale(.5);transform-origin:top left;border:0;pointer-events:none}

/* Quick action buttons */
.qa-list{display:flex;flex-direction:column;gap:7px}
</style>

<div class="page-head">
  <div class="page-head-left">
    <h2>Dashboard</h2>
    <p>Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> 👋</p>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <a href="../index.php" target="_blank" class="btn btn-ghost btn-sm"><?= icon('external-link', 13) ?> Lihat Website</a>
    <a href="https://search.google.com/search-console" target="_blank" class="btn btn-ghost btn-sm"><?= icon('bar-chart', 13) ?> Search Console</a>
  </div>
</div>

<!-- STAT CARDS -->
<div class="stats-grid-new">
  <div class="scard"><div class="scard-icon" style="color:var(--accent)"><?= icon('experience', 20) ?></div><div class="scard-val"><?= $jml_experience ?></div><div class="scard-label">Experience</div></div>
  <div class="scard"><div class="scard-icon" style="color:var(--accent)"><?= icon('projects', 20) ?></div><div class="scard-val"><?= $jml_projects ?></div><div class="scard-label">Projects</div></div>
  <div class="scard"><div class="scard-icon" style="color:var(--amber)"><?= icon('skills', 20) ?></div><div class="scard-val"><?= $jml_skills ?></div><div class="scard-label">Skills</div></div>
  <div class="scard"><div class="scard-icon" style="color:var(--green)"><?= icon('clients', 20) ?></div><div class="scard-val"><?= $jml_clients ?></div><div class="scard-label">Clients</div></div>
  <div class="scard"><div class="scard-icon" style="color:var(--accent)"><?= icon('galeri', 20) ?></div><div class="scard-val"><?= $jml_foto ?></div><div class="scard-label">Foto Galeri</div></div>
  <div class="scard"><div class="scard-icon" style="color:var(--red)"><?= icon('video', 20) ?></div><div class="scard-val"><?= $jml_video ?></div><div class="scard-label">Video</div></div>
</div>

<!-- ROW 1 -->
<div class="dash-grid">

  <!-- COMPLETENESS -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Portfolio Completeness</span>
      <span class="badge <?= $pct>=80?'badge-green':($pct>=50?'badge-amber':'badge-red') ?>"><?= $pct ?>%</span>
    </div>
    <div class="card-body">
      <div style="font-size:12px;color:var(--text-dim)"><?= $done ?> dari <?= $total ?> item selesai</div>
      <div class="prog-wrap"><div class="prog-bar" style="width:<?= $pct ?>%"></div></div>
      <div class="check-list">
        <?php foreach ($checks as $c): ?>
        <div class="check-item">
          <div class="ci <?= $c['ok']?'ok':'nok' ?>"><?= $c['ok']?'✓':'✕' ?></div>
          <span style="color:<?= $c['ok']?'var(--text)':'var(--text-dim)' ?>"><?= $c['label'] ?></span>
          <?php if(!$c['ok']): ?><span style="font-size:11px;color:var(--red);margin-left:auto">Belum</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- WEBSITE HEALTH -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Website Health</span>
      <button class="btn btn-ghost btn-sm" onclick="runHealthCheck()"><?= icon('refresh', 13) ?> Cek Ulang</button>
    </div>
    <div class="card-body">
      <div class="health-list">
        <?php foreach ($health_checks as $hc): ?>
        <div class="health-item" id="<?= $hc['id'] ?>">
          <?= icon('globe', 13) ?>
          <span><?= $hc['label'] ?></span>
          <span class="health-badge hb-loading">Mengecek...</span>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:10px;font-size:11px;color:var(--text-dim)">* Cek dilakukan langsung dari browser ke situs live</div>
    </div>
  </div>

  <!-- PROJECT UPTIME -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Project Uptime</span>
      <button class="btn btn-ghost btn-sm" onclick="runUptimeCheck()"><?= icon('refresh', 13) ?> Cek Ulang</button>
    </div>
    <div class="card-body">
      <?php if (empty($project_urls)): ?>
        <p style="color:var(--text-dim);font-size:13px">Belum ada project dengan URL publik.</p>
      <?php else: ?>
        <div class="uptime-list">
          <?php foreach ($project_urls as $i => $p): ?>
          <div class="uptime-item">
            <div class="udot checking" id="udot-<?= $i ?>"></div>
            <div>
              <div style="font-size:13px;font-weight:500;color:var(--text-hi)"><?= htmlspecialchars($p['title']) ?></div>
              <div style="font-size:11px;color:var(--text-dim)"><?= htmlspecialchars($p['url']) ?></div>
            </div>
            <span class="uptime-ms" id="upms-<?= $i ?>">—</span>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ROW 2 -->
<div class="dash-grid">

  <!-- QUICK ACTIONS -->
  <div class="card">
    <div class="card-header"><span class="card-title">Quick Actions</span></div>
    <div class="card-body">
      <div class="qa-list">
        <a href="profil.php"     class="btn btn-ghost" style="justify-content:flex-start"><?= icon('profil') ?> Edit Profil & WhatsApp</a>
        <a href="experience.php" class="btn btn-ghost" style="justify-content:flex-start"><?= icon('experience') ?> Tambah Experience</a>
        <a href="projects.php"   class="btn btn-ghost" style="justify-content:flex-start"><?= icon('projects') ?> Tambah Project</a>
        <a href="skills.php"     class="btn btn-ghost" style="justify-content:flex-start"><?= icon('skills') ?> Kelola Skills</a>
        <a href="clients.php"    class="btn btn-ghost" style="justify-content:flex-start"><?= icon('clients') ?> Kelola Clients</a>
        <a href="galeri.php"     class="btn btn-ghost" style="justify-content:flex-start"><?= icon('galeri') ?> Upload Foto Galeri</a>
        <a href="video.php"      class="btn btn-ghost" style="justify-content:flex-start"><?= icon('video') ?> Tambah Video</a>
      </div>
    </div>
  </div>

  <!-- PREVIEW WEBSITE -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Preview Website</span>
      <a href="https://rsby.my.id" target="_blank" class="btn btn-ghost btn-sm"><?= icon('external-link', 13) ?> Buka Full</a>
    </div>
    <div class="card-body" style="padding:0">
      <div class="preview-wrap">
        <iframe src="https://rsby.my.id" loading="lazy" title="Preview rsby.my.id" sandbox="allow-scripts allow-same-origin"></iframe>
      </div>
      <div style="padding:8px 14px;font-size:11px;color:var(--text-dim)">Preview hanya-baca · Klik "Buka Full" untuk versi interaktif</div>
    </div>
  </div>

  <!-- ACTIVITY LOG -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">Activity Log</span>
    </div>
    <div class="card-body" style="padding:0 16px">
      <?php if (empty($logs)): ?>
        <div class="act-list">
          <div class="act-item">
            <div class="act-icon"><?= icon('info', 13) ?></div>
            <div>
              <div class="act-text">Belum ada aktivitas tercatat.</div>
              <div class="act-time">Aktifkan dengan SQL di bawah ini</div>
            </div>
          </div>
        </div>
        <details style="margin-top:10px">
          <summary style="font-size:11px;color:var(--text-dim);cursor:pointer;user-select:none">Lihat SQL untuk aktifkan log ▸</summary>
          <code style="display:block;background:var(--surface2);padding:8px;border-radius:6px;font-size:10.5px;margin-top:6px;line-height:1.6;white-space:pre-wrap">CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(255),
  icon VARCHAR(50) DEFAULT 'lucide-edit',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</code>
        </details>
      <?php else: ?>
        <div class="act-list">
          <?php foreach ($logs as $log):
            $act = strtolower($log['action'] ?? '');
            $ic = 'edit';
            if (str_contains($act,'hapus')) $ic = 'projects';
            elseif (str_contains($act,'tambah')) $ic = 'skills';
            elseif (str_contains($act,'login')) $ic = 'profil';
            $teks = trim(($log['action'] ?? '') . ' ' . ($log['entity'] ?? ''));
            if (!empty($log['note'])) $teks .= ': ' . $log['note'];
          ?>
          <div class="act-item">
            <div class="act-icon"><?= icon($ic, 13) ?></div>
            <div>
              <div class="act-text"><?= htmlspecialchars($teks) ?></div>
              <div class="act-time"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

</div></div>

<script>
function toggleSidebar(){document.getElementById('sidebar').classList.toggle('mobile-open');document.getElementById('overlay').classList.toggle('show')}
function closeSidebar(){document.getElementById('sidebar').classList.remove('mobile-open');document.getElementById('overlay').classList.remove('show')}

// ── HEALTH CHECK ──
const healthChecks = <?= json_encode($health_checks) ?>;
async function runHealthCheck() {
  for (const hc of healthChecks) {
    const el = document.getElementById(hc.id);
    if (!el) continue;
    const badge = el.querySelector('.health-badge');
    badge.className = 'health-badge hb-loading';
    badge.textContent = 'Mengecek...';
    try {
      const t0 = Date.now();
      const res = await fetch(hc.url, { method:'HEAD', cache:'no-store', signal: AbortSignal.timeout(7000) });
      const ms = Date.now() - t0;
      badge.className = 'health-badge ' + (res.ok ? 'hb-ok' : 'hb-fail');
      badge.textContent = res.ok ? `OK (${ms}ms)` : `Error ${res.status}`;
    } catch(e) {
      badge.className = 'health-badge hb-fail';
      badge.textContent = 'Tidak terjangkau';
    }
  }
}

// ── UPTIME CHECK ──
const projectUrls = <?= json_encode($project_urls) ?>;
async function runUptimeCheck() {
  projectUrls.forEach(async (p, i) => {
    const dot  = document.getElementById('udot-' + i);
    const msEl = document.getElementById('upms-' + i);
    if (!dot) return;
    dot.className = 'udot checking';
    if (msEl) msEl.textContent = 'Mengecek...';
    try {
      const t0 = Date.now();
      await fetch(p.url, { method:'HEAD', cache:'no-store', mode:'no-cors', signal: AbortSignal.timeout(8000) });
      const ms = Date.now() - t0;
      dot.className = 'udot up';
      if (msEl) msEl.textContent = ms + 'ms';
    } catch(e) {
      dot.className = 'udot down';
      if (msEl) msEl.textContent = 'Offline';
    }
  });
}

window.addEventListener('DOMContentLoaded', () => {
  runHealthCheck();
  runUptimeCheck();
});
</script>
</body></html>