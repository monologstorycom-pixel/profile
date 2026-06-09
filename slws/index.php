<?php
require '../admin/koneksi.php';

/* ── DATA RINGAN: kategori + cover (1 foto/kategori). Foto lengkap di-fetch saat folder diklik ── */
$categories_db = $pdo->query("SELECT * FROM slws_categories ORDER BY id")->fetchAll();

$data_kategori = [];
foreach ($categories_db as $kat) {
    $stmt_cover = $pdo->prepare("SELECT image_path FROM slws_photos WHERE category_id = ? ORDER BY id DESC LIMIT 1");
    $stmt_cover->execute([$kat['id']]);
    $cover_path = $stmt_cover->fetchColumn();

    $stmt_cnt = $pdo->prepare("SELECT COUNT(*) FROM slws_photos WHERE category_id = ?");
    $stmt_cnt->execute([$kat['id']]);
    $total = (int)$stmt_cnt->fetchColumn();

    $data_kategori[] = [
        'id'    => $kat['id'],
        'name'  => $kat['name'],
        'icon'  => $kat['icon'],
        'cover' => $cover_path ? '../' . $cover_path : '',
        'total' => $total,
    ];
}
$json_categories = json_encode($data_kategori);

$videos = $pdo->query("SELECT * FROM videos ORDER BY id DESC")->fetchAll();
$total_foto_all = array_sum(array_column($data_kategori, 'total'));

function getYouTubeEmbed($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $m);
    return isset($m[1]) ? "https://www.youtube.com/embed/" . $m[1] . "?rel=0" : htmlspecialchars($url);
}
function getYouTubeThumb($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $m);
    return isset($m[1]) ? "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg" : '';
}
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SELAWAS VISUAL — Portfolio Fotografi &amp; Video</title>
<meta name="description" content="SELAWAS VISUAL — Studio fotografi independen Pekalongan. Wedding, Couple Session, Portrait, Video.">
<link rel="icon" href="../favicon.svg" type="image/svg+xml">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap"></noscript>
<link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

<script>(function(){try{var t=localStorage.getItem('rsby-theme')||'dark';document.documentElement.setAttribute('data-theme',t)}catch(e){}})()</script>

<style>
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
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);
  font-size:15px;line-height:1.7;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;
  min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s;letter-spacing:-.01em}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}
button{font:inherit;color:inherit;background:none;border:none;cursor:pointer}
::selection{background:var(--accent);color:#0a0a0b}
::-webkit-scrollbar{width:9px}::-webkit-scrollbar-thumb{background:var(--line-2);border-radius:9px;border:2px solid var(--bg)}
h1,h2,.font-display{font-family:'Space Grotesk','Inter',sans-serif;letter-spacing:-.02em}

.wrap{max-width:920px;margin:0 auto;padding:0 24px}

/* NAV */
.nav{position:sticky;top:0;z-index:50;background:color-mix(in srgb,var(--bg) 80%,transparent);
  backdrop-filter:blur(16px) saturate(150%);-webkit-backdrop-filter:blur(16px) saturate(150%);
  border-bottom:1px solid transparent;transition:border-color .3s}
.nav.scrolled{border-color:var(--line)}
.nav-in{max-width:920px;margin:0 auto;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;gap:14px}
.back{display:inline-flex;align-items:center;gap:8px;font-size:13.5px;font-weight:500;color:var(--text-soft);
  padding:7px 12px;border-radius:9px;border:1px solid var(--line);transition:all .18s}
.back:hover{color:var(--text-hi);border-color:var(--line-2)}
.brand{display:flex;align-items:center;gap:9px;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;color:var(--text-hi)}
.brand .dot{width:7px;height:7px;border-radius:50%;background:var(--accent)}
.icon-btn{width:36px;height:36px;border-radius:9px;display:grid;place-items:center;color:var(--text-soft);border:1px solid var(--line);transition:all .2s}
.icon-btn:hover{color:var(--text-hi);border-color:var(--line-2)}
.icon-btn svg{width:16px;height:16px}

/* PAGES */
.page{display:none}
.page.active{display:block;animation:fu .35s ease both}
@keyframes fu{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

/* HERO */
.hero{padding:64px 0 36px;text-align:center}
@media(max-width:560px){.hero{padding:44px 0 24px}}
.hero-tag{font-family:'Space Grotesk',monospace;font-size:12px;letter-spacing:.14em;color:var(--accent);
  text-transform:uppercase;margin-bottom:16px;display:inline-block}
.hero h1{font-size:clamp(2rem,6vw,3rem);font-weight:700;color:var(--text-hi);line-height:1.08;margin-bottom:12px}
.hero-sub{font-size:15px;color:var(--text-soft);margin-bottom:24px}
.meta{display:flex;justify-content:center;gap:8px;flex-wrap:wrap}
.meta-item{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text-soft);
  padding:6px 13px;border-radius:99px;background:var(--card);border:1px solid var(--line);font-weight:500}
.meta-item i{color:var(--accent);font-size:11px}

/* SECTION */
.sec{padding:8px 0 56px}
.sec-head{display:flex;align-items:center;gap:12px;margin-bottom:24px}
.sec-label{font-family:'Space Grotesk',monospace;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--text-soft);font-weight:500}
.sec-line{flex:1;height:1px;background:var(--line)}

/* FOLDER GRID */
.folders{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
@media(max-width:560px){.folders{grid-template-columns:repeat(2,1fr);gap:11px}}
.folder{position:relative;border:1px solid var(--line);border-radius:16px;overflow:hidden;cursor:pointer;
  background:var(--card);transition:border-color .25s,transform .25s;aspect-ratio:4/5}
.folder:hover{border-color:var(--accent-line);transform:translateY(-4px)}
.folder-cover{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
.folder:hover .folder-cover{transform:scale(1.06)}
.folder-ph{width:100%;height:100%;background:var(--bg-2);display:grid;place-items:center;font-size:38px;color:var(--line-2)}
.folder-grad{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.78) 0%,rgba(0,0,0,.1) 45%,transparent 100%)}
.folder-info{position:absolute;left:0;right:0;bottom:0;padding:16px}
.folder-name{font-family:'Space Grotesk',sans-serif;font-size:15px;font-weight:600;color:#fff;line-height:1.2;margin-bottom:3px}
.folder-count{font-size:11.5px;color:rgba(255,255,255,.7);display:inline-flex;align-items:center;gap:5px}
.folder-badge{position:absolute;top:12px;right:12px;width:30px;height:30px;border-radius:9px;
  background:rgba(0,0,0,.4);backdrop-filter:blur(6px);display:grid;place-items:center;color:#fff;font-size:12px}

/* VIDEO GRID */
.divider{height:1px;background:var(--line);margin:8px 0 48px}
.vid-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px}
@media(max-width:560px){.vid-grid{grid-template-columns:1fr}}
.vid{border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--card);transition:all .22s}
.vid:hover{border-color:var(--line-2);transform:translateY(-2px);box-shadow:var(--shadow)}
.vid-thumb{position:relative;aspect-ratio:16/9;background:#000;cursor:pointer;overflow:hidden}
.vid-thumb img{width:100%;height:100%;object-fit:cover;opacity:.82;transition:opacity .25s,transform .4s}
.vid-thumb:hover img{opacity:1;transform:scale(1.05)}
.vid-play{position:absolute;inset:0;display:grid;place-items:center;pointer-events:none}
.vid-play span{width:52px;height:52px;border-radius:50%;background:rgba(0,0,0,.45);backdrop-filter:blur(6px);
  border:1.5px solid rgba(255,255,255,.5);display:grid;place-items:center;color:#fff;font-size:16px;transition:all .25s}
.vid-thumb:hover .vid-play span{background:var(--accent);border-color:var(--accent);color:#0a0a0b;transform:scale(1.1)}
.vid-iframe{display:none;aspect-ratio:16/9;background:#000}
.vid-iframe.active{display:block}
.vid-iframe iframe{width:100%;height:100%;border:0;display:block}
.vid-thumb.hidden{display:none}
.vid-body{padding:13px 16px}
.vid-title{font-size:14px;font-weight:600;color:var(--text-hi);line-height:1.4}
.vid-desc{font-size:12.5px;color:var(--text-soft);margin-top:3px}

/* GALLERY PAGE */
.gal-head{padding:36px 0 24px}
.gal-top{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap}
.gal-title{font-family:'Space Grotesk',sans-serif;font-size:clamp(1.5rem,4vw,2rem);font-weight:700;color:var(--text-hi)}
.gal-sub{font-size:13px;color:var(--text-soft);margin-top:2px}

/* MASONRY */
.masonry{columns:3 200px;column-gap:12px}
@media(max-width:560px){.masonry{columns:2 150px;column-gap:10px}}
.m-item{break-inside:avoid;margin-bottom:12px;border-radius:12px;overflow:hidden;border:1px solid var(--line);
  cursor:pointer;position:relative;background:var(--card);transition:border-color .2s,transform .2s}
.m-item:hover{border-color:var(--accent-line);transform:scale(1.01)}
.m-item img{width:100%;display:block;transition:transform .35s ease}
.m-item:hover img{transform:scale(1.05)}
.m-ov{position:absolute;inset:0;opacity:0;display:grid;place-items:center;transition:all .25s;
  font-size:18px;color:#fff;background:rgba(0,0,0,0)}
.m-item:hover .m-ov{opacity:1;background:rgba(0,0,0,.3)}

/* LOADING */
.loading{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:70px 20px;gap:14px;color:var(--text-soft)}
.spin{width:30px;height:30px;border:2px solid var(--line-2);border-top-color:var(--accent);border-radius:50%;animation:sp .7s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}

/* EMPTY */
.empty{text-align:center;padding:70px 20px;color:var(--text-soft)}
.empty i{font-size:38px;margin-bottom:14px;display:block;opacity:.35}
.empty p{font-size:14px}

/* LIGHTBOX */
.lb{display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.94);align-items:center;justify-content:center}
.lb.open{display:flex;animation:fin .2s ease}
@keyframes fin{from{opacity:0}to{opacity:1}}
.lb-wrap{position:relative;max-width:min(92vw,980px);max-height:90vh;animation:lu .25s ease;user-select:none}
@keyframes lu{from{transform:scale(.95);opacity:0}to{transform:scale(1);opacity:1}}
.lb-img{max-width:100%;max-height:90vh;object-fit:contain;border-radius:10px;display:block;pointer-events:none;transition:opacity .15s}
.lb-img.loading{opacity:.35}
.lb-x{position:absolute;top:-44px;right:0;color:#fff;font-size:28px;opacity:.7;padding:6px;transition:opacity .15s}
.lb-x:hover{opacity:1}
.lb-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);color:#fff;
  padding:12px 15px;font-size:16px;border-radius:10px;transition:background .2s;backdrop-filter:blur(4px)}
.lb-nav:hover{background:rgba(255,255,255,.22)}
.lb-prev{left:-56px}.lb-next{right:-56px}
@media(max-width:760px){.lb-prev{left:8px}.lb-next{right:8px}.lb-x{top:8px;right:8px}}
.lb-count{position:absolute;bottom:-34px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.55);
  font-size:11px;font-family:'Space Grotesk',monospace;white-space:nowrap}
.lb-hint{display:none;position:absolute;bottom:-56px;left:50%;transform:translateX(-50%);font-size:10px;color:rgba(255,255,255,.3);font-family:'Space Grotesk',monospace}
@media(hover:none){.lb-hint{display:block}}

/* FOOTER */
footer{text-align:center;padding:32px 20px 36px;border-top:1px solid var(--line)}
.foot-txt{font-size:12.5px;color:var(--text-mute);font-family:'Space Grotesk',monospace}
.foot-txt a{color:var(--accent)}

/* REVEAL stagger */
.folder{opacity:0;transform:translateY(16px);animation:rin .5s ease forwards}
.folder:nth-child(1){animation-delay:.04s}.folder:nth-child(2){animation-delay:.08s}
.folder:nth-child(3){animation-delay:.12s}.folder:nth-child(4){animation-delay:.16s}
.folder:nth-child(5){animation-delay:.2s}.folder:nth-child(6){animation-delay:.24s}
.folder:nth-child(n+7){animation-delay:.28s}
@keyframes rin{to{opacity:1;transform:none}}
@media(prefers-reduced-motion:reduce){.folder{opacity:1;transform:none;animation:none}*{animation-duration:.01ms!important}}
</style>
</head>
<body>

<nav class="nav" id="nav">
  <div class="nav-in">
    <a href="../index.php" class="back"><i class="fas fa-arrow-left"></i> Portfolio</a>
    <div class="brand"><span class="dot"></span> SELAWAS VISUAL</div>
    <button class="icon-btn" onclick="toggleTheme()" aria-label="Ganti tema">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="th-ic"></svg>
    </button>
  </div>
</nav>

<!-- ═══ HOME ═══ -->
<div class="page active" id="page-home">
  <header class="wrap hero">
    <span class="hero-tag">Pekalongan · 2017—2024</span>
    <h1>Portfolio Fotografi</h1>
    <p class="hero-sub">Studio fotografi independen — momen, cerita, dan visual yang berkesan.</p>
    <div class="meta">
      <span class="meta-item"><i class="fas fa-folder"></i> <?= count($data_kategori) ?> kategori</span>
      <span class="meta-item"><i class="fas fa-images"></i> <?= $total_foto_all ?> foto</span>
      <span class="meta-item"><i class="fas fa-video"></i> <?= count($videos) ?> video</span>
      <span class="meta-item"><i class="fas fa-location-dot"></i> Pekalongan</span>
    </div>
  </header>

  <div class="wrap sec">
    <div class="sec-head">
      <span class="sec-label">Galeri Kategori</span>
      <div class="sec-line"></div>
    </div>
    <div class="folders" id="folders"></div>
  </div>

  <?php if (!empty($videos)): ?>
  <div class="wrap">
    <div class="divider"></div>
  </div>
  <div class="wrap sec" style="padding-bottom:32px">
    <div class="sec-head">
      <span class="sec-label">Video Portfolio</span>
      <div class="sec-line"></div>
    </div>
    <div class="vid-grid">
      <?php foreach ($videos as $v):
        $thumb = getYouTubeThumb($v['video_url']); $embed = getYouTubeEmbed($v['video_url']); $vid = 'v'.$v['id'];
      ?>
      <div class="vid">
        <div class="vid-thumb" id="th-<?= $vid ?>" onclick="playVid('<?= $vid ?>','<?= esc($embed) ?>')" role="button" aria-label="Putar <?= esc($v['title']) ?>">
          <?php if ($thumb): ?><img src="<?= esc($thumb) ?>" alt="<?= esc($v['title']) ?>" loading="lazy" decoding="async">
          <?php else: ?><div style="width:100%;height:100%;background:#111;display:grid;place-items:center;color:#444;font-size:30px"><i class="fas fa-film"></i></div><?php endif; ?>
          <div class="vid-play"><span><i class="fas fa-play"></i></span></div>
        </div>
        <div class="vid-iframe" id="if-<?= $vid ?>"></div>
        <div class="vid-body">
          <div class="vid-title"><?= esc($v['title']) ?></div>
          <?php if (!empty($v['description'])): ?><div class="vid-desc"><?= esc($v['description']) ?></div><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <footer><p class="foot-txt">© <?= date('Y') ?> SELAWAS VISUAL · <a href="mailto:rizqisubagyo07@gmail.com">rizqisubagyo07@gmail.com</a></p></footer>
</div>

<!-- ═══ GALLERY ═══ -->
<div class="page" id="page-gallery">
  <div class="wrap gal-head">
    <div class="gal-top">
      <div>
        <div class="gal-title" id="gal-title">—</div>
        <div class="gal-sub" id="gal-sub">memuat…</div>
      </div>
      <button class="back" onclick="goHome()"><i class="fas fa-arrow-left"></i> Semua Kategori</button>
    </div>
  </div>
  <div class="wrap" style="padding-bottom:56px">
    <div class="loading" id="loading"><div class="spin"></div><span style="font-size:12.5px;font-family:'Space Grotesk',monospace">Memuat foto…</span></div>
    <div class="masonry" id="masonry" style="display:none"></div>
  </div>
  <footer><p class="foot-txt">© <?= date('Y') ?> SELAWAS VISUAL · <a href="mailto:rizqisubagyo07@gmail.com">rizqisubagyo07@gmail.com</a></p></footer>
</div>

<!-- ═══ LIGHTBOX ═══ -->
<div class="lb" id="lb" onclick="lbOut(event)">
  <div class="lb-wrap">
    <button class="lb-x" onclick="closeLb()">×</button>
    <img class="lb-img" id="lb-img" src="" alt="">
    <button class="lb-nav lb-prev" onclick="lbNav(-1)"><i class="fas fa-chevron-left"></i></button>
    <button class="lb-nav lb-next" onclick="lbNav(1)"><i class="fas fa-chevron-right"></i></button>
    <div class="lb-count" id="lb-count">1 / 1</div>
    <div class="lb-hint">← geser untuk navigasi →</div>
  </div>
</div>

<script>
const categories = <?= $json_categories ?>;
const photoCache = {};
let currentPhotos = [], currentLbIdx = 0;

/* THEME */
const TI={dark:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M6.3 17.7l-1.4 1.4M19.1 4.9l-1.4 1.4"/>',light:'<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>'};
function setTheme(t){document.documentElement.setAttribute('data-theme',t);document.getElementById('th-ic').innerHTML=t==='dark'?TI.dark:TI.light;try{localStorage.setItem('rsby-theme',t)}catch(e){}}
function toggleTheme(){setTheme((document.documentElement.getAttribute('data-theme')||'dark')==='dark'?'light':'dark')}
setTheme(document.documentElement.getAttribute('data-theme')||'dark');

/* NAV scroll */
const nav=document.getElementById('nav');
addEventListener('scroll',()=>nav.classList.toggle('scrolled',scrollY>4),{passive:true});

/* RENDER FOLDERS */
function renderHome(){
  const grid=document.getElementById('folders');grid.innerHTML='';
  if(!categories.length){grid.innerHTML='<div class="empty" style="grid-column:1/-1"><i class="fas fa-folder-open"></i><p>Belum ada kategori foto.</p></div>';return;}
  categories.forEach(cat=>{
    const card=document.createElement('div');card.className='folder';
    card.onclick=()=>openCategory(cat.id,cat.name);
    const ic=(cat.icon&&/^fa-[\w-]+$/.test(cat.icon))?cat.icon:'fa-folder';
    const cover=cat.cover?`<img class="folder-cover" src="${cat.cover}" alt="${cat.name}" loading="lazy" decoding="async">`:`<div class="folder-ph"><i class="fas ${ic}"></i></div>`;
    card.innerHTML=`${cover}<div class="folder-grad"></div><div class="folder-badge"><i class="fas ${ic}"></i></div>
      <div class="folder-info"><div class="folder-name">${cat.name}</div>
      <div class="folder-count"><i class="fas fa-image" style="font-size:9px"></i> ${cat.total} foto</div></div>`;
    grid.appendChild(card);
  });
}

/* OPEN CATEGORY */
async function openCategory(id,name){
  document.getElementById('gal-title').textContent=name;
  document.getElementById('gal-sub').textContent='memuat…';
  document.getElementById('loading').style.display='flex';
  document.getElementById('masonry').style.display='none';
  document.getElementById('masonry').innerHTML='';
  document.getElementById('page-home').classList.remove('active');
  document.getElementById('page-gallery').classList.add('active');
  scrollTo({top:0,behavior:'instant'});
  if(!photoCache[id]){
    try{const r=await fetch(`api_photos.php?cat=${encodeURIComponent(id)}`);const d=await r.json();photoCache[id]=d.ok?d.photos:[];}
    catch(e){photoCache[id]=[];}
  }
  const photos=photoCache[id];currentPhotos=photos;
  document.getElementById('gal-sub').textContent=`${photos.length} foto`;
  document.getElementById('loading').style.display='none';
  const grid=document.getElementById('masonry');grid.style.display='block';
  if(!photos.length){grid.innerHTML=`<div class="empty" style="column-span:all"><i class="fas fa-images"></i><p>Foto akan segera ditambahkan.</p></div>`;return;}
  photos.forEach((url,i)=>{
    const it=document.createElement('div');it.className='m-item';
    it.innerHTML=`<img src="${url}" alt="${name} ${i+1}" loading="lazy" decoding="async"><div class="m-ov"><i class="fas fa-expand"></i></div>`;
    it.onclick=()=>openLb(i);grid.appendChild(it);
  });
}

function goHome(){
  document.querySelectorAll('.vid-iframe.active').forEach(el=>{el.innerHTML='';el.classList.remove('active');const t=document.getElementById(el.id.replace('if-','th-'));if(t)t.classList.remove('hidden')});
  document.getElementById('page-gallery').classList.remove('active');
  document.getElementById('page-home').classList.add('active');
  scrollTo({top:0,behavior:'instant'});
}

/* VIDEO */
function playVid(id,embed){
  document.querySelectorAll('.vid-iframe.active').forEach(el=>{if(el.id!=='if-'+id){el.innerHTML='';el.classList.remove('active');const t=document.getElementById(el.id.replace('if-','th-'));if(t)t.classList.remove('hidden')}});
  const t=document.getElementById('th-'+id),w=document.getElementById('if-'+id);if(!w)return;
  t.classList.add('hidden');w.classList.add('active');
  w.innerHTML=`<iframe src="${embed}&autoplay=1" allow="autoplay;encrypted-media;fullscreen" allowfullscreen loading="lazy"></iframe>`;
}

/* LIGHTBOX */
function openLb(i){currentLbIdx=i;updateLb();document.getElementById('lb').classList.add('open');document.body.style.overflow='hidden';}
function updateLb(){const img=document.getElementById('lb-img');img.classList.add('loading');img.onload=()=>img.classList.remove('loading');img.src=currentPhotos[currentLbIdx];document.getElementById('lb-count').textContent=`${currentLbIdx+1} / ${currentPhotos.length}`;}
function lbNav(d){currentLbIdx=(currentLbIdx+d+currentPhotos.length)%currentPhotos.length;updateLb();}
function closeLb(){document.getElementById('lb').classList.remove('open');document.body.style.overflow='';}
function lbOut(e){if(e.target===document.getElementById('lb'))closeLb();}
addEventListener('keydown',e=>{if(!document.getElementById('lb').classList.contains('open'))return;if(e.key==='ArrowRight')lbNav(1);if(e.key==='ArrowLeft')lbNav(-1);if(e.key==='Escape')closeLb();});

/* SWIPE */
(function(){let sx=0,sy=0,drag=false;const TH=50,AX=30;const lb=document.getElementById('lb');
  lb.addEventListener('touchstart',e=>{if(e.touches.length!==1)return;sx=e.touches[0].clientX;sy=e.touches[0].clientY;drag=true;},{passive:true});
  lb.addEventListener('touchmove',e=>{if(!drag||e.touches.length!==1)return;const dx=Math.abs(e.touches[0].clientX-sx),dy=Math.abs(e.touches[0].clientY-sy);if(dx>dy&&dx>AX)e.preventDefault();},{passive:false});
  lb.addEventListener('touchend',e=>{if(!drag)return;drag=false;if(e.changedTouches.length!==1)return;const dx=e.changedTouches[0].clientX-sx,dy=Math.abs(e.changedTouches[0].clientY-sy);if(Math.abs(dx)<TH||dy>Math.abs(dx))return;dx<0?lbNav(1):lbNav(-1);},{passive:true});
})();

renderHome();
</script>
</body>
</html>
