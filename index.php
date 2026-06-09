<?php
require 'admin/koneksi.php';

/* ─────────────── DATA ─────────────── */
$profil = $pdo->query("SELECT * FROM profile_settings LIMIT 1")->fetch() ?: [];

$nama     = $profil['full_name']           ?? 'Rizqi Subagyo';
$tagline  = $profil['tagline']             ?? 'IT Support Specialist | Full-stack Developer';
$status   = $profil['availability_status'] ?? 'Tersedia untuk proyek baru';
$email    = $profil['email']               ?? 'rizqisubagyo07@gmail.com';
$github   = $profil['github_link']         ?? 'https://github.com/monologstorycom-pixel';
$linkedin = $profil['linkedin_link']       ?? 'https://www.linkedin.com/in/rizqi-subagyo-7ab331380';
$whatsapp = preg_replace('/[^0-9]/', '', $profil['whatsapp'] ?? '');
$foto_raw = !empty($profil['profile_picture']) ? $profil['profile_picture'] : 'https://avatars.githubusercontent.com/u/252295342?v=4';
$favicon  = !empty($profil['favicon_url'] ?? '') ? $profil['favicon_url'] : 'favicon.svg';

function abs_url(string $url, string $base = 'https://rsby.my.id/'): string {
    if (preg_match('#^https?://#i', $url)) return $url;
    return rtrim($base, '/') . '/' . ltrim($url, '/');
}
$foto_abs = abs_url($foto_raw);
function esc($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/* EXPERIENCE */
$experiences = [];
try { $experiences = $pdo->query("SELECT * FROM experiences ORDER BY is_active DESC, id DESC")->fetchAll(); } catch (Exception $e) {}

/* PROJECTS */
$projects = [];
try {
    $projects = $pdo->query("SELECT *, TIMESTAMPDIFF(DAY, created_at, NOW()) AS days_ago FROM projects ORDER BY id DESC")->fetchAll();
} catch (Exception $e) {
    try { $projects = $pdo->query("SELECT *, NULL AS days_ago FROM projects ORDER BY id DESC")->fetchAll(); } catch (Exception $e2) {}
}

/* VIDEOS dihilangkan dari halaman publik */

/* SKILLS */
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

/* CLIENTS */
$clients = [];
try { $clients = $pdo->query("SELECT * FROM clients ORDER BY sort_order, id")->fetchAll(); } catch (Exception $e) {}
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

<meta property="og:type" content="website">
<meta property="og:url" content="https://rsby.my.id/">
<meta property="og:title" content="<?= esc($nama) ?> — IT Support &amp; Full-stack Developer">
<meta property="og:description" content="<?= esc($tagline) ?>">
<meta property="og:image" content="<?= esc($foto_abs) ?>">
<meta property="og:locale" content="id_ID">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= esc($nama) ?>">
<meta name="twitter:description" content="<?= esc($tagline) ?>">
<meta name="twitter:image" content="<?= esc($foto_abs) ?>">

<script type="application/ld+json"><?= json_encode([
    '@context'=>'https://schema.org','@type'=>'Person','name'=>$nama,
    'jobTitle'=>'IT Support Specialist & Full-stack Developer','email'=>$email,
    'url'=>'https://rsby.my.id/','image'=>$foto_abs,
    'sameAs'=>array_values(array_filter([$github,$linkedin])),
    'knowsAbout'=>['IT Support','Networking','MikroTik','PHP','Python','Next.js','Docker','Linux'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<link rel="icon" href="<?= esc($favicon) ?>" type="<?= str_ends_with($favicon, '.svg') ? 'image/svg+xml' : 'image/png' ?>">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#0a0a0a">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"></noscript>
<link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

<script>(function(){try{var t=localStorage.getItem('rsby-theme')||'dark';document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>

<style>
/* ════════ TOKENS — monochrome + 1 green accent ════════ */
[data-theme="dark"]{
  --bg:#0a0a0b; --bg-2:#0f0f11; --card:#131316; --card-2:#1a1a1e;
  --line:rgba(255,255,255,.07); --line-2:rgba(255,255,255,.12);
  --text:#a1a1aa; --text-hi:#fafafa; --text-soft:#71717a; --text-mute:#52525b;
  --accent:#4ade80; --accent-soft:rgba(74,222,128,.12); --accent-line:rgba(74,222,128,.3);
  --shadow:0 1px 2px rgba(0,0,0,.4),0 16px 40px -12px rgba(0,0,0,.5);
}
[data-theme="light"]{
  --bg:#fbfbfa; --bg-2:#ffffff; --card:#ffffff; --card-2:#f6f6f4;
  --line:rgba(0,0,0,.07); --line-2:rgba(0,0,0,.13);
  --text:#52525b; --text-hi:#18181b; --text-soft:#71717a; --text-mute:#a1a1aa;
  --accent:#16a34a; --accent-soft:rgba(22,163,74,.08); --accent-line:rgba(22,163,74,.25);
  --shadow:0 1px 2px rgba(0,0,0,.04),0 12px 32px -12px rgba(0,0,0,.1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{
  font-family:'Inter',system-ui,-apple-system,sans-serif;
  background:var(--bg);color:var(--text);
  font-size:15px;line-height:1.7;font-weight:400;letter-spacing:-.01em;
  -webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
  min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s;
}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}
button{font:inherit;color:inherit;background:none;border:none;cursor:pointer}
::selection{background:var(--accent);color:#0a0a0b}
::-webkit-scrollbar{width:9px;height:9px}
::-webkit-scrollbar-thumb{background:var(--line-2);border-radius:9px;border:2px solid var(--bg)}

h1,h2,h3,.font-display{font-family:'Space Grotesk','Inter',sans-serif;letter-spacing:-.02em}

.wrap{max-width:760px;margin:0 auto;padding:0 24px;position:relative}

/* ════════ NAV ════════ */
.nav{position:sticky;top:0;z-index:50;background:color-mix(in srgb,var(--bg) 80%,transparent);
  backdrop-filter:blur(16px) saturate(150%);-webkit-backdrop-filter:blur(16px) saturate(150%);
  border-bottom:1px solid transparent;transition:border-color .3s}
.nav.scrolled{border-color:var(--line)}
.nav-inner{max-width:760px;margin:0 auto;padding:15px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.brand{display:flex;align-items:center;gap:10px;font-weight:600;color:var(--text-hi)}
.brand-mark{width:30px;height:30px;border-radius:8px;background:var(--text-hi);color:var(--bg);
  display:grid;place-items:center;font-family:'Space Grotesk',monospace;font-weight:700;font-size:13px}
.brand-text{font-size:14px;font-family:'Space Grotesk',sans-serif}
.nav-right{display:flex;align-items:center;gap:6px}
.nav-links{display:flex;gap:2px}
.nav-link{color:var(--text-soft);font-size:13.5px;font-weight:500;padding:7px 12px;border-radius:8px;transition:color .15s,background .15s}
.nav-link:hover{color:var(--text-hi)}
.nav-link.active{color:var(--accent)}
@media(max-width:640px){.nav-links{display:none}}
.icon-btn{width:36px;height:36px;border-radius:9px;display:grid;place-items:center;color:var(--text-soft);
  border:1px solid var(--line);transition:all .2s}
.icon-btn:hover{color:var(--text-hi);border-color:var(--line-2)}
.icon-btn svg{width:16px;height:16px}

/* ════════ HERO ════════ */
.hero{padding:72px 0 40px}
@media(max-width:640px){.hero{padding:48px 0 28px}}
.hero-photo{width:88px;height:88px;border-radius:24px;overflow:hidden;border:1px solid var(--line-2);
  margin-bottom:28px;cursor:pointer;transition:transform .3s,border-radius .3s;background:var(--card)}
.hero-photo:hover{transform:translateY(-2px);border-radius:50%}
.hero-photo img{width:100%;height:100%;object-fit:cover}
.hero-status{display:inline-flex;align-items:center;gap:8px;font-size:13px;color:var(--text-soft);
  margin-bottom:18px;font-weight:500}
.hero-status .dot{width:7px;height:7px;border-radius:50%;background:var(--accent);
  box-shadow:0 0 0 4px var(--accent-soft);animation:pulse 2.4s ease infinite}
@keyframes pulse{0%,100%{box-shadow:0 0 0 4px var(--accent-soft)}50%{box-shadow:0 0 0 7px transparent}}
.hero h1{font-size:clamp(2.2rem,6vw,3.1rem);font-weight:700;color:var(--text-hi);line-height:1.08;margin-bottom:18px}
.hero h1 .grad{color:var(--accent)}
.hero-bio{font-size:16px;line-height:1.75;color:var(--text);max-width:560px;margin-bottom:26px}
.hero-bio strong{color:var(--text-hi);font-weight:600}
.hero-actions{display:flex;flex-wrap:wrap;gap:10px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border-radius:10px;
  font-size:14px;font-weight:500;border:1px solid var(--line-2);background:var(--card);
  color:var(--text-hi);transition:all .18s}
.btn:hover{border-color:var(--text-soft);transform:translateY(-2px)}
.btn-primary{background:var(--accent);color:#0a0a0b;border-color:var(--accent);font-weight:600}
.btn-primary:hover{filter:brightness(1.08)}
.btn i{font-size:14px}

/* ════════ SECTION ════════ */
section{padding:48px 0;scroll-margin-top:76px}
@media(max-width:640px){section{padding:36px 0}}
.sec-head{display:flex;align-items:center;gap:12px;margin-bottom:28px}
.sec-num{font-family:'Space Grotesk',monospace;font-size:13px;font-weight:600;color:var(--accent)}
.sec-title{font-size:20px;font-weight:600;color:var(--text-hi)}
.sec-line{flex:1;height:1px;background:var(--line)}

/* ════════ EXPERIENCE ════════ */
.tl{position:relative;padding-left:26px}
.tl::before{content:'';position:absolute;left:4px;top:6px;bottom:6px;width:1px;background:var(--line-2)}
.tl-item{position:relative;padding-bottom:30px}
.tl-item:last-child{padding-bottom:0}
.tl-item::before{content:'';position:absolute;left:-26px;top:5px;width:9px;height:9px;border-radius:50%;
  background:var(--bg);border:1.5px solid var(--line-2);transition:all .25s}
.tl-item.on::before{background:var(--accent);border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-soft)}
.tl-top{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:baseline;margin-bottom:3px}
.tl-role{font-size:15.5px;font-weight:600;color:var(--text-hi)}
.tl-year{font-family:'Space Grotesk',monospace;font-size:12px;color:var(--text-soft);white-space:nowrap}
.tl-co{font-size:14px;color:var(--accent);font-weight:500;margin-bottom:10px}
.tl-desc{font-size:14px;color:var(--text);line-height:1.7}
.tl-desc ul{list-style:none;display:flex;flex-direction:column;gap:5px}
.tl-desc li{position:relative;padding-left:16px}
.tl-desc li::before{content:'';position:absolute;left:0;top:11px;width:6px;height:1px;background:var(--accent)}
details.tg>summary{list-style:none;cursor:pointer;font-size:13px;color:var(--text-soft);
  display:inline-flex;align-items:center;gap:6px;padding:3px 0;user-select:none;transition:color .15s}
details.tg>summary::-webkit-details-marker{display:none}
details.tg>summary:hover{color:var(--text-hi)}
details.tg>summary::after{content:'+';font-size:15px;transition:transform .2s}
details.tg[open]>summary::after{transform:rotate(45deg)}
details.tg[open]>summary{margin-bottom:8px}

/* ════════ PROJECTS ════════ */
.proj-list{display:flex;flex-direction:column;gap:10px}
.proj{display:flex;align-items:flex-start;gap:16px;padding:18px 20px;border:1px solid var(--line);
  border-radius:14px;background:var(--card);transition:all .22s;position:relative;overflow:hidden}
.proj:hover{border-color:var(--line-2);background:var(--card-2);transform:translateY(-2px)}
.proj-ic{width:42px;height:42px;border-radius:11px;background:var(--accent-soft);color:var(--accent);
  display:grid;place-items:center;font-size:17px;flex-shrink:0}
.proj-body{flex:1;min-width:0}
.proj-top{display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap}
.proj-title{font-size:15.5px;font-weight:600;color:var(--text-hi)}
.proj-new{font-family:'Space Grotesk',monospace;font-size:9.5px;font-weight:700;letter-spacing:.07em;
  padding:2px 7px;border-radius:99px;background:var(--accent-soft);color:var(--accent);
  border:1px solid var(--accent-line);text-transform:uppercase}
.proj-desc{font-size:13.5px;color:var(--text-soft);line-height:1.6}
.proj-arrow{color:var(--text-mute);font-size:13px;align-self:center;transition:transform .2s,color .2s;flex-shrink:0}
.proj:hover .proj-arrow{color:var(--accent);transform:translate(3px,-3px)}

/* ════════ SKILLS ════════ */
.skills{display:flex;flex-direction:column;gap:20px}
.skill-row{display:grid;grid-template-columns:120px 1fr;gap:16px;align-items:start}
@media(max-width:520px){.skill-row{grid-template-columns:1fr;gap:8px}}
.skill-label{font-family:'Space Grotesk',monospace;font-size:12px;color:var(--text-soft);
  font-weight:500;padding-top:6px}
.skill-tags{display:flex;flex-wrap:wrap;gap:7px}
.skill-tag{font-size:13px;padding:5px 12px;border-radius:8px;background:var(--card);
  border:1px solid var(--line);color:var(--text);transition:all .18s;font-weight:500}
.skill-tag:hover{border-color:var(--accent-line);color:var(--accent);background:var(--accent-soft);transform:translateY(-1px)}

/* ════════ CLIENTS ════════ */
.cl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px}
.cl{display:flex;align-items:center;gap:12px;padding:14px 16px;border:1px solid var(--line);
  border-radius:12px;background:var(--card);transition:all .2s}
.cl:hover{border-color:var(--line-2);background:var(--card-2)}
a.cl:hover{border-color:var(--accent-line)}
.cl-ic{width:34px;height:34px;border-radius:9px;background:var(--bg-2);display:grid;place-items:center;
  color:var(--text-soft);font-size:14px;flex-shrink:0;transition:color .2s}
.cl:hover .cl-ic{color:var(--accent)}
.cl-name{font-size:13.5px;font-weight:500;color:var(--text-hi);line-height:1.3}
.cl-loc{font-size:12px;color:var(--text-soft);margin-top:1px}

/* ════════ CONTACT ════════ */
.contact{padding:44px 32px;border-radius:20px;background:var(--card);border:1px solid var(--line);
  text-align:center;position:relative;overflow:hidden;margin-top:8px}
.contact::before{content:'';position:absolute;top:0;left:50%;transform:translateX(-50%);
  width:160px;height:1px;background:linear-gradient(90deg,transparent,var(--accent),transparent)}
.contact h2{font-size:26px;font-weight:700;color:var(--text-hi);margin-bottom:10px}
.contact p{font-size:15px;color:var(--text-soft);margin-bottom:24px;max-width:420px;margin-left:auto;margin-right:auto}
.contact-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}

/* ════════ FOOTER ════════ */
footer{margin-top:56px;padding:28px 0 36px;border-top:1px solid var(--line);text-align:center}
.foot-row{display:flex;justify-content:center;gap:16px;margin-bottom:14px}
.foot-row a{width:38px;height:38px;border-radius:10px;display:grid;place-items:center;
  border:1px solid var(--line);color:var(--text-soft);transition:all .2s}
.foot-row a:hover{color:var(--accent);border-color:var(--accent-line);transform:translateY(-2px)}
.foot-txt{font-size:12.5px;color:var(--text-mute);font-family:'Space Grotesk',monospace}

/* ════════ FAB ════════ */
.fab{position:fixed;right:18px;bottom:18px;z-index:40;display:flex;flex-direction:column;gap:10px}
.fab a,.fab button{width:46px;height:46px;border-radius:50%;display:grid;place-items:center;
  box-shadow:var(--shadow);transition:all .25s}
.fab-wa{background:#25d366;color:#fff;font-size:20px}
.fab-wa:hover{transform:translateY(-3px) scale(1.05)}
.fab-top{background:var(--card);border:1px solid var(--line-2);color:var(--text-hi);font-size:15px;
  opacity:0;pointer-events:none;transform:translateY(12px)}
.fab-top.show{opacity:1;pointer-events:auto;transform:none}
.fab-top:hover{border-color:var(--accent);color:var(--accent)}

/* ════════ REVEAL ════════ */
.rv{opacity:0;transform:translateY(18px);transition:opacity .65s ease,transform .65s ease}
.rv.in{opacity:1;transform:none}
@media(prefers-reduced-motion:reduce){.rv{opacity:1;transform:none;transition:none}*{animation-duration:.01ms!important;transition-duration:.01ms!important}}

/* ════════ LIGHTBOX ════════ */
.plb{display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.9);
  align-items:center;justify-content:center;padding:24px;backdrop-filter:blur(8px)}
.plb.open{display:flex;animation:fin .25s ease}
.plb img{max-width:min(440px,90vw);max-height:82vh;border-radius:20px;box-shadow:0 30px 80px rgba(0,0,0,.6);animation:zin .3s ease}
.plb-x{position:absolute;top:22px;right:24px;font-size:26px;color:#fff;opacity:.7;width:42px;height:42px;
  border-radius:50%;display:grid;place-items:center;transition:all .15s}
.plb-x:hover{opacity:1;background:rgba(255,255,255,.1)}
@keyframes fin{from{opacity:0}to{opacity:1}}
@keyframes zin{from{transform:scale(.92);opacity:0}to{transform:scale(1);opacity:1}}
</style>
</head>
<body>

<nav class="nav" id="nav">
  <div class="nav-inner">
    <a href="#top" class="brand">
      <span class="brand-mark">RS</span>
      <span class="brand-text">rsby.my.id</span>
    </a>
    <div class="nav-right">
      <div class="nav-links">
        <a href="#about" class="nav-link">About</a>
        <a href="#work" class="nav-link">Work</a>
        <a href="#skills" class="nav-link">Skills</a>
        <a href="#contact" class="nav-link">Contact</a>
      </div>
      <button class="icon-btn" onclick="toggleTheme()" aria-label="Ganti tema">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="th-ic"></svg>
      </button>
    </div>
  </div>
</nav>

<div id="top"></div>

<!-- HERO -->
<header class="wrap hero">
  <div class="hero-photo" id="hero-photo" role="button" aria-label="Lihat foto">
    <img src="<?= esc($foto_raw) ?>" alt="<?= esc($nama) ?>" width="88" height="88" loading="eager">
  </div>
  <div class="hero-status"><span class="dot"></span><?= esc($status) ?></div>
  <h1>Halo, saya<br><span class="grad"><?= esc($nama) ?></span></h1>
  <p class="hero-bio">
    <strong>IT Support &amp; Full-stack Developer</strong> berbasis di Jawa Tengah.
    Saya bantu bangun infrastruktur jaringan yang reliabel dan aplikasi web yang rapi —
    dari setup enterprise sampai sistem dari nol.
  </p>
  <div class="hero-actions">
    <a href="#contact" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Hire me</a>
    <a href="<?= esc($github) ?>" target="_blank" rel="noopener" class="btn"><i class="fab fa-github"></i> GitHub</a>
    <a href="<?= esc($linkedin) ?>" target="_blank" rel="noopener" class="btn"><i class="fab fa-linkedin"></i> LinkedIn</a>
  </div>
</header>

<!-- ABOUT / EXPERIENCE -->
<?php if (!empty($experiences)): ?>
<section id="about" class="wrap">
  <div class="sec-head rv">
    <span class="sec-num">01</span>
    <h2 class="sec-title">Experience</h2>
    <div class="sec-line"></div>
  </div>
  <div class="tl rv">
    <?php foreach ($experiences as $exp):
      $bullets = array_values(array_filter(array_map('trim', explode("\n", $exp['description']))));
    ?>
      <article class="tl-item <?= !empty($exp['is_active']) ? 'on' : '' ?>">
        <div class="tl-top">
          <span class="tl-role"><?= esc($exp['job_title']) ?></span>
          <span class="tl-year"><?= esc($exp['year_range']) ?></span>
        </div>
        <div class="tl-co"><?= esc($exp['company']) ?></div>
        <?php if (!empty($exp['is_active'])): ?>
          <div class="tl-desc"><ul><?php foreach ($bullets as $b): ?><li><?= esc($b) ?></li><?php endforeach; ?></ul></div>
        <?php else: ?>
          <details class="tg"><summary>Lihat detail</summary>
            <div class="tl-desc"><ul><?php foreach ($bullets as $b): ?><li><?= esc($b) ?></li><?php endforeach; ?></ul></div>
          </details>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- PROJECTS -->
<?php if (!empty($projects)): ?>
<section id="work" class="wrap">
  <div class="sec-head rv">
    <span class="sec-num">02</span>
    <h2 class="sec-title">Projects</h2>
    <div class="sec-line"></div>
  </div>
  <div class="proj-list">
    <?php foreach ($projects as $p):
      $is_new = isset($p['days_ago']) && $p['days_ago'] !== null && (int)$p['days_ago'] <= 30;
      $ext = !empty($p['link_url']) && preg_match('#^https?://#i', $p['link_url']);
      $tag = !empty($p['link_url']) ? 'a' : 'div';
      $attr = !empty($p['link_url']) ? 'href="'.esc($p['link_url']).'"'.($ext?' target="_blank" rel="noopener"':'') : '';
    ?>
      <<?= $tag ?> class="proj rv" <?= $attr ?>>
        <div class="proj-ic"><i class="<?= esc($p['icon_class']) ?>"></i></div>
        <div class="proj-body">
          <div class="proj-top">
            <span class="proj-title"><?= esc($p['title']) ?></span>
            <?php if ($is_new): ?><span class="proj-new">New</span><?php endif; ?>
          </div>
          <div class="proj-desc"><?= esc($p['description']) ?></div>
        </div>
        <?php if (!empty($p['link_url'])): ?><i class="proj-arrow fas fa-arrow-up-right-from-square"></i><?php endif; ?>
      </<?= $tag ?>>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- SKILLS -->
<section id="skills" class="wrap">
  <div class="sec-head rv">
    <span class="sec-num">03</span>
    <h2 class="sec-title">Skills &amp; Stack</h2>
    <div class="sec-line"></div>
  </div>
  <div class="skills rv">
    <?php foreach ($skills_raw as $group => $list): ?>
      <div class="skill-row">
        <div class="skill-label"><?= esc($group) ?></div>
        <div class="skill-tags">
          <?php foreach ($list as $s): ?><span class="skill-tag"><?= esc($s) ?></span><?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CLIENTS -->
<?php if (!empty($clients)): ?>
<section id="clients" class="wrap">
  <div class="sec-head rv">
    <span class="sec-num">04</span>
    <h2 class="sec-title">Trusted By</h2>
    <div class="sec-line"></div>
  </div>
  <div class="cl-grid rv">
    <?php foreach ($clients as $c):
      $tag = !empty($c['url']) ? 'a' : 'div';
      $attr = !empty($c['url']) ? 'href="'.esc($c['url']).'" target="_blank" rel="noopener"' : '';
    ?>
      <<?= $tag ?> class="cl" <?= $attr ?>>
        <div class="cl-ic"><i class="<?= esc($c['icon_class'] ?: 'fas fa-building') ?>"></i></div>
        <div>
          <div class="cl-name"><?= esc($c['name']) ?></div>
          <?php if (!empty($c['location'])): ?><div class="cl-loc"><?= esc($c['location']) ?></div><?php endif; ?>
        </div>
      </<?= $tag ?>>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- CONTACT -->
<section id="contact" class="wrap">
  <div class="contact rv">
    <h2>Mari berkolaborasi.</h2>
    <p>Punya proyek IT, jaringan, atau aplikasi web? Saya siap bantu wujudkan.</p>
    <div class="contact-actions">
      <a href="mailto:<?= esc($email) ?>" class="btn btn-primary"><i class="fas fa-envelope"></i> Email saya</a>
      <?php if ($whatsapp): ?><a href="https://wa.me/<?= esc($whatsapp) ?>" target="_blank" rel="noopener" class="btn"><i class="fab fa-whatsapp" style="color:#25d366"></i> WhatsApp</a><?php endif; ?>
    </div>
  </div>
</section>

<footer class="wrap">
  <div class="foot-row">
    <a href="<?= esc($github) ?>" target="_blank" rel="noopener" aria-label="GitHub"><i class="fab fa-github"></i></a>
    <a href="<?= esc($linkedin) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
    <a href="mailto:<?= esc($email) ?>" aria-label="Email"><i class="fas fa-envelope"></i></a>
    <a href="/slws/" aria-label="Selawas Visual"><i class="fas fa-camera"></i></a>
  </div>
  <p class="foot-txt">© <?= date('Y') ?> <?= esc($nama) ?> · <a href="/sitemap.php" style="color:var(--accent)">sitemap</a></p>
</footer>

<div class="fab">
  <button class="fab-top" id="fab-top" onclick="scrollTo({top:0,behavior:'smooth'})" aria-label="Ke atas"><i class="fas fa-arrow-up"></i></button>
  <?php if ($whatsapp): ?><a class="fab-wa" href="https://wa.me/<?= esc($whatsapp) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a><?php endif; ?>
</div>

<div class="plb" id="plb" onclick="closePlb()">
  <button class="plb-x" aria-label="Tutup">×</button>
  <img src="<?= esc($foto_raw) ?>" alt="<?= esc($nama) ?>">
</div>

<script>
/* THEME */
const TI={dark:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M6.3 17.7l-1.4 1.4M19.1 4.9l-1.4 1.4"/>',light:'<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>'};
function setTheme(t){document.documentElement.setAttribute('data-theme',t);document.getElementById('th-ic').innerHTML=t==='dark'?TI.dark:TI.light;try{localStorage.setItem('rsby-theme',t)}catch(e){}}
function toggleTheme(){setTheme((document.documentElement.getAttribute('data-theme')||'dark')==='dark'?'light':'dark')}
setTheme(document.documentElement.getAttribute('data-theme')||'dark');

/* NAV + FAB scroll */
const nav=document.getElementById('nav'),fabTop=document.getElementById('fab-top');
addEventListener('scroll',()=>{const y=scrollY;nav.classList.toggle('scrolled',y>4);fabTop.classList.toggle('show',y>500)},{passive:true});

/* ACTIVE NAV */
const links=document.querySelectorAll('.nav-link');
const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){const id=e.target.id;links.forEach(l=>l.classList.toggle('active',l.getAttribute('href')==='#'+id))}}),{rootMargin:'-45% 0px -50% 0px'});
document.querySelectorAll('section[id]').forEach(s=>io.observe(s));

/* REVEAL */
const ro=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');ro.unobserve(e.target)}}),{rootMargin:'0px 0px -40px 0px',threshold:.08});
document.querySelectorAll('.rv').forEach(el=>ro.observe(el));

/* PHOTO LIGHTBOX */
document.getElementById('hero-photo').onclick=()=>{document.getElementById('plb').classList.add('open');document.body.style.overflow='hidden'};
function closePlb(){document.getElementById('plb').classList.remove('open');document.body.style.overflow=''}
addEventListener('keydown',e=>{if(e.key==='Escape')closePlb()});
</script>
</body>
</html>
