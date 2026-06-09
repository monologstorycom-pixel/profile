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
// Flat list skill untuk hero pills (ambil maksimal 6 yang representatif)
$skill_flat = [];
foreach ($skills_raw as $g => $list) foreach ($list as $s) $skill_flat[] = $s;
$hero_pills = array_slice(array_values(array_unique($skill_flat)), 0, 6);

/* CLIENTS */
$clients = [];
try { $clients = $pdo->query("SELECT * FROM clients ORDER BY sort_order, id")->fetchAll(); } catch (Exception $e) {}

/* STATS */
$jml_proj = count($projects);
$jml_klien = count($clients);
// Hitung tahun pengalaman dari experience paling lama (ambil angka tahun pertama)
$min_year = (int)date('Y');
foreach ($experiences as $e) {
    if (preg_match('/(20\d{2})/', $e['year_range'] ?? '', $m)) $min_year = min($min_year, (int)$m[1]);
}
$years_exp = max(1, (int)date('Y') - $min_year);

/* TOOLS — devicon CDN (logo asli) */
$tools = [
  ['name'=>'PHP',      'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg'],
  ['name'=>'Python',   'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/python/python-original.svg'],
  ['name'=>'Next.js',  'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nextjs/nextjs-original.svg', 'invert'=>true],
  ['name'=>'Docker',   'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/docker/docker-original.svg'],
  ['name'=>'Linux',    'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/linux/linux-original.svg'],
  ['name'=>'MySQL',    'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg'],
  ['name'=>'Proxmox',  'src'=>'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/proxmox/proxmox-original.svg'],
  ['name'=>'MikroTik', 'fa'=>'fas fa-network-wired'],
];
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
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
<meta name="theme-color" content="#ffffff">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap"></noscript>
<link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

<script>(function(){try{var t=localStorage.getItem('rsby-theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})()</script>

<style>
/* ════════ TOKENS ════════ */
[data-theme="light"]{
  --bg:#f4f4f2; --panel:#ffffff; --panel-2:#fafafa;
  --line:rgba(0,0,0,.08); --line-2:rgba(0,0,0,.14);
  --text:#3f3f46; --text-hi:#111114; --text-soft:#71717a; --text-mute:#a1a1aa;
  --accent:#15a34a; --accent-soft:rgba(21,163,74,.1); --accent-line:rgba(21,163,74,.28);
  --ink:#111114; --on-ink:#ffffff;
  --shadow:0 1px 2px rgba(0,0,0,.04),0 18px 40px -16px rgba(0,0,0,.16);
  --shadow-sm:0 1px 2px rgba(0,0,0,.05),0 6px 18px -8px rgba(0,0,0,.12);
}
[data-theme="dark"]{
  --bg:#0b0b0d; --panel:#141417; --panel-2:#1a1a1e;
  --line:rgba(255,255,255,.08); --line-2:rgba(255,255,255,.14);
  --text:#a1a1aa; --text-hi:#fafafa; --text-soft:#8a8a94; --text-mute:#52525b;
  --accent:#4ade80; --accent-soft:rgba(74,222,128,.12); --accent-line:rgba(74,222,128,.3);
  --ink:#fafafa; --on-ink:#0b0b0d;
  --shadow:0 1px 2px rgba(0,0,0,.4),0 18px 44px -14px rgba(0,0,0,.6);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 8px 20px -10px rgba(0,0,0,.5);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
body{font-family:'Inter',system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--text);
  font-size:15px;line-height:1.65;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
  min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s;letter-spacing:-.01em}
img{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
button{font:inherit;color:inherit;background:none;border:none;cursor:pointer}
::selection{background:var(--accent);color:var(--on-ink)}
::-webkit-scrollbar{width:10px}::-webkit-scrollbar-thumb{background:var(--line-2);border-radius:10px;border:3px solid var(--bg)}
.serif{font-family:'Instrument Serif',Georgia,serif;font-weight:400;letter-spacing:0}

.wrap{max-width:980px;margin:0 auto;padding:0 24px}

/* ════════ NAV ════════ */
.nav{position:fixed;top:14px;left:0;right:0;z-index:60;display:flex;justify-content:center;pointer-events:none}
.nav-pill{pointer-events:auto;display:flex;align-items:center;gap:6px;padding:7px 7px 7px 16px;
  background:color-mix(in srgb,var(--panel) 82%,transparent);backdrop-filter:blur(16px) saturate(160%);
  -webkit-backdrop-filter:blur(16px) saturate(160%);border:1px solid var(--line);border-radius:99px;box-shadow:var(--shadow-sm)}
.nav-brand{display:flex;align-items:center;gap:8px;font-weight:600;font-size:14px;color:var(--text-hi);padding-right:6px}
.nav-brand .dot{width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 0 3px var(--accent-soft)}
.nav-sep{width:1px;height:18px;background:var(--line);margin:0 4px}
.nav-links{display:flex;gap:2px}
.nav-link{color:var(--text-soft);font-size:13.5px;font-weight:500;padding:7px 12px;border-radius:99px;transition:color .15s,background .15s}
.nav-link:hover{color:var(--text-hi);background:var(--panel-2)}
.nav-link.active{color:var(--accent)}
@media(max-width:680px){.nav-links,.nav-sep{display:none}}
.nav-tg{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;color:var(--text-soft);background:var(--panel-2);transition:all .2s}
.nav-tg:hover{color:var(--text-hi)}
.nav-tg svg{width:15px;height:15px}

/* ════════ HERO ════════ */
.hero{padding:130px 0 56px;text-align:center}
@media(max-width:680px){.hero{padding:104px 0 40px}}
.hero-av{width:84px;height:84px;border-radius:50%;object-fit:cover;margin:0 auto 22px;border:3px solid var(--panel);
  box-shadow:var(--shadow-sm);cursor:pointer;transition:transform .3s}
.hero-av:hover{transform:scale(1.06)}
.hero h1{font-size:clamp(2.6rem,8vw,4.6rem);line-height:1;color:var(--text-hi);margin-bottom:20px;font-weight:400}
.hero h1 b{font-weight:600}
.hero-desc{font-size:clamp(1.05rem,2.4vw,1.35rem);color:var(--text);max-width:600px;margin:0 auto 14px;line-height:1.5}
.hero-desc b{color:var(--text-hi);font-weight:600}
.hero-loc{display:inline-flex;align-items:center;gap:7px;font-size:13.5px;color:var(--text-soft);margin-bottom:26px}
.hero-loc i{color:var(--accent)}
.pills{display:flex;flex-wrap:wrap;justify-content:center;gap:8px;max-width:600px;margin:0 auto 30px}
.pill{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:500;color:var(--text);
  padding:7px 14px;border-radius:99px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow-sm)}
.pill i{color:var(--accent);font-size:11px}
.hero-cta{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:99px;font-size:14px;font-weight:600;transition:all .2s}
.btn-dark{background:var(--ink);color:var(--on-ink)}
.btn-dark:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
.btn-ghost{background:var(--panel);color:var(--text-hi);border:1px solid var(--line-2)}
.btn-ghost:hover{transform:translateY(-2px);border-color:var(--text-soft)}

/* ════════ SECTION ════════ */
section{padding:60px 0;scroll-margin-top:90px}
@media(max-width:680px){section{padding:44px 0}}
.sec-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:30px;flex-wrap:wrap}
.sec-eyebrow{font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:8px}
.sec-title{font-size:clamp(1.7rem,4vw,2.4rem);font-weight:400;color:var(--text-hi);line-height:1.1}
.sec-link{font-size:13.5px;font-weight:500;color:var(--text-soft);display:inline-flex;align-items:center;gap:6px;transition:color .15s}
.sec-link:hover{color:var(--accent)}

/* ════════ PROJECTS ════════ */
.proj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.pcard{display:flex;flex-direction:column;padding:22px;border-radius:20px;background:var(--panel);
  border:1px solid var(--line);box-shadow:var(--shadow-sm);transition:all .25s;position:relative;min-height:188px}
.pcard:hover{transform:translateY(-4px);box-shadow:var(--shadow);border-color:var(--line-2)}
.pcard-ic{width:46px;height:46px;border-radius:13px;background:var(--accent-soft);color:var(--accent);
  display:grid;place-items:center;font-size:19px;margin-bottom:auto}
.pcard-new{position:absolute;top:22px;right:22px;font-size:9.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;
  padding:3px 9px;border-radius:99px;background:var(--accent-soft);color:var(--accent);border:1px solid var(--accent-line)}
.pcard-title{font-size:17px;font-weight:600;color:var(--text-hi);margin:18px 0 6px;display:flex;align-items:center;gap:8px}
.pcard-title i{font-size:12px;color:var(--text-mute);transition:transform .2s,color .2s}
.pcard:hover .pcard-title i{color:var(--accent);transform:translate(3px,-3px)}
.pcard-desc{font-size:13.5px;color:var(--text-soft);line-height:1.6;margin-bottom:14px}
.pcard-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:auto}
.ptag{font-size:11px;font-weight:500;color:var(--text-soft);padding:4px 10px;border-radius:99px;background:var(--panel-2);border:1px solid var(--line)}

/* ════════ EXPERIENCE ════════ */
.tl{position:relative;padding-left:26px}
.tl::before{content:'';position:absolute;left:4px;top:6px;bottom:6px;width:1px;background:var(--line-2)}
.tl-item{position:relative;padding-bottom:30px}
.tl-item:last-child{padding-bottom:0}
.tl-item::before{content:'';position:absolute;left:-26px;top:5px;width:9px;height:9px;border-radius:50%;
  background:var(--bg);border:1.5px solid var(--line-2);transition:all .25s}
.tl-item.on::before{background:var(--accent);border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-soft)}
.tl-top{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:baseline;margin-bottom:3px}
.tl-role{font-size:16px;font-weight:600;color:var(--text-hi)}
.tl-year{font-size:12.5px;color:var(--text-soft);white-space:nowrap;font-weight:500}
.tl-co{font-size:14px;color:var(--accent);font-weight:500;margin-bottom:10px}
.tl-desc{font-size:14px;color:var(--text);line-height:1.7}
.tl-desc ul{list-style:none;display:flex;flex-direction:column;gap:5px}
.tl-desc li{position:relative;padding-left:16px}
.tl-desc li::before{content:'';position:absolute;left:0;top:11px;width:6px;height:1px;background:var(--accent)}
details.tg>summary{list-style:none;cursor:pointer;font-size:13px;color:var(--text-soft);display:inline-flex;align-items:center;gap:6px;padding:3px 0;user-select:none;transition:color .15s}
details.tg>summary::-webkit-details-marker{display:none}
details.tg>summary:hover{color:var(--text-hi)}
details.tg>summary::after{content:'+';font-size:15px;transition:transform .2s}
details.tg[open]>summary::after{transform:rotate(45deg)}
details.tg[open]>summary{margin-bottom:8px}

/* ════════ TOOLS ════════ */
.tools{display:grid;grid-template-columns:repeat(auto-fill,minmax(108px,1fr));gap:12px}
.tool{display:flex;flex-direction:column;align-items:center;gap:10px;padding:22px 12px;border-radius:18px;
  background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow-sm);transition:all .25s}
.tool:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
.tool img{width:34px;height:34px;object-fit:contain}
.tool .fa-fallback{font-size:30px;color:var(--accent)}
[data-theme="dark"] .tool img.invert{filter:invert(1)}
.tool span{font-size:12px;font-weight:500;color:var(--text-soft)}

/* ════════ ABOUT + STATS ════════ */
.about-card{padding:clamp(28px,5vw,48px);border-radius:24px;background:var(--panel);border:1px solid var(--line);box-shadow:var(--shadow-sm)}
.about-card .eyebrow{font-size:12px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--accent);margin-bottom:14px}
.about-card h2{font-size:clamp(1.6rem,4vw,2.3rem);font-weight:400;color:var(--text-hi);line-height:1.15;margin-bottom:16px;max-width:620px}
.about-card p{font-size:15.5px;color:var(--text);line-height:1.75;max-width:600px;margin-bottom:28px}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
@media(max-width:560px){.stats{grid-template-columns:1fr;gap:0}.stat{padding:16px 0;border-bottom:1px solid var(--line)}.stat:last-child{border-bottom:0}}
.stat{padding-top:20px;border-top:2px solid var(--ink)}
@media(max-width:560px){.stat{border-top:0}}
.stat-num{font-size:clamp(1.8rem,5vw,2.6rem);font-weight:600;color:var(--text-hi);line-height:1}
.stat-num span{color:var(--accent)}
.stat-label{font-size:13px;color:var(--text-soft);margin-top:6px}

/* ════════ CLIENTS ════════ */
.cl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.cl{display:flex;align-items:center;gap:13px;padding:15px 17px;border-radius:16px;background:var(--panel);
  border:1px solid var(--line);box-shadow:var(--shadow-sm);transition:all .22s}
.cl:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
a.cl:hover{border-color:var(--accent-line)}
.cl-ic{width:38px;height:38px;border-radius:11px;background:var(--accent-soft);display:grid;place-items:center;color:var(--accent);font-size:15px;flex-shrink:0}
.cl-name{font-size:14px;font-weight:600;color:var(--text-hi);line-height:1.3}
.cl-loc{font-size:12px;color:var(--text-soft);margin-top:1px}

/* ════════ CONTACT ════════ */
.contact{padding:clamp(40px,7vw,72px) 32px;border-radius:28px;background:var(--ink);color:var(--on-ink);text-align:center;position:relative;overflow:hidden}
.contact h2{font-size:clamp(2rem,5vw,3rem);font-weight:400;margin-bottom:14px;line-height:1.1}
.contact p{font-size:15.5px;opacity:.7;max-width:440px;margin:0 auto 28px;line-height:1.6}
.contact-cta{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
.btn-on-ink{background:var(--on-ink);color:var(--ink)}
.btn-on-ink:hover{transform:translateY(-2px);opacity:.92}
.btn-out-ink{border:1px solid color-mix(in srgb,var(--on-ink) 30%,transparent);color:var(--on-ink)}
.btn-out-ink:hover{transform:translateY(-2px);background:color-mix(in srgb,var(--on-ink) 10%,transparent)}

/* ════════ FOOTER ════════ */
footer{padding:48px 0 40px;border-top:1px solid var(--line);margin-top:60px}
.foot-in{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap}
.foot-txt{font-size:13px;color:var(--text-soft)}
.foot-soc{display:flex;gap:8px}
.foot-soc a{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:var(--panel);
  border:1px solid var(--line);color:var(--text-soft);box-shadow:var(--shadow-sm);transition:all .2s}
.foot-soc a:hover{color:var(--accent);border-color:var(--accent-line);transform:translateY(-2px)}

/* ════════ FAB ════════ */
.fab{position:fixed;right:18px;bottom:18px;z-index:40;display:flex;flex-direction:column;gap:10px}
.fab a,.fab button{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;box-shadow:var(--shadow);transition:all .25s}
.fab-wa{background:#25d366;color:#fff;font-size:21px}
.fab-wa:hover{transform:translateY(-3px) scale(1.05)}
.fab-top{background:var(--panel);border:1px solid var(--line-2);color:var(--text-hi);font-size:15px;opacity:0;pointer-events:none;transform:translateY(12px)}
.fab-top.show{opacity:1;pointer-events:auto;transform:none}
.fab-top:hover{color:var(--accent);border-color:var(--accent-line)}

/* ════════ REVEAL ════════ */
.rv{opacity:0;transform:translateY(20px);transition:opacity .7s cubic-bezier(.2,.7,.2,1),transform .7s cubic-bezier(.2,.7,.2,1)}
.rv.in{opacity:1;transform:none}
@media(prefers-reduced-motion:reduce){.rv{opacity:1;transform:none;transition:none}*{animation-duration:.01ms!important;transition-duration:.01ms!important}}

/* ════════ LIGHTBOX ════════ */
.plb{display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.85);align-items:center;justify-content:center;padding:24px;backdrop-filter:blur(8px)}
.plb.open{display:flex;animation:fin .25s ease}
.plb img{max-width:min(420px,90vw);max-height:82vh;border-radius:24px;box-shadow:0 30px 80px rgba(0,0,0,.6);animation:zin .3s ease}
.plb-x{position:absolute;top:22px;right:24px;font-size:26px;color:#fff;opacity:.7;width:42px;height:42px;border-radius:50%;display:grid;place-items:center;transition:all .15s}
.plb-x:hover{opacity:1;background:rgba(255,255,255,.1)}
@keyframes fin{from{opacity:0}to{opacity:1}}
@keyframes zin{from{transform:scale(.92);opacity:0}to{transform:scale(1);opacity:1}}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <div class="nav-pill">
    <a href="#top" class="nav-brand"><span class="dot"></span> <?= esc(explode(' ', $nama)[0]) ?></a>
    <span class="nav-sep"></span>
    <div class="nav-links">
      <a href="#work" class="nav-link">Work</a>
      <a href="#experience" class="nav-link">Experience</a>
      <a href="#tools" class="nav-link">Tools</a>
      <a href="#about" class="nav-link">About</a>
    </div>
    <span class="nav-sep"></span>
    <button class="nav-tg" onclick="toggleTheme()" aria-label="Ganti tema">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="th-ic"></svg>
    </button>
  </div>
</nav>

<div id="top"></div>

<!-- HERO -->
<header class="wrap hero">
  <img class="hero-av" id="hero-av" src="<?= esc($foto_raw) ?>" alt="<?= esc($nama) ?>" width="84" height="84" loading="eager">
  <h1 class="serif">Halo, saya <b><?= esc($nama) ?></b></h1>
  <p class="hero-desc"><b>IT Support &amp; Full-stack Developer</b> yang bangun sistem nyata, bukan cuma rencana.</p>
  <div class="hero-loc"><i class="fas fa-circle" style="font-size:7px"></i> <?= esc($status) ?></div>
  <div class="pills">
    <?php foreach ($hero_pills as $p): ?><span class="pill"><i class="fas fa-check"></i><?= esc($p) ?></span><?php endforeach; ?>
  </div>
  <div class="hero-cta">
    <a href="#contact" class="btn btn-dark"><i class="fas fa-paper-plane"></i> Diskusi Proyek</a>
    <a href="<?= esc($github) ?>" target="_blank" rel="noopener" class="btn btn-ghost"><i class="fab fa-github"></i> GitHub</a>
  </div>
</header>

<!-- PROJECTS -->
<?php if (!empty($projects)): ?>
<section id="work" class="wrap">
  <div class="sec-head rv">
    <div>
      <div class="sec-eyebrow">Projects</div>
      <h2 class="sec-title serif">Karya pilihan</h2>
    </div>
    <a href="/slws/" class="sec-link">Lihat galeri foto <i class="fas fa-arrow-right"></i></a>
  </div>
  <div class="proj-grid">
    <?php foreach ($projects as $p):
      $is_new = isset($p['days_ago']) && $p['days_ago'] !== null && (int)$p['days_ago'] <= 30;
      $ext = !empty($p['link_url']) && preg_match('#^https?://#i', $p['link_url']);
      $tag = !empty($p['link_url']) ? 'a' : 'div';
      $attr = !empty($p['link_url']) ? 'href="'.esc($p['link_url']).'"'.($ext?' target="_blank" rel="noopener"':'') : '';
      // Tag heuristik dari deskripsi/judul
      $auto_tags = [];
      $hay = strtolower($p['title'].' '.$p['description']);
      foreach (['Networking'=>'jaring|network|mikrotik','Web'=>'web|aplikasi|sistem|ticket|inventory','Monitoring'=>'monitor|noc|log','Fotografi'=>'foto|visual|photo'] as $lbl=>$rx) {
        if (preg_match("/$rx/i", $hay)) $auto_tags[] = $lbl;
      }
      if (empty($auto_tags)) $auto_tags[] = 'Project';
    ?>
      <<?= $tag ?> class="pcard rv" <?= $attr ?>>
        <div class="pcard-ic"><i class="<?= esc($p['icon_class']) ?>"></i></div>
        <?php if ($is_new): ?><span class="pcard-new">New</span><?php endif; ?>
        <div class="pcard-title"><?= esc($p['title']) ?><?php if (!empty($p['link_url'])): ?><i class="fas fa-arrow-up-right-from-square"></i><?php endif; ?></div>
        <div class="pcard-desc"><?= esc($p['description']) ?></div>
        <div class="pcard-tags">
          <?php foreach (array_slice($auto_tags,0,3) as $t): ?><span class="ptag"><?= esc($t) ?></span><?php endforeach; ?>
        </div>
      </<?= $tag ?>>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- EXPERIENCE -->
<?php if (!empty($experiences)): ?>
<section id="experience" class="wrap">
  <div class="sec-head rv">
    <div>
      <div class="sec-eyebrow">Journey</div>
      <h2 class="sec-title serif">Pengalaman</h2>
    </div>
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

<!-- TOOLS -->
<section id="tools" class="wrap">
  <div class="sec-head rv">
    <div>
      <div class="sec-eyebrow">Stack</div>
      <h2 class="sec-title serif">Tools yang saya pakai</h2>
    </div>
  </div>
  <div class="tools rv">
    <?php foreach ($tools as $t): ?>
      <div class="tool">
        <?php if (!empty($t['fa'])): ?>
          <i class="<?= esc($t['fa']) ?> fa-fallback"></i>
        <?php else: ?>
          <img src="<?= esc($t['src']) ?>" alt="<?= esc($t['name']) ?>" class="<?= !empty($t['invert'])?'invert':'' ?>" loading="lazy" onerror="this.outerHTML='<i class=\'fas fa-cube fa-fallback\'></i>'">
        <?php endif; ?>
        <span><?= esc($t['name']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ABOUT + STATS -->
<section id="about" class="wrap">
  <div class="about-card rv">
    <div class="eyebrow">About Me</div>
    <h2 class="serif">Teknologi adalah cara saya menyelesaikan masalah nyata.</h2>
    <p>Saya seorang IT Support &amp; Full-stack Developer multidisiplin yang menikmati membangun infrastruktur dan aplikasi yang fungsional. Dengan perhatian pada detail, saya bantu instansi dan bisnis terhubung dengan teknologinya secara andal.</p>
    <div class="stats">
      <div class="stat"><div class="stat-num"><?= $years_exp ?><span>+</span></div><div class="stat-label">Tahun pengalaman</div></div>
      <div class="stat"><div class="stat-num"><?= $jml_klien ?: $jml_proj ?><span>+</span></div><div class="stat-label"><?= $jml_klien ? 'Klien &amp; instansi' : 'Project dikerjakan' ?></div></div>
      <div class="stat"><div class="stat-num serif" style="font-size:clamp(1.3rem,3.5vw,1.8rem)">Open</div><div class="stat-label">Untuk freelance &amp; kolaborasi</div></div>
    </div>
  </div>
</section>

<!-- CLIENTS -->
<?php if (!empty($clients)): ?>
<section id="clients" class="wrap">
  <div class="sec-head rv">
    <div>
      <div class="sec-eyebrow">Trusted by</div>
      <h2 class="sec-title serif">Klien &amp; instansi</h2>
    </div>
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
    <h2 class="serif">Punya ide? Mari wujudkan.</h2>
    <p>Kalau kamu punya kebutuhan IT, jaringan, atau aplikasi web — tinggal hubungi. Saya bantu sampai jadi.</p>
    <div class="contact-cta">
      <a href="mailto:<?= esc($email) ?>" class="btn btn-on-ink"><i class="fas fa-envelope"></i> Email saya</a>
      <?php if ($whatsapp): ?><a href="https://wa.me/<?= esc($whatsapp) ?>" target="_blank" rel="noopener" class="btn btn-out-ink"><i class="fab fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
    </div>
  </div>
</section>

<footer class="wrap">
  <div class="foot-in">
    <div class="foot-txt">© <?= date('Y') ?> <?= esc($nama) ?>. All rights reserved.</div>
    <div class="foot-soc">
      <a href="<?= esc($github) ?>" target="_blank" rel="noopener" aria-label="GitHub"><i class="fab fa-github"></i></a>
      <a href="<?= esc($linkedin) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      <a href="mailto:<?= esc($email) ?>" aria-label="Email"><i class="fas fa-envelope"></i></a>
      <a href="/slws/" aria-label="Selawas Visual"><i class="fas fa-camera"></i></a>
    </div>
  </div>
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
const TI={dark:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M6.3 17.7l-1.4 1.4M19.1 4.9l-1.4 1.4"/>',light:'<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>'};
function setTheme(t){document.documentElement.setAttribute('data-theme',t);document.getElementById('th-ic').innerHTML=t==='dark'?TI.dark:TI.light;try{localStorage.setItem('rsby-theme',t)}catch(e){}}
function toggleTheme(){setTheme((document.documentElement.getAttribute('data-theme')||'light')==='dark'?'light':'dark')}
setTheme(document.documentElement.getAttribute('data-theme')||'light');

const fabTop=document.getElementById('fab-top');
addEventListener('scroll',()=>fabTop.classList.toggle('show',scrollY>500),{passive:true});

const links=document.querySelectorAll('.nav-link');
const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){const id=e.target.id;links.forEach(l=>l.classList.toggle('active',l.getAttribute('href')==='#'+id))}}),{rootMargin:'-45% 0px -50% 0px'});
document.querySelectorAll('section[id]').forEach(s=>io.observe(s));

const ro=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');ro.unobserve(e.target)}}),{rootMargin:'0px 0px -40px 0px',threshold:.08});
document.querySelectorAll('.rv').forEach(el=>ro.observe(el));

document.getElementById('hero-av').onclick=()=>{document.getElementById('plb').classList.add('open');document.body.style.overflow='hidden'};
function closePlb(){document.getElementById('plb').classList.remove('open');document.body.style.overflow=''}
addEventListener('keydown',e=>{if(e.key==='Escape')closePlb()});
</script>
</body>
</html>
