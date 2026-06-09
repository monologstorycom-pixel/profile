<?php
require 'admin/koneksi.php';

/* ─────────────── DATA ─────────────── */
$profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch() ?: [];

$nama       = $profil['full_name']           ?? 'Rizqi Subagyo';
$tagline    = $profil['tagline']             ?? 'IT Support Specialist | Full-stack Developer';
$status     = $profil['availability_status'] ?? 'Tersedia untuk proyek baru';
$email      = $profil['email']               ?? 'rizqisubagyo07@gmail.com';
$github     = $profil['github_link']         ?? 'https://github.com/monologstorycom-pixel';
$linkedin   = $profil['linkedin_link']       ?? 'https://www.linkedin.com/in/rizqi-subagyo-7ab331380';
$whatsapp   = preg_replace('/[^0-9]/', '', $profil['whatsapp'] ?? '');
$foto_raw   = !empty($profil['profile_picture']) ? $profil['profile_picture'] : 'https://avatars.githubusercontent.com/u/252295342?v=4';
$favicon    = !empty($profil['favicon_url'] ?? '') ? $profil['favicon_url'] : 'favicon.svg';

// Untuk OG/JSON-LD: pastikan URL absolut
function abs_url(string $url, string $base = 'https://rsby.my.id/'): string {
    if (preg_match('#^https?://#i', $url)) return $url;
    return rtrim($base, '/') . '/' . ltrim($url, '/');
}
$foto_abs    = abs_url($foto_raw);
$favicon_abs = abs_url($favicon);

// Helper escape
function esc($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/* ─────────────── EXPERIENCE ─────────────── */
$experiences = [];
try {
    $experiences = $pdo->query("SELECT * FROM experiences ORDER BY is_active DESC, id DESC")->fetchAll();
} catch (Exception $e) {}

/* ─────────────── PROJECTS ─────────────── */
$projects = [];
try {
    $projects = $pdo->query("SELECT *, TIMESTAMPDIFF(DAY, created_at, NOW()) AS days_ago FROM projects ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    try { $projects = $pdo->query("SELECT *, NULL AS days_ago FROM projects ORDER BY id DESC")->fetchAll(); }
    catch (Exception $e2) {}
}

/* ─────────────── VIDEOS ─────────────── */
$videos = [];
try { $videos = $pdo->query("SELECT * FROM videos ORDER BY id DESC LIMIT 6")->fetchAll(); } catch (Exception $e) {}

/* ─────────────── SKILLS ─────────────── */
$skills_raw = [];
try {
    foreach ($pdo->query("SELECT * FROM skills ORDER BY group_name, sort_order, skill_name")->fetchAll() as $s) {
        $skills_raw[$s['group_name']][] = $s['skill_name'];
    }
} catch (Exception $e) {}
if (empty($skills_raw)) {
    $skills_raw = [
        'Programming'    => ['Python', 'Next.js', 'PHP'],
        'Networking'     => ['LAN/WAN', 'TCP/IP', 'Firewall', 'CCTV', 'UniFi', 'Ruijie'],
        'Infrastructure' => ['MikroTik', 'Proxmox', 'Docker', 'Linux'],
    ];
}

/* ─────────────── CLIENTS ─────────────── */
$clients = [];
try { $clients = $pdo->query("SELECT * FROM clients ORDER BY sort_order, id")->fetchAll(); } catch (Exception $e) {}

/* ─────────────── HELPERS ─────────────── */
function ytEmbed($url) {
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $m)) {
        return "https://www.youtube.com/embed/" . $m[1] . "?rel=0";
    }
    return '';
}
function ytThumb($url) {
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $m)) {
        return "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($nama) ?> — IT Support &amp; Full-stack Developer</title>
<meta name="description" content="<?= esc($nama . ' — ' . $tagline) ?>. Tersedia untuk proyek IT Support, Networking, dan Web Development.">
<meta name="robots" content="index, follow">
<meta name="author" content="<?= esc($nama) ?>">
<link rel="canonical" href="https://rsby.my.id/">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://rsby.my.id/">
<meta property="og:title" content="<?= esc($nama) ?> — IT Support &amp; Full-stack Developer">
<meta property="og:description" content="<?= esc($tagline) ?>">
<meta property="og:image" content="<?= esc($foto_abs) ?>">
<meta property="og:locale" content="id_ID">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($nama) ?> — IT Support &amp; Full-stack Developer">
<meta name="twitter:description" content="<?= esc($tagline) ?>">
<meta name="twitter:image" content="<?= esc($foto_abs) ?>">

<!-- JSON-LD -->
<script type="application/ld+json"><?= json_encode([
    '@context'   => 'https://schema.org',
    '@type'      => 'Person',
    'name'       => $nama,
    'jobTitle'   => 'IT Support Specialist & Full-stack Developer',
    'email'      => $email,
    'url'        => 'https://rsby.my.id/',
    'image'      => $foto_abs,
    'sameAs'     => array_values(array_filter([$github, $linkedin])),
    'knowsAbout' => ['IT Support','Networking','MikroTik','PHP','Python','Next.js','Docker','Linux'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<link rel="icon" href="<?= esc($favicon) ?>" type="<?= str_ends_with($favicon, '.svg') ? 'image/svg+xml' : 'image/png' ?>">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0a0c0f">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"></noscript>
<link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

<!-- Set theme sebelum render -->
<script>(function(){try{var t=localStorage.getItem('rsby-theme')||'dark';document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>

<style>
/* ───────────── DESIGN TOKENS ─────────────
   Aesthetic: soft warm minimalist, low-contrast text,
   subtle grain, breathing whitespace, no harsh black.
*/
[data-theme="dark"] {
  --bg:         #0d1014;
  --bg-elev:    #12161c;
  --surface:    #161b22;
  --surface-2:  #1c222b;
  --line:       rgba(255,255,255,0.06);
  --line-2:     rgba(255,255,255,0.10);
  --text:       #b4bac6;
  --text-hi:    #e6e9ef;
  --text-soft:  #6c7589;
  --text-muted: #4a5163;
  --accent:     #e9b97a;     /* warm amber */
  --accent-hi:  #f4cc94;
  --accent-bg:  rgba(233,185,122,0.10);
  --accent-2:   #7da9c9;     /* cool blue */
  --green:      #7cc28a;
  --shadow:     0 1px 2px rgba(0,0,0,.3), 0 12px 32px rgba(0,0,0,.35);
  --grain-op:   .025;
}
[data-theme="light"] {
  --bg:         #f7f5f1;
  --bg-elev:    #ffffff;
  --surface:    #ffffff;
  --surface-2:  #f0ede7;
  --line:       rgba(20,20,20,0.07);
  --line-2:     rgba(20,20,20,0.13);
  --text:       #555a64;
  --text-hi:    #1a1d22;
  --text-soft:  #828893;
  --text-muted: #a8aebb;
  --accent:     #b8804a;
  --accent-hi:  #a16f3d;
  --accent-bg:  rgba(184,128,74,0.08);
  --accent-2:   #4a7e9e;
  --green:      #4a9b5d;
  --shadow:     0 1px 2px rgba(0,0,0,.04), 0 8px 24px rgba(0,0,0,.06);
  --grain-op:   .015;
}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{
  font-family:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif;
  background:var(--bg);
  color:var(--text);
  line-height:1.65;
  font-size:15px;
  font-weight:400;
  letter-spacing:-0.005em;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  min-height:100vh;
  overflow-x:hidden;
  transition:background .3s ease,color .3s ease;
  position:relative;
}
/* Subtle grain texture */
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:1;
  background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='180' height='180'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='0.85'/></filter><rect width='100%25' height='100%25' filter='url(%23n)' opacity='1'/></svg>");
  opacity:var(--grain-op);mix-blend-mode:overlay;
}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}
button{font:inherit;color:inherit;background:none;border:none;cursor:pointer}

::selection{background:var(--accent);color:var(--bg)}
::-webkit-scrollbar{width:8px;height:8px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--line-2);border-radius:8px}
::-webkit-scrollbar-thumb:hover{background:var(--text-soft)}

/* ───────────── LAYOUT ───────────── */
.wrap{max-width:920px;margin:0 auto;padding:0 28px;position:relative;z-index:2}
@media(max-width:520px){.wrap{padding:0 20px}}

/* ───────────── NAV ───────────── */
.nav{
  position:sticky;top:0;z-index:50;
  background:color-mix(in srgb,var(--bg) 78%,transparent);
  backdrop-filter:blur(14px) saturate(140%);
  -webkit-backdrop-filter:blur(14px) saturate(140%);
  border-bottom:1px solid transparent;
  transition:border-color .25s ease;
}
.nav.scrolled{border-color:var(--line)}
.nav-inner{
  max-width:920px;margin:0 auto;
  padding:14px 28px;
  display:flex;align-items:center;justify-content:space-between;gap:20px;
}
@media(max-width:520px){.nav-inner{padding:12px 20px}}

.brand{display:flex;align-items:center;gap:9px;font-weight:600;color:var(--text-hi);font-size:14px}
.brand-mark{
  width:28px;height:28px;border-radius:7px;
  background:var(--accent);color:var(--bg);
  display:grid;place-items:center;
  font-family:'JetBrains Mono',monospace;font-weight:700;font-size:12px;
  box-shadow:0 0 0 1px var(--line-2);
}
.brand-text{font-family:'JetBrains Mono',monospace;font-size:13px;letter-spacing:-.02em}

.nav-links{display:flex;align-items:center;gap:4px}
.nav-link{
  color:var(--text-soft);font-size:13px;
  padding:7px 12px;border-radius:7px;
  transition:color .15s ease,background .15s ease;
}
.nav-link:hover{color:var(--text-hi)}
.nav-link.active{color:var(--accent);background:var(--accent-bg)}
@media(max-width:680px){.nav-links{display:none}}

.theme-btn{
  width:34px;height:34px;border-radius:8px;
  display:grid;place-items:center;
  color:var(--text-soft);
  border:1px solid var(--line);background:transparent;
  transition:all .2s ease;
}
.theme-btn:hover{color:var(--accent);border-color:var(--line-2);transform:rotate(15deg)}
.theme-btn svg{width:15px;height:15px}

/* ───────────── HERO ───────────── */
.hero{padding:80px 0 56px}
@media(max-width:520px){.hero{padding:48px 0 32px}}

.hero-meta{
  display:inline-flex;align-items:center;gap:8px;
  font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--text-soft);
  margin-bottom:24px;letter-spacing:.02em;
}
.hero-meta::before{content:'';width:18px;height:1px;background:var(--text-muted)}

.hero-grid{
  display:grid;grid-template-columns:1fr 140px;gap:44px;align-items:center;
}
@media(max-width:680px){
  .hero-grid{grid-template-columns:1fr;gap:28px}
  .hero-photo{order:-1;justify-self:start;width:100px}
}

.hero-name{
  font-size:clamp(2.1rem, 5.5vw, 3.4rem);
  line-height:1.05;font-weight:600;letter-spacing:-.035em;
  color:var(--text-hi);margin-bottom:14px;
}
.hero-name .accent{color:var(--accent);font-style:italic;font-weight:500}
.hero-tagline{
  font-size:clamp(1rem, 2vw, 1.1rem);color:var(--text);
  max-width:520px;margin-bottom:18px;font-weight:400;line-height:1.55;
}

.status-pill{
  display:inline-flex;align-items:center;gap:7px;
  padding:5px 12px 5px 9px;border-radius:99px;
  background:var(--surface);border:1px solid var(--line);
  font-size:12px;color:var(--text);font-weight:500;
  margin-bottom:24px;
}
.status-dot{
  width:6px;height:6px;border-radius:50%;background:var(--green);
  box-shadow:0 0 0 4px color-mix(in srgb,var(--green) 25%,transparent);
  animation:dot-pulse 2.4s ease infinite;
}
@keyframes dot-pulse{
  0%,100%{box-shadow:0 0 0 4px color-mix(in srgb,var(--green) 25%,transparent)}
  50%{box-shadow:0 0 0 6px color-mix(in srgb,var(--green) 8%,transparent)}
}

.hero-actions{display:flex;flex-wrap:wrap;gap:8px}

.btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:9px 16px;border-radius:9px;
  font-size:13px;font-weight:500;
  border:1px solid var(--line-2);background:var(--surface);
  color:var(--text-hi);
  transition:all .18s ease;
  white-space:nowrap;
}
.btn:hover{border-color:var(--text-soft);transform:translateY(-1px)}
.btn-primary{background:var(--accent);color:var(--bg);border-color:var(--accent)}
.btn-primary:hover{background:var(--accent-hi);border-color:var(--accent-hi)}
.btn-icon{font-size:13px;opacity:.85}

.hero-photo{
  position:relative;aspect-ratio:1;
  border-radius:50%;
  border:1px solid var(--line-2);
  overflow:hidden;
  background:var(--surface);
  box-shadow:var(--shadow);
}
.hero-photo::after{
  content:'';position:absolute;inset:0;border-radius:50%;
  box-shadow:inset 0 0 30px rgba(0,0,0,.15);pointer-events:none;
}
.hero-photo img{
  width:100%;height:100%;object-fit:cover;
  transition:transform .5s ease;
}
.hero-photo:hover img{transform:scale(1.04)}

/* ───────────── SECTION ───────────── */
section{padding:56px 0;scroll-margin-top:72px}
@media(max-width:520px){section{padding:40px 0}}
.section-head{
  display:flex;align-items:baseline;gap:12px;margin-bottom:32px;
}
.section-tag{
  font-family:'JetBrains Mono',monospace;font-size:11px;
  color:var(--accent);letter-spacing:.06em;text-transform:uppercase;
  padding:3px 9px;border-radius:5px;background:var(--accent-bg);
  font-weight:500;
}
.section-title{
  font-size:clamp(1.4rem,3vw,1.7rem);
  font-weight:600;color:var(--text-hi);letter-spacing:-.02em;line-height:1.2;
}
.section-sub{
  font-size:13.5px;color:var(--text-soft);margin-top:4px;
}

/* ───────────── ABOUT ───────────── */
.about-card{
  padding:32px;
  border:1px solid var(--line);
  border-radius:18px;
  background:linear-gradient(135deg,var(--surface) 0%,var(--bg-elev) 100%);
  position:relative;overflow:hidden;
}
.about-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent 0%,var(--accent) 50%,transparent 100%);
  opacity:.4;
}
.about-card p{
  font-size:15px;line-height:1.85;color:var(--text);
  max-width:680px;
}
.about-card p + p{margin-top:14px}
.about-card strong{color:var(--text-hi);font-weight:600}
.about-card em{color:var(--accent);font-style:normal;font-weight:500}

/* ───────────── EXPERIENCE TIMELINE ───────────── */
.timeline{position:relative;padding-left:24px}
.timeline::before{
  content:'';position:absolute;left:5px;top:8px;bottom:8px;width:1px;
  background:linear-gradient(180deg,var(--line-2) 0%,transparent 100%);
}
.exp-item{position:relative;padding-bottom:30px}
.exp-item:last-child{padding-bottom:0}
.exp-item::before{
  content:'';position:absolute;left:-23px;top:7px;
  width:11px;height:11px;border-radius:50%;
  background:var(--bg);border:1px solid var(--line-2);
  transition:border-color .2s ease,box-shadow .25s ease;
}
.exp-item.active::before{
  background:var(--accent);border-color:var(--accent);
  box-shadow:0 0 0 4px var(--accent-bg);
}
.exp-row{
  display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;
  margin-bottom:4px;align-items:baseline;
}
.exp-title{font-size:15.5px;font-weight:600;color:var(--text-hi);line-height:1.3}
.exp-year{
  font-family:'JetBrains Mono',monospace;font-size:11.5px;
  color:var(--text-soft);white-space:nowrap;
}
.exp-co{font-size:13.5px;color:var(--accent);font-weight:500;margin-bottom:10px}
.exp-desc{
  font-size:13.5px;color:var(--text);line-height:1.7;
}
.exp-desc ul{list-style:none;display:flex;flex-direction:column;gap:6px}
.exp-desc li{
  position:relative;padding-left:14px;
}
.exp-desc li::before{
  content:'';position:absolute;left:0;top:10px;width:5px;height:1px;background:var(--accent)
}
details.exp-toggle > summary{
  list-style:none;cursor:pointer;
  font-size:12.5px;color:var(--text-soft);
  display:inline-flex;align-items:center;gap:5px;
  padding:4px 0;user-select:none;
  transition:color .15s ease;
}
details.exp-toggle > summary::-webkit-details-marker{display:none}
details.exp-toggle > summary:hover{color:var(--text-hi)}
details.exp-toggle > summary::after{
  content:'›';transition:transform .2s ease;display:inline-block;font-size:14px;
}
details.exp-toggle[open] > summary::after{transform:rotate(90deg)}
details.exp-toggle[open] > summary{margin-bottom:8px}

/* ───────────── PROJECTS GRID ───────────── */
.proj-grid{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px;
}
.proj-card{
  display:flex;flex-direction:column;
  padding:22px 22px 20px;
  border:1px solid var(--line);
  border-radius:14px;
  background:var(--surface);
  transition:all .25s ease;
  position:relative;overflow:hidden;
}
.proj-card::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:var(--accent);transform:scaleX(0);transform-origin:left;
  transition:transform .35s ease;
}
.proj-card:hover{
  border-color:var(--line-2);
  background:var(--surface-2);
  transform:translateY(-2px);
}
.proj-card:hover::before{transform:scaleX(1)}

.proj-icon{
  font-size:18px;color:var(--accent);
  width:38px;height:38px;border-radius:9px;
  background:var(--accent-bg);
  display:grid;place-items:center;margin-bottom:14px;
}
.proj-title-row{display:flex;justify-content:space-between;gap:8px;align-items:flex-start;margin-bottom:6px}
.proj-title{font-size:15px;font-weight:600;color:var(--text-hi);line-height:1.3}
.proj-new{
  font-family:'JetBrains Mono',monospace;font-size:9.5px;font-weight:600;
  letter-spacing:.06em;text-transform:uppercase;
  padding:2px 7px;border-radius:99px;
  background:var(--accent-bg);color:var(--accent);
  white-space:nowrap;flex-shrink:0;
  border:1px solid color-mix(in srgb,var(--accent) 30%,transparent);
}
.proj-desc{font-size:13px;color:var(--text-soft);line-height:1.6;flex:1;margin-bottom:14px}
.proj-link{
  display:inline-flex;align-items:center;gap:5px;
  font-size:12px;color:var(--text);font-weight:500;
  padding-top:10px;border-top:1px solid var(--line);
  transition:color .15s ease;
}
.proj-link:hover{color:var(--accent)}
.proj-link i{font-size:10px;transition:transform .2s ease}
.proj-link:hover i{transform:translate(2px,-2px)}

/* ───────────── SKILLS ───────────── */
.skill-groups{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
.skill-group-label{
  font-family:'JetBrains Mono',monospace;font-size:11px;
  color:var(--text-soft);letter-spacing:.08em;text-transform:uppercase;
  margin-bottom:12px;display:flex;align-items:center;gap:8px;
}
.skill-group-label::before{
  content:'';width:5px;height:5px;border-radius:50%;background:var(--accent);
}
.skill-tags{display:flex;flex-wrap:wrap;gap:6px}
.skill-tag{
  font-family:'JetBrains Mono',monospace;font-size:11.5px;
  padding:5px 11px;border-radius:6px;
  background:var(--surface);border:1px solid var(--line);
  color:var(--text);transition:all .18s ease;
}
.skill-tag:hover{
  border-color:var(--accent);color:var(--accent);
  background:var(--accent-bg);transform:translateY(-1px);
}

/* ───────────── CLIENTS ───────────── */
.clients-grid{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;
}
.client-card{
  display:flex;align-items:flex-start;gap:11px;
  padding:14px 16px;
  border:1px solid var(--line);border-radius:11px;
  background:var(--surface);
  transition:all .2s ease;
}
.client-card:hover{border-color:var(--line-2);background:var(--surface-2)}
a.client-card:hover{border-color:var(--accent)}
.client-icon{
  width:32px;height:32px;border-radius:8px;
  background:var(--bg-elev);
  display:grid;place-items:center;
  color:var(--text-soft);font-size:13px;
  flex-shrink:0;
  transition:color .2s ease;
}
.client-card:hover .client-icon{color:var(--accent)}
.client-name{font-size:13px;font-weight:500;color:var(--text-hi);line-height:1.3}
.client-loc{font-size:11.5px;color:var(--text-soft);margin-top:2px}

/* ───────────── VIDEOS ───────────── */
.videos-grid{
  display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;
}
.video-card{
  border:1px solid var(--line);border-radius:14px;overflow:hidden;
  background:var(--surface);transition:all .25s ease;
}
.video-card:hover{border-color:var(--line-2);transform:translateY(-2px);box-shadow:var(--shadow)}
.video-thumb{
  position:relative;aspect-ratio:16/9;background:#000;
  cursor:pointer;overflow:hidden;
}
.video-thumb img{
  width:100%;height:100%;object-fit:cover;
  opacity:.85;transition:opacity .25s ease,transform .35s ease;
}
.video-thumb:hover img{opacity:1;transform:scale(1.04)}
.video-play{
  position:absolute;inset:0;display:grid;place-items:center;pointer-events:none;
}
.video-play-btn{
  width:54px;height:54px;border-radius:50%;
  background:rgba(255,255,255,.18);
  backdrop-filter:blur(6px);
  border:1.5px solid rgba(255,255,255,.4);
  display:grid;place-items:center;color:#fff;font-size:18px;
  transition:all .25s ease;
}
.video-thumb:hover .video-play-btn{
  background:var(--accent);border-color:var(--accent);transform:scale(1.08);
}
.video-iframe-wrap{display:none;aspect-ratio:16/9;background:#000}
.video-iframe-wrap.active{display:block}
.video-iframe-wrap iframe{width:100%;height:100%;border:0;display:block}
.video-thumb.hidden{display:none}
.video-body{padding:14px 16px}
.video-title{font-size:13.5px;font-weight:600;color:var(--text-hi);line-height:1.4;margin-bottom:3px}
.video-desc{font-size:12px;color:var(--text-soft);line-height:1.55}

/* ───────────── CTA ───────────── */
.cta{
  margin-top:24px;
  padding:36px 32px;
  border-radius:18px;
  background:linear-gradient(135deg,var(--surface) 0%,var(--bg-elev) 100%);
  border:1px solid var(--line);
  text-align:center;
  position:relative;overflow:hidden;
}
.cta::before{
  content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);
  width:200px;height:1px;
  background:linear-gradient(90deg,transparent 0%,var(--accent) 50%,transparent 100%);
}
.cta-title{font-size:1.5rem;font-weight:600;color:var(--text-hi);margin-bottom:8px;letter-spacing:-.02em}
.cta-sub{font-size:14px;color:var(--text-soft);margin-bottom:20px;max-width:440px;margin-left:auto;margin-right:auto}
.cta-actions{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}

/* ───────────── FOOTER ───────────── */
footer{
  margin-top:64px;padding:28px 0 32px;
  border-top:1px solid var(--line);
  text-align:center;font-size:12px;color:var(--text-soft);
  font-family:'JetBrains Mono',monospace;
}
footer a{color:var(--accent)}

/* ───────────── FAB & BACK TO TOP ───────────── */
.fab-container{position:fixed;right:20px;bottom:20px;z-index:40;display:flex;flex-direction:column;gap:10px}
.fab{
  width:46px;height:46px;border-radius:50%;
  display:grid;place-items:center;
  background:var(--surface);border:1px solid var(--line-2);
  color:var(--text-hi);font-size:16px;
  box-shadow:var(--shadow);
  transition:all .25s ease;
  cursor:pointer;
}
.fab:hover{transform:translateY(-3px);border-color:var(--accent);color:var(--accent)}
.fab.fab-wa{background:#25d366;color:#fff;border-color:#25d366}
.fab.fab-wa:hover{background:#1fbf5b;color:#fff;border-color:#1fbf5b}
.fab-top{opacity:0;pointer-events:none;transform:translateY(10px)}
.fab-top.show{opacity:1;pointer-events:auto;transform:none}

/* ───────────── REVEAL ANIMATION ───────────── */
.reveal{opacity:0;transform:translateY(20px);transition:opacity .7s ease,transform .7s ease}
.reveal.visible{opacity:1;transform:none}
@media(prefers-reduced-motion:reduce){
  .reveal{opacity:1;transform:none;transition:none}
  *,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}
}

/* ───────────── LIGHTBOX (foto profil) ───────────── */
.photo-lb{
  display:none;position:fixed;inset:0;z-index:999;
  background:rgba(0,0,0,.86);
  align-items:center;justify-content:center;padding:24px;
  backdrop-filter:blur(8px);
}
.photo-lb.open{display:flex;animation:fade-in .25s ease}
.photo-lb img{
  max-width:min(420px,90vw);max-height:80vh;
  border-radius:50%;
  box-shadow:0 30px 80px rgba(0,0,0,.6);
  animation:zoom-in .3s ease;
}
.photo-lb-close{
  position:absolute;top:20px;right:24px;
  font-size:24px;color:#fff;opacity:.7;
  width:40px;height:40px;border-radius:50%;
  display:grid;place-items:center;
  transition:opacity .15s ease,background .15s ease;
}
.photo-lb-close:hover{opacity:1;background:rgba(255,255,255,.1)}

@keyframes fade-in{from{opacity:0}to{opacity:1}}
@keyframes zoom-in{from{transform:scale(.92);opacity:0}to{transform:scale(1);opacity:1}}
</style>
</head>
<body>

<!-- ═══════════ NAV ═══════════ -->
<nav class="nav" id="nav">
  <div class="nav-inner">
    <a href="#top" class="brand">
      <span class="brand-mark">RS</span>
      <span class="brand-text">rsby.my.id</span>
    </a>
    <div class="nav-links">
      <a href="#about"      class="nav-link">About</a>
      <a href="#experience" class="nav-link">Experience</a>
      <a href="#projects"   class="nav-link">Projects</a>
      <a href="#skills"     class="nav-link">Skills</a>
      <a href="#contact"    class="nav-link">Contact</a>
    </div>
    <button class="theme-btn" onclick="toggleTheme()" aria-label="Toggle tema">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="theme-icon"></svg>
    </button>
  </div>
</nav>

<div id="top"></div>

<!-- ═══════════ HERO ═══════════ -->
<header class="wrap hero">
  <div class="hero-meta">Portfolio · 2026</div>
  <div class="hero-grid">
    <div>
      <h1 class="hero-name">
        Halo, saya <span class="accent"><?= esc($nama) ?></span>.
      </h1>
      <p class="hero-tagline"><?= esc($tagline) ?>.</p>
      <div class="status-pill">
        <span class="status-dot"></span>
        <?= esc($status) ?>
      </div>
      <div class="hero-actions">
        <a href="#contact" class="btn btn-primary">
          <i class="btn-icon fas fa-paper-plane"></i> Hire me
        </a>
        <a href="<?= esc($github) ?>" target="_blank" rel="noopener" class="btn">
          <i class="btn-icon fab fa-github"></i> GitHub
        </a>
        <a href="<?= esc($linkedin) ?>" target="_blank" rel="noopener" class="btn">
          <i class="btn-icon fab fa-linkedin"></i> LinkedIn
        </a>
      </div>
    </div>
    <div class="hero-photo" id="hero-photo" role="img" aria-label="Foto <?= esc($nama) ?>">
      <img src="<?= esc($foto_raw) ?>" alt="<?= esc($nama) ?>" width="140" height="140" loading="eager" decoding="async">
    </div>
  </div>
</header>

<!-- ═══════════ ABOUT ═══════════ -->
<section id="about" class="wrap">
  <div class="section-head">
    <span class="section-tag">01</span>
    <h2 class="section-title">About</h2>
  </div>
  <div class="about-card reveal">
    <p>
      Saya seorang <strong>IT Support &amp; Full-stack Developer</strong> berbasis di Jawa Tengah,
      dengan pengalaman menangani <em>infrastruktur jaringan</em>, <em>server</em>, dan pengembangan
      aplikasi web untuk instansi pemerintah maupun swasta.
    </p>
    <p>
      Fokus utama saya: memberikan solusi teknologi yang reliabel, efisien, dan tepat sasaran —
      mulai dari setup jaringan enterprise hingga membangun sistem berbasis web dari nol.
    </p>
  </div>
</section>

<!-- ═══════════ EXPERIENCE ═══════════ -->
<?php if (!empty($experiences)): ?>
<section id="experience" class="wrap">
  <div class="section-head">
    <span class="section-tag">02</span>
    <h2 class="section-title">Experience</h2>
  </div>
  <div class="timeline reveal">
    <?php foreach ($experiences as $exp): ?>
      <article class="exp-item <?= !empty($exp['is_active']) ? 'active' : '' ?>">
        <div class="exp-row">
          <div class="exp-title"><?= esc($exp['job_title']) ?></div>
          <div class="exp-year"><?= esc($exp['year_range']) ?></div>
        </div>
        <div class="exp-co"><?= esc($exp['company']) ?></div>
        <?php
        $bullets = array_values(array_filter(array_map('trim', explode("\n", $exp['description']))));
        ?>
        <?php if (!empty($exp['is_active'])): ?>
          <div class="exp-desc">
            <ul>
              <?php foreach ($bullets as $b): ?><li><?= esc($b) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php else: ?>
          <details class="exp-toggle">
            <summary>Lihat detail</summary>
            <div class="exp-desc">
              <ul>
                <?php foreach ($bullets as $b): ?><li><?= esc($b) ?></li><?php endforeach; ?>
              </ul>
            </div>
          </details>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════ PROJECTS ═══════════ -->
<?php if (!empty($projects)): ?>
<section id="projects" class="wrap">
  <div class="section-head">
    <span class="section-tag">03</span>
    <h2 class="section-title">Projects</h2>
  </div>
  <div class="proj-grid">
    <?php foreach ($projects as $p):
      $is_new = isset($p['days_ago']) && $p['days_ago'] !== null && (int)$p['days_ago'] <= 30;
      $is_external = !empty($p['link_url']) && preg_match('#^https?://#i', $p['link_url']);
    ?>
      <article class="proj-card reveal">
        <div class="proj-icon"><i class="<?= esc($p['icon_class']) ?>"></i></div>
        <div class="proj-title-row">
          <div class="proj-title"><?= esc($p['title']) ?></div>
          <?php if ($is_new): ?><span class="proj-new">NEW</span><?php endif; ?>
        </div>
        <p class="proj-desc"><?= esc($p['description']) ?></p>
        <?php if (!empty($p['link_url'])): ?>
          <a class="proj-link"
             href="<?= esc($p['link_url']) ?>"
             <?= $is_external ? 'target="_blank" rel="noopener"' : '' ?>>
            Lihat project <i class="fas fa-arrow-up-right-from-square"></i>
          </a>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════ VIDEOS ═══════════ -->
<?php if (!empty($videos)): ?>
<section id="videos" class="wrap">
  <div class="section-head">
    <span class="section-tag">04</span>
    <h2 class="section-title">Video Showcase</h2>
  </div>
  <div class="videos-grid">
    <?php foreach ($videos as $i => $v):
      $thumb = ytThumb($v['video_url']);
      $embed = ytEmbed($v['video_url']);
      if (!$embed) continue;
      $vid = 'video-' . (int)$v['id'];
    ?>
      <article class="video-card reveal">
        <div class="video-thumb" id="thumb-<?= $vid ?>"
             onclick="playVideo('<?= $vid ?>','<?= esc($embed) ?>')"
             role="button" aria-label="Putar <?= esc($v['title']) ?>">
          <?php if ($thumb): ?>
            <img src="<?= esc($thumb) ?>" alt="<?= esc($v['title']) ?>" loading="lazy" decoding="async">
          <?php else: ?>
            <div style="width:100%;height:100%;background:#111;display:grid;place-items:center;color:#444;font-size:32px"><i class="fas fa-film"></i></div>
          <?php endif; ?>
          <div class="video-play"><div class="video-play-btn"><i class="fas fa-play"></i></div></div>
        </div>
        <div class="video-iframe-wrap" id="iframe-<?= $vid ?>"></div>
        <div class="video-body">
          <div class="video-title"><?= esc($v['title']) ?></div>
          <?php if (!empty($v['description'])): ?>
            <div class="video-desc"><?= esc($v['description']) ?></div>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════ SKILLS ═══════════ -->
<section id="skills" class="wrap">
  <div class="section-head">
    <span class="section-tag">05</span>
    <h2 class="section-title">Skills &amp; Stack</h2>
  </div>
  <div class="skill-groups reveal">
    <?php foreach ($skills_raw as $group => $list): ?>
      <div>
        <div class="skill-group-label"><?= esc($group) ?></div>
        <div class="skill-tags">
          <?php foreach ($list as $s): ?>
            <span class="skill-tag"><?= esc($s) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ═══════════ CLIENTS ═══════════ -->
<?php if (!empty($clients)): ?>
<section id="clients" class="wrap">
  <div class="section-head">
    <span class="section-tag">06</span>
    <h2 class="section-title">Trusted By</h2>
  </div>
  <div class="clients-grid reveal">
    <?php foreach ($clients as $c):
      $tag = !empty($c['url']) ? 'a' : 'div';
      $attrs = !empty($c['url']) ? 'href="' . esc($c['url']) . '" target="_blank" rel="noopener"' : '';
    ?>
      <<?= $tag ?> class="client-card" <?= $attrs ?>>
        <div class="client-icon"><i class="<?= esc($c['icon_class'] ?: 'fas fa-building') ?>"></i></div>
        <div>
          <div class="client-name"><?= esc($c['name']) ?></div>
          <?php if (!empty($c['location'])): ?>
            <div class="client-loc"><?= esc($c['location']) ?></div>
          <?php endif; ?>
        </div>
      </<?= $tag ?>>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════ CONTACT / CTA ═══════════ -->
<section id="contact" class="wrap">
  <div class="cta reveal">
    <h2 class="cta-title">Punya proyek yang menarik?</h2>
    <p class="cta-sub">Mari diskusikan kebutuhan IT, jaringan, atau aplikasi web Anda. Saya siap bantu.</p>
    <div class="cta-actions">
      <a href="mailto:<?= esc($email) ?>" class="btn btn-primary">
        <i class="btn-icon fas fa-envelope"></i> <?= esc($email) ?>
      </a>
      <?php if ($whatsapp): ?>
        <a href="https://wa.me/<?= esc($whatsapp) ?>" target="_blank" rel="noopener" class="btn">
          <i class="btn-icon fab fa-whatsapp" style="color:#25d366"></i> WhatsApp
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer class="wrap">
  <p>© <?= date('Y') ?> <?= esc($nama) ?> — built with care · <a href="/sitemap.php">sitemap</a></p>
</footer>

<!-- ═══════════ FAB ═══════════ -->
<div class="fab-container">
  <button class="fab fab-top" id="fab-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Scroll ke atas">
    <i class="fas fa-arrow-up"></i>
  </button>
  <?php if ($whatsapp): ?>
    <a class="fab fab-wa" href="https://wa.me/<?= esc($whatsapp) ?>" target="_blank" rel="noopener" aria-label="Chat WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>
  <?php endif; ?>
</div>

<!-- ═══════════ PHOTO LIGHTBOX ═══════════ -->
<div class="photo-lb" id="photo-lb" onclick="closePhotoLb()">
  <button class="photo-lb-close" aria-label="Tutup">×</button>
  <img src="<?= esc($foto_raw) ?>" alt="<?= esc($nama) ?>">
</div>

<script>
/* ── THEME ── */
const ICONS = {
  dark: '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
  light:'<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'
};
function setTheme(t){
  document.documentElement.setAttribute('data-theme',t);
  document.getElementById('theme-icon').innerHTML = t==='dark' ? ICONS.dark : ICONS.light;
  try{localStorage.setItem('rsby-theme',t);}catch(e){}
}
function toggleTheme(){
  const cur = document.documentElement.getAttribute('data-theme')||'dark';
  setTheme(cur==='dark'?'light':'dark');
}
setTheme(document.documentElement.getAttribute('data-theme')||'dark');

/* ── NAV scroll ── */
const nav = document.getElementById('nav');
const fabTop = document.getElementById('fab-top');
let lastY = 0;
function onScroll(){
  const y = window.scrollY;
  nav.classList.toggle('scrolled', y > 4);
  fabTop.classList.toggle('show', y > 600);
  lastY = y;
}
addEventListener('scroll', onScroll, { passive:true });
onScroll();

/* ── ACTIVE NAV via IntersectionObserver ── */
const sections = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-link');
const navObs = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){
      const id = e.target.id;
      navLinks.forEach(l=>l.classList.toggle('active', l.getAttribute('href') === '#'+id));
    }
  });
},{ rootMargin:'-40% 0px -55% 0px', threshold:0 });
sections.forEach(s=>navObs.observe(s));

/* ── REVEAL on scroll ── */
const revealObs = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){ e.target.classList.add('visible'); revealObs.unobserve(e.target); }
  });
},{ rootMargin:'0px 0px -50px 0px', threshold:.1 });
document.querySelectorAll('.reveal').forEach(el=>revealObs.observe(el));

/* ── PHOTO LIGHTBOX ── */
document.getElementById('hero-photo').addEventListener('click', ()=>{
  document.getElementById('photo-lb').classList.add('open');
  document.body.style.overflow='hidden';
});
function closePhotoLb(){
  document.getElementById('photo-lb').classList.remove('open');
  document.body.style.overflow='';
}
addEventListener('keydown', e=>{
  if(e.key==='Escape') closePhotoLb();
});

/* ── VIDEO INLINE PLAY ── */
function playVideo(vid, embed){
  document.querySelectorAll('.video-iframe-wrap.active').forEach(el=>{
    if(el.id !== 'iframe-'+vid){
      el.innerHTML = '';
      el.classList.remove('active');
      const t = document.getElementById(el.id.replace('iframe-','thumb-'));
      if(t) t.classList.remove('hidden');
    }
  });
  const thumb = document.getElementById('thumb-'+vid);
  const wrap  = document.getElementById('iframe-'+vid);
  if(!wrap) return;
  thumb.classList.add('hidden');
  wrap.classList.add('active');
  wrap.innerHTML = '<iframe src="'+embed+'&autoplay=1" allow="autoplay;encrypted-media;fullscreen" allowfullscreen loading="lazy"></iframe>';
}

/* ── COPY EMAIL on long-press optional (skipped to keep slim) ── */
</script>
</body>
</html>
