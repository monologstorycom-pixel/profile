<?php
require '../admin/koneksi.php';

// ── Hanya ambil DATA RINGAN: kategori + cover (1 foto saja per kategori) ──
// Foto lengkap di-fetch JS saat user klik folder (lazy)
$stmt_cat = $pdo->query("SELECT * FROM slws_categories ORDER BY id");
$categories_db = $stmt_cat->fetchAll();

$data_kategori = [];
foreach ($categories_db as $kat) {
    // Hanya ambil 1 foto sebagai cover — BUKAN semua foto!
    $stmt_cover = $pdo->prepare(
        "SELECT image_path FROM slws_photos WHERE category_id = ? ORDER BY id DESC LIMIT 1"
    );
    $stmt_cover->execute([$kat['id']]);
    $cover_path = $stmt_cover->fetchColumn();

    // Hitung total foto (COUNT ringan)
    $stmt_cnt = $pdo->prepare("SELECT COUNT(*) FROM slws_photos WHERE category_id = ?");
    $stmt_cnt->execute([$kat['id']]);
    $total = (int)$stmt_cnt->fetchColumn();

    $data_kategori[] = [
        'id'    => $kat['id'],
        'name'  => $kat['name'],
        'icon'  => $kat['icon'],
        'cover' => $cover_path ? '../' . $cover_path : '',
        'total' => $total,
        // 'photos' tidak ada lagi — di-fetch on demand
    ];
}
$json_categories = json_encode($data_kategori);

// ── Video tetap di-load sekaligus (jumlah biasanya sedikit) ──
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
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SELAWAS VISUAL — Portfolio Fotografi & Video</title>
    <meta name="description" content="SELAWAS VISUAL — Studio fotografi profesional Pekalongan. Wedding, Prewedding, Portrait, Product, Event.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"></noscript>

    <!-- Theme sebelum render — cegah flash -->
    <script>(function(){try{var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t)}catch(e){}})()</script>

    <style>
        /* ── THEMES ── */
        [data-theme="dark"] {
            --bg:        #090b09; --surface: #0d100d;
            --card:      #101310; --card-h:  #131713;
            --border:    #1c241c; --border-s:#243024;
            --text:      #8bb880; --text-hi: #b4d4ac;
            --text-head: #cce8c4; --text-dim:#3d5a38;
            --accent:    #52a852; --accent-bg:rgba(82,168,82,0.08);
            --shadow:    0 2px 16px rgba(0,0,0,0.55);
        }
        [data-theme="light"] {
            --bg:        #f4f4f0; --surface: #ffffff;
            --card:      #ffffff; --card-h:  #fafaf8;
            --border:    #e5e5e0; --border-s:#d0d0c8;
            --text:      #404040; --text-hi: #1a1a1a;
            --text-head: #111111; --text-dim:#999990;
            --accent:    #1d6ed8; --accent-bg:rgba(29,110,216,0.07);
            --shadow:    0 2px 12px rgba(0,0,0,0.07);
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth}
        body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);min-height:100vh;font-size:14px;line-height:1.65;transition:background .25s,color .25s;overflow-x:hidden}
        ::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:var(--border-s);border-radius:3px}

        /* TOGGLE */
        .toggle{position:fixed;top:18px;right:18px;z-index:300;background:var(--card);border:1px solid var(--border);border-radius:99px;padding:6px 13px 6px 10px;display:flex;align-items:center;gap:6px;cursor:pointer;font-size:12px;font-weight:500;color:var(--text);box-shadow:var(--shadow);transition:border-color .2s,color .2s;font-family:'Inter',sans-serif}
        .toggle:hover{border-color:var(--border-s);color:var(--text-hi)}

        /* PAGES */
        .page{display:none}
        .page.active{display:block;animation:fu .35s ease both}

        /* SITE HEADER */
        .site-header{padding:52px 24px 32px;max-width:900px;margin:0 auto;text-align:center}
        .back-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:7px;font-size:12px;font-weight:500;text-decoration:none;border:1px solid var(--border);background:var(--card);color:var(--text);margin-bottom:28px;transition:all .15s;cursor:pointer}
        .back-btn:hover{border-color:var(--accent);color:var(--text-hi)}
        .logo-line{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--accent);letter-spacing:.1em;display:block;margin-bottom:10px}
        h1{font-size:clamp(1.8rem,5vw,2.6rem);font-weight:600;color:var(--text-head);letter-spacing:-.03em;line-height:1.1;margin-bottom:8px}
        [data-theme="dark"] h1::after{content:'_';color:var(--accent);animation:blink 1s step-end infinite;font-weight:400}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
        .tagline{font-size:13px;color:var(--text-dim);margin-bottom:6px}
        .meta-row{display:flex;justify-content:center;gap:18px;margin-top:16px;flex-wrap:wrap}
        .meta-item{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-dim);font-family:'IBM Plex Mono',monospace}
        .meta-item i{color:var(--accent);font-size:10px}

        /* SECTION WRAPPER */
        .folder-section{max-width:900px;margin:0 auto;padding:0 24px 64px}
        .section-label{font-family:'IBM Plex Mono',monospace;font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--text-dim);display:flex;align-items:center;gap:10px;margin-bottom:20px}
        .section-label::after{content:'';flex:1;height:1px;background:var(--border)}

        /* FOLDER GRID */
        .folder-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
        @media(max-width:500px){.folder-grid{grid-template-columns:repeat(2,1fr);gap:10px}}
        .folder-card{position:relative;border:1px solid var(--border);border-radius:10px;overflow:hidden;cursor:pointer;background:var(--card);transition:border-color .2s,transform .2s;aspect-ratio:4/3}
        .folder-card:hover{border-color:var(--accent);transform:translateY(-3px)}
        .folder-cover{width:100%;height:100%;object-fit:cover;display:block;transition:transform .4s ease}
        .folder-card:hover .folder-cover{transform:scale(1.06)}
        .folder-cover-placeholder{width:100%;height:100%;background:var(--surface);display:flex;align-items:center;justify-content:center;font-size:32px;color:var(--border-s)}
        .folder-info{position:absolute;bottom:0;left:0;right:0;padding:28px 12px 12px;background:linear-gradient(to top,rgba(0,0,0,.75) 0%,transparent 100%);display:flex;align-items:flex-end;justify-content:space-between}
        .folder-name{font-size:13px;font-weight:600;color:#fff;line-height:1.2;text-shadow:0 1px 4px rgba(0,0,0,.5)}
        .folder-count{font-family:'IBM Plex Mono',monospace;font-size:10px;color:rgba(255,255,255,.65);text-shadow:0 1px 3px rgba(0,0,0,.5)}
        .folder-icon-badge{position:absolute;top:10px;left:10px;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);border-radius:6px;padding:4px 7px;font-size:11px;color:rgba(255,255,255,.85);display:flex;align-items:center;gap:4px}

        /* SKELETON loading */
        .skeleton{background:linear-gradient(90deg,var(--surface) 25%,var(--card-h, #131713) 50%,var(--surface) 75%);background-size:200% 100%;animation:shimmer 1.4s infinite}
        @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

        /* VIDEO GRID */
        .video-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
        @media(max-width:560px){.video-grid{grid-template-columns:1fr}}
        .video-card{border:1px solid var(--border);border-radius:12px;overflow:hidden;background:var(--card);transition:border-color .2s,transform .2s,box-shadow .2s}
        .video-card:hover{border-color:var(--accent);transform:translateY(-3px);box-shadow:var(--shadow)}
        .vc-thumb{position:relative;width:100%;aspect-ratio:16/9;background:#000;cursor:pointer;overflow:hidden}
        .vc-thumb img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .35s,opacity .2s;opacity:.85}
        .vc-thumb:hover img{transform:scale(1.04);opacity:1}
        .vc-play{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none}
        .vc-play-btn{width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.18);backdrop-filter:blur(6px);border:2px solid rgba(255,255,255,.35);display:flex;align-items:center;justify-content:center;transition:background .2s,transform .2s}
        .vc-thumb:hover .vc-play-btn{background:rgba(255,255,255,.3);transform:scale(1.08)}
        .vc-play-btn i{color:#fff;font-size:18px;margin-left:3px}
        .vc-iframe-wrap{display:none;position:relative;width:100%;aspect-ratio:16/9}
        .vc-iframe-wrap iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
        .vc-iframe-wrap.active{display:block}
        .vc-thumb.hidden{display:none}
        .vc-body{padding:14px 16px 16px}
        .vc-title{font-size:13px;font-weight:600;color:var(--text-head);margin-bottom:5px;line-height:1.35}
        .vc-desc{font-size:12px;color:var(--text-dim);line-height:1.55}

        /* GALLERY PAGE */
        .gallery-header{max-width:900px;margin:0 auto;padding:52px 24px 28px}
        .gallery-header-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:6px}
        .gallery-title{font-size:clamp(1.4rem,4vw,2rem);font-weight:600;color:var(--text-head);letter-spacing:-.02em}
        .gallery-sub{font-size:12px;color:var(--text-dim);font-family:'IBM Plex Mono',monospace;margin-bottom:20px}

        /* MASONRY */
        .masonry-wrap{max-width:900px;margin:0 auto;padding:0 24px 64px}
        .masonry{columns:3 180px;column-gap:10px}
        @media(max-width:480px){.masonry{columns:2 140px}}
        .masonry-item{break-inside:avoid;margin-bottom:10px;border-radius:8px;overflow:hidden;border:1px solid var(--border);cursor:pointer;position:relative;background:var(--card);transition:border-color .2s,transform .2s}
        .masonry-item:hover{border-color:var(--accent);transform:scale(1.015)}
        .masonry-item img{width:100%;display:block;transition:transform .3s ease}
        .masonry-item:hover img{transform:scale(1.04)}
        .masonry-overlay{position:absolute;inset:0;background:rgba(0,0,0,0);opacity:0;display:flex;align-items:center;justify-content:center;transition:all .25s;font-size:20px;color:#fff}
        .masonry-item:hover .masonry-overlay{opacity:1;background:rgba(0,0,0,.35)}

        /* Skeleton masonry item */
        .masonry-skel{break-inside:avoid;margin-bottom:10px;border-radius:8px;border:1px solid var(--border)}

        /* LOADING SPINNER */
        .gallery-loading{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;gap:14px;color:var(--text-dim)}
        .spinner{width:28px;height:28px;border:2px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin .7s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}

        /* EMPTY */
        .empty-state{text-align:center;padding:60px 20px;color:var(--text-dim)}
        .empty-state i{font-size:36px;margin-bottom:14px;display:block;opacity:.4}
        .empty-state p{font-size:13px}

        /* ── LIGHTBOX ── */
        .lightbox{display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,.93);align-items:center;justify-content:center}
        .lightbox.open{display:flex;animation:fadeIn .2s ease}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        .lb-wrap{position:relative;max-width:min(92vw,960px);max-height:90vh;animation:lbUp .25s ease;user-select:none}
        @keyframes lbUp{from{transform:scale(.94);opacity:0}to{transform:scale(1);opacity:1}}
        .lb-img{max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px;display:block;pointer-events:none;transition:opacity .15s}
        .lb-img.loading{opacity:.4}
        .lb-close{position:absolute;top:-38px;right:0;background:none;border:none;color:#fff;font-size:30px;cursor:pointer;opacity:.65;transition:opacity .15s;padding:6px}
        .lb-close:hover{opacity:1}
        .lb-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.1);border:none;color:#fff;padding:10px 14px;cursor:pointer;font-size:16px;border-radius:6px;transition:background .2s;backdrop-filter:blur(4px)}
        .lb-nav:hover{background:rgba(255,255,255,.22)}
        .lb-prev{left:-52px}.lb-next{right:-52px}
        @media(max-width:700px){.lb-prev{left:8px}.lb-next{right:8px}.lb-close{top:8px;right:8px}}
        .lb-counter{position:absolute;bottom:-32px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,.55);font-size:11px;font-family:'IBM Plex Mono',monospace;white-space:nowrap}

        /* swipe hint (mobile only) */
        .lb-swipe-hint{display:none;position:absolute;bottom:-54px;left:50%;transform:translateX(-50%);font-size:10px;color:rgba(255,255,255,.3);font-family:'IBM Plex Mono',monospace;white-space:nowrap}
        @media(hover:none){.lb-swipe-hint{display:block}}

        /* FOOTER */
        footer{text-align:center;padding:28px 20px 20px;font-size:11px;color:var(--text-dim);border-top:1px solid var(--border)}
        footer a{color:var(--accent);text-decoration:none}

        /* DIVIDER */
        .section-divider{max-width:900px;margin:0 auto;padding:0 24px}
        .section-divider hr{border:none;border-top:1px solid var(--border);margin-bottom:48px}

        @keyframes fu{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
        .folder-card:nth-child(1){animation:fu .35s ease .05s both}
        .folder-card:nth-child(2){animation:fu .35s ease .1s both}
        .folder-card:nth-child(3){animation:fu .35s ease .15s both}
        .folder-card:nth-child(4){animation:fu .35s ease .2s both}
        .folder-card:nth-child(5){animation:fu .35s ease .25s both}
        .folder-card:nth-child(6){animation:fu .35s ease .3s both}
    </style>
</head>
<body>

<button class="toggle" onclick="toggleTheme()">
    <i class="fas fa-circle-half-stroke"></i>
    <span id="tlbl">Light</span>
</button>

<!-- ═══════════════════ PAGE: HOME ═══════════════════ -->
<div class="page active" id="page-home">
    <div class="site-header">
        <a href="../index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali ke Portfolio</a>
        <span class="logo-line">// SELAWAS VISUAL · Pekalongan</span>
        <h1>Portfolio Fotografi</h1>
        <p class="tagline">Studio fotografi independen · 2017 — 2024</p>
        <div class="meta-row">
            <div class="meta-item"><i class="fas fa-folder"></i> <?= count($data_kategori) ?> kategori</div>
            <div class="meta-item"><i class="fas fa-images"></i> <?= $total_foto_all ?> foto</div>
            <div class="meta-item"><i class="fas fa-video"></i> <?= count($videos) ?> video</div>
            <div class="meta-item"><i class="fas fa-map-marker-alt"></i> Pekalongan</div>
        </div>
    </div>

    <div class="folder-section">
        <div class="section-label">Pilih Kategori Foto</div>
        <div class="folder-grid" id="folder-grid"></div>
    </div>

    <?php if (!empty($videos)): ?>
    <div class="section-divider"><hr></div>
    <div class="folder-section" style="padding-bottom:32px">
        <div class="section-label">Video Portfolio</div>
        <div class="video-grid">
            <?php foreach ($videos as $v):
                $thumb = getYouTubeThumb($v['video_url']);
                $embed = getYouTubeEmbed($v['video_url']);
                $vid_id = 'vc-' . $v['id'];
            ?>
            <div class="video-card">
                <div class="vc-thumb" id="thumb-<?= $vid_id ?>" onclick="playVideo('<?= $vid_id ?>','<?= htmlspecialchars($embed) ?>')">
                    <?php if ($thumb): ?>
                        <img src="<?= $thumb ?>" alt="<?= htmlspecialchars($v['title']) ?>" loading="lazy" decoding="async">
                    <?php else: ?>
                        <div style="width:100%;height:100%;background:#111;display:flex;align-items:center;justify-content:center"><i class="fas fa-film" style="font-size:32px;color:#444"></i></div>
                    <?php endif; ?>
                    <div class="vc-play"><div class="vc-play-btn"><i class="fas fa-play"></i></div></div>
                </div>
                <div class="vc-iframe-wrap" id="iframe-<?= $vid_id ?>"></div>
                <div class="vc-body">
                    <div class="vc-title"><?= htmlspecialchars($v['title']) ?></div>
                    <?php if (!empty($v['description'])): ?>
                        <div class="vc-desc"><?= nl2br(htmlspecialchars($v['description'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <footer><p>© <?= date('Y') ?> SELAWAS VISUAL — <a href="mailto:rizqisubagyo07@gmail.com">rizqisubagyo07@gmail.com</a></p></footer>
</div>

<!-- ═══════════════════ PAGE: GALLERY ═══════════════════ -->
<div class="page" id="page-gallery">
    <div class="gallery-header">
        <div class="gallery-header-top">
            <div>
                <div class="gallery-title" id="gal-title">—</div>
                <div class="gallery-sub" id="gal-sub">// memuat...</div>
            </div>
            <button class="back-btn" onclick="goHome()"><i class="fas fa-arrow-left"></i> Semua Kategori</button>
        </div>
    </div>
    <div class="masonry-wrap">
        <div id="masonry-container">
            <!-- Loading state -->
            <div class="gallery-loading" id="gallery-loading">
                <div class="spinner"></div>
                <span style="font-size:12px;font-family:'IBM Plex Mono',monospace">Memuat foto...</span>
            </div>
            <div class="masonry" id="masonry-grid" style="display:none"></div>
        </div>
    </div>
    <footer><p>© <?= date('Y') ?> SELAWAS VISUAL — <a href="mailto:rizqisubagyo07@gmail.com">rizqisubagyo07@gmail.com</a></p></footer>
</div>

<!-- ═══════════════════ LIGHTBOX ═══════════════════ -->
<div class="lightbox" id="lightbox" onclick="lbClickOutside(event)">
    <div class="lb-wrap" id="lb-wrap">
        <button class="lb-close" onclick="closeLb()">×</button>
        <img class="lb-img" id="lb-img" src="" alt="">
        <button class="lb-nav lb-prev" onclick="lbNav(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="lb-nav lb-next" onclick="lbNav(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="lb-counter" id="lb-counter">1 / 1</div>
        <div class="lb-swipe-hint">← geser untuk navigasi →</div>
    </div>
</div>

<script>
/* ═══════════════════════════════════════════════════
   DATA — hanya metadata ringan (id, name, icon, cover, total)
   Foto per kategori di-fetch saat folder diklik
═══════════════════════════════════════════════════ */
const categories = <?= $json_categories ?>;

// Cache foto yang sudah di-fetch — tidak fetch ulang kalau sudah ada
const photoCache = {};

// State
let currentPhotos = [];
let currentCatId  = null;
let currentLbIdx  = 0;

/* ═══ RENDER FOLDER HOME ═══ */
function renderHome() {
    const grid = document.getElementById('folder-grid');
    grid.innerHTML = '';
    if (!categories.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-folder-open"></i><p>Belum ada kategori foto.</p></div>';
        return;
    }
    categories.forEach(cat => {
        const card = document.createElement('div');
        card.className = 'folder-card';
        card.onclick = () => openCategory(cat.id, cat.name);
        const coverHtml = cat.cover
            ? `<img class="folder-cover" src="${cat.cover}" alt="${cat.name}" loading="lazy" decoding="async">`
            : `<div class="folder-cover-placeholder"><i class="fas ${cat.icon}"></i></div>`;
        card.innerHTML = `
            ${coverHtml}
            <div class="folder-icon-badge"><i class="fas ${cat.icon}"></i></div>
            <div class="folder-info">
                <div class="folder-name">${cat.name}</div>
                <div class="folder-count">${cat.total} foto</div>
            </div>`;
        grid.appendChild(card);
    });
}

/* ═══ OPEN CATEGORY — lazy fetch foto ═══ */
async function openCategory(id, name) {
    currentCatId = id;
    const cat = categories.find(c => c.id === id);

    // Switch ke halaman gallery dulu — tampilkan loading
    document.getElementById('gal-title').textContent = name;
    document.getElementById('gal-sub').textContent   = '// memuat...';
    document.getElementById('gallery-loading').style.display = 'flex';
    document.getElementById('masonry-grid').style.display    = 'none';
    document.getElementById('masonry-grid').innerHTML        = '';
    document.getElementById('page-home').classList.remove('active');
    document.getElementById('page-gallery').classList.add('active');
    window.scrollTo({ top: 0, behavior: 'instant' });

    // Cek cache dulu
    if (!photoCache[id]) {
        try {
            const res  = await fetch(`api_photos.php?cat=${encodeURIComponent(id)}`);
            const data = await res.json();
            photoCache[id] = data.ok ? data.photos : [];
        } catch (e) {
            photoCache[id] = [];
        }
    }

    const photos = photoCache[id];
    currentPhotos = photos;

    // Update subtitle
    document.getElementById('gal-sub').textContent = `// ${photos.length} foto`;

    // Sembunyikan loading
    document.getElementById('gallery-loading').style.display = 'none';
    const grid = document.getElementById('masonry-grid');
    grid.style.display = 'block';

    if (!photos.length) {
        grid.innerHTML = `<div class="empty-state" style="column-span:all"><i class="fas fa-images"></i><p>Foto akan segera ditambahkan.</p></div>`;
        return;
    }

    // Render masonry dengan loading="lazy"
    photos.forEach((url, idx) => {
        const item = document.createElement('div');
        item.className = 'masonry-item';
        item.innerHTML = `<img src="${url}" alt="${name} ${idx+1}" loading="lazy" decoding="async"><div class="masonry-overlay"><i class="fas fa-expand"></i></div>`;
        item.onclick = () => openLb(idx);
        grid.appendChild(item);
    });
}

/* ═══ GO HOME ═══ */
function goHome() {
    // Pause video
    document.querySelectorAll('.vc-iframe-wrap.active').forEach(el => {
        el.innerHTML = '';
        el.classList.remove('active');
        const t = document.getElementById(el.id.replace('iframe-','thumb-'));
        if (t) t.classList.remove('hidden');
    });
    document.getElementById('page-gallery').classList.remove('active');
    document.getElementById('page-home').classList.add('active');
    window.scrollTo({ top: 0, behavior: 'instant' });
}

/* ═══ VIDEO PLAY ═══ */
function playVideo(vidId, embedUrl) {
    const thumb     = document.getElementById('thumb-'  + vidId);
    const iframeWrap= document.getElementById('iframe-' + vidId);
    if (!iframeWrap) return;
    document.querySelectorAll('.vc-iframe-wrap.active').forEach(el => {
        if (el.id !== 'iframe-' + vidId) {
            el.innerHTML = '';
            el.classList.remove('active');
            const t = document.getElementById(el.id.replace('iframe-','thumb-'));
            if (t) t.classList.remove('hidden');
        }
    });
    thumb.classList.add('hidden');
    iframeWrap.classList.add('active');
    iframeWrap.innerHTML = `<iframe src="${embedUrl}&autoplay=1" allow="autoplay;encrypted-media;fullscreen" allowfullscreen></iframe>`;
}

/* ═══════════════════════════════════════════════
   LIGHTBOX + SWIPE MOBILE
═══════════════════════════════════════════════ */
function openLb(idx) {
    currentLbIdx = idx;
    updateLb();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function updateLb() {
    const img = document.getElementById('lb-img');
    img.classList.add('loading');
    img.onload = () => img.classList.remove('loading');
    img.src = currentPhotos[currentLbIdx];
    document.getElementById('lb-counter').textContent = `${currentLbIdx + 1} / ${currentPhotos.length}`;
}

function lbNav(dir) {
    currentLbIdx = (currentLbIdx + dir + currentPhotos.length) % currentPhotos.length;
    updateLb();
}

function closeLb() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
}

function lbClickOutside(e) {
    if (e.target === document.getElementById('lightbox')) closeLb();
}

// Keyboard navigation
document.addEventListener('keydown', e => {
    if (!document.getElementById('lightbox').classList.contains('open')) return;
    if (e.key === 'ArrowRight') lbNav(1);
    if (e.key === 'ArrowLeft')  lbNav(-1);
    if (e.key === 'Escape')     closeLb();
});

/* ── SWIPE TOUCH (mobile) ── */
(function() {
    let startX = 0, startY = 0, isDragging = false;
    const SWIPE_THRESHOLD = 50;   // pixel minimum untuk dianggap swipe
    const AXIS_LOCK = 30;         // pixel vertikal max sebelum lock horizontal

    const lb = document.getElementById('lightbox');

    lb.addEventListener('touchstart', e => {
        if (e.touches.length !== 1) return;
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
        isDragging = true;
    }, { passive: true });

    lb.addEventListener('touchmove', e => {
        if (!isDragging || e.touches.length !== 1) return;
        const dx = Math.abs(e.touches[0].clientX - startX);
        const dy = Math.abs(e.touches[0].clientY - startY);
        // Lock horizontal scroll — cegah halaman ikut scroll
        if (dx > dy && dx > AXIS_LOCK) {
            e.preventDefault();
        }
    }, { passive: false });

    lb.addEventListener('touchend', e => {
        if (!isDragging) return;
        isDragging = false;
        if (e.changedTouches.length !== 1) return;

        const dx = e.changedTouches[0].clientX - startX;
        const dy = Math.abs(e.changedTouches[0].clientY - startY);

        // Hanya proses swipe horizontal, abaikan jika lebih vertikal
        if (Math.abs(dx) < SWIPE_THRESHOLD || dy > Math.abs(dx)) return;

        if (dx < 0) lbNav(1);   // swipe kiri → foto berikutnya
        else        lbNav(-1);  // swipe kanan → foto sebelumnya
    }, { passive: true });
})();

/* ═══ THEME ═══ */
function setTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    document.getElementById('tlbl').textContent = t === 'dark' ? 'Light' : 'Dark';
    try { localStorage.setItem('theme', t); } catch(e) {}
}
function toggleTheme() {
    setTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
}
(function() {
    try { setTheme(localStorage.getItem('theme') || 'dark'); } catch(e) {}
})();

/* ═══ INIT ═══ */
renderHome();
</script>
</body>
</html>