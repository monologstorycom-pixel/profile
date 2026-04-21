<?php
require '../admin/koneksi.php'; // Koneksi mundur 1 folder karena ada di dalam folder slws/

// 1. Ambil data kategori dari database
$stmt_cat = $pdo->query("SELECT * FROM slws_categories");
$categories_db = $stmt_cat->fetchAll();

$data_kategori = [];
foreach ($categories_db as $kat) {
    // 2. Ambil semua foto milik kategori ini
    $stmt_photo = $pdo->prepare("SELECT image_path FROM slws_photos WHERE category_id = ? ORDER BY id DESC");
    $stmt_photo->execute([$kat['id']]);
    $photos_db = $stmt_photo->fetchAll(PDO::FETCH_COLUMN);
    
    // 3. Tambahkan '../' ke depan path agar gambar merujuk ke luar folder slws (ke root uploads/)
    $photo_urls = array_map(function($path) {
        return '../' . $path; 
    }, $photos_db);
    
    // 4. Jadikan foto pertama sebagai Cover Folder otomatis (kalau ada)
    $cover = count($photo_urls) > 0 ? $photo_urls[0] : '';

    // Susun format arraynya agar sama persis seperti yang diminta Javascript lama kamu
    $data_kategori[] = [
        'id' => $kat['id'],
        'name' => $kat['name'],
        'icon' => $kat['icon'],
        'cover' => $cover,
        'photos' => $photo_urls
    ];
}

// Convert array PHP menjadi JSON Text
$json_categories = json_encode($data_kategori);
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SELAWAS VISUAL — Portfolio Fotografi</title>
    <meta name="description" content="SELAWAS VISUAL — Studio fotografi profesional Pekalongan. Wedding, Prewedding, Portrait, Product, Event.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        [data-theme="dark"] {
            --bg:        #090b09;
            --surface:   #0d100d;
            --card:      #101310;
            --card-h:    #131713;
            --border:    #1c241c;
            --border-s:  #243024;
            --text:      #8bb880;
            --text-hi:   #b4d4ac;
            --text-head: #cce8c4;
            --text-dim:  #3d5a38;
            --accent:    #52a852;
            --accent-bg: rgba(82,168,82,0.08);
            --shadow:    0 2px 16px rgba(0,0,0,0.55);
            --overlay:   rgba(9,11,9,0.7);
        }
        [data-theme="light"] {
            --bg:        #f4f4f0;
            --surface:   #ffffff;
            --card:      #ffffff;
            --card-h:    #fafaf8;
            --border:    #e5e5e0;
            --border-s:  #d0d0c8;
            --text:      #404040;
            --text-hi:   #1a1a1a;
            --text-head: #111111;
            --text-dim:  #999990;
            --accent:    #1d6ed8;
            --accent-bg: rgba(29,110,216,0.07);
            --shadow:    0 2px 12px rgba(0,0,0,0.07);
            --overlay:   rgba(244,244,240,0.75);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.65;
            transition: background 0.25s, color 0.25s;
            overflow-x: hidden;
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-s); border-radius: 3px; }

        /* ── TOGGLE ── */
        .toggle {
            position: fixed; top: 18px; right: 18px; z-index: 300;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 99px; padding: 6px 13px 6px 10px;
            display: flex; align-items: center; gap: 6px;
            cursor: pointer; font-size: 12px; font-weight: 500;
            color: var(--text); box-shadow: var(--shadow);
            transition: border-color 0.2s, color 0.2s;
        }
        .toggle:hover { border-color: var(--border-s); color: var(--text-hi); }

        /* ── PAGES ── */
        .page { display: none; }
        .page.active { display: block; animation: fu 0.35s ease both; }

        /* ── HEADER ── */
        .site-header {
            padding: 52px 24px 32px;
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 13px; border-radius: 7px; font-size: 12px;
            font-weight: 500; text-decoration: none;
            border: 1px solid var(--border); background: var(--card);
            color: var(--text); margin-bottom: 28px;
            transition: all 0.15s; cursor: pointer;
        }
        .back-btn:hover { border-color: var(--accent); color: var(--text-hi); }

        .logo-line {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 11px; color: var(--accent);
            letter-spacing: 0.1em; display: block; margin-bottom: 10px;
        }

        h1 {
            font-size: clamp(1.8rem, 5vw, 2.6rem);
            font-weight: 600; color: var(--text-head);
            letter-spacing: -0.03em; line-height: 1.1; margin-bottom: 8px;
        }

        [data-theme="dark"] h1::after {
            content: '_'; color: var(--accent);
            animation: blink 1s step-end infinite; font-weight: 400;
        }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

        .tagline {
            font-size: 13px; color: var(--text-dim); margin-bottom: 6px;
        }

        .meta-row {
            display: flex; justify-content: center; gap: 18px;
            margin-top: 16px; flex-wrap: wrap;
        }
        .meta-item {
            display: flex; align-items: center; gap: 5px;
            font-size: 11px; color: var(--text-dim);
            font-family: 'IBM Plex Mono', monospace;
        }
        .meta-item i { color: var(--accent); font-size: 10px; }

        /* ── FOLDER GRID ── */
        .folder-section {
            max-width: 900px; margin: 0 auto; padding: 0 24px 64px;
        }

        .section-label {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 10px; letter-spacing: 0.12em;
            text-transform: uppercase; color: var(--text-dim);
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 20px;
        }
        .section-label::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        .folder-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        @media (max-width: 500px) {
            .folder-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        }

        .folder-card {
            position: relative;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            background: var(--card);
            transition: border-color 0.2s, transform 0.2s;
            aspect-ratio: 4/3;
        }
        .folder-card:hover {
            border-color: var(--accent);
            transform: translateY(-3px);
        }

        .folder-cover {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }
        .folder-card:hover .folder-cover { transform: scale(1.06); }

        .folder-cover-placeholder {
            width: 100%; height: 100%;
            background: var(--surface);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: var(--border-s);
        }

        .folder-info {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 28px 12px 12px;
            background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 100%);
            display: flex; align-items: flex-end; justify-content: space-between;
        }

        .folder-name {
            font-size: 13px; font-weight: 600;
            color: #fff; line-height: 1.2;
            text-shadow: 0 1px 4px rgba(0,0,0,0.5);
        }

        .folder-count {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 10px; color: rgba(255,255,255,0.65);
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }

        .folder-icon-badge {
            position: absolute; top: 10px; left: 10px;
            background: rgba(0,0,0,0.45);
            backdrop-filter: blur(4px);
            border-radius: 6px; padding: 4px 7px;
            font-size: 11px; color: rgba(255,255,255,0.85);
            display: flex; align-items: center; gap: 4px;
        }

        /* ── GALLERY PAGE ── */
        .gallery-header {
            max-width: 900px; margin: 0 auto;
            padding: 52px 24px 28px;
        }

        .gallery-header-top {
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 12px;
            margin-bottom: 6px;
        }

        .gallery-title {
            font-size: clamp(1.4rem, 4vw, 2rem);
            font-weight: 600; color: var(--text-head);
            letter-spacing: -0.02em;
        }

        .gallery-sub {
            font-size: 12px; color: var(--text-dim);
            font-family: 'IBM Plex Mono', monospace;
            margin-bottom: 20px;
        }

        /* ── MASONRY GRID ── */
        .masonry-wrap {
            max-width: 900px; margin: 0 auto; padding: 0 24px 64px;
        }

        .masonry {
            columns: 3 180px;
            column-gap: 10px;
        }

        @media (max-width: 480px) { .masonry { columns: 2 140px; } }

        .masonry-item {
            break-inside: avoid;
            margin-bottom: 10px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
            cursor: pointer;
            position: relative;
            background: var(--card);
            transition: border-color 0.2s, transform 0.2s;
        }
        .masonry-item:hover { border-color: var(--accent); transform: scale(1.015); }

        .masonry-item img {
            width: 100%; display: block;
            transition: transform 0.3s ease;
        }
        .masonry-item:hover img { transform: scale(1.04); }

        .masonry-overlay {
            position: absolute; inset: 0;
            background: rgba(0,0,0,0); opacity: 0;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.25s;
            font-size: 20px; color: #fff;
        }
        .masonry-item:hover .masonry-overlay { opacity: 1; background: rgba(0,0,0,0.35); }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--text-dim);
        }
        .empty-state i { font-size: 36px; margin-bottom: 14px; display: block; opacity: 0.4; }
        .empty-state p { font-size: 13px; }

        /* ── LIGHTBOX ── */
        .lightbox {
            display: none; position: fixed; inset: 0; z-index: 999;
            background: rgba(0,0,0,0.93);
            align-items: center; justify-content: center;
        }
        .lightbox.open { display: flex; animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }

        .lb-wrap {
            position: relative;
            max-width: min(92vw, 960px);
            max-height: 90vh;
            animation: lbUp 0.25s ease;
        }
        @keyframes lbUp { from{transform:scale(0.94);opacity:0} to{transform:scale(1);opacity:1} }

        .lb-img {
            max-width: 100%; max-height: 90vh;
            object-fit: contain; border-radius: 8px; display: block;
        }
        .lb-close {
            position: absolute; top: -38px; right: 0;
            background: none; border: none; color: #fff;
            font-size: 30px; cursor: pointer; opacity: 0.65;
            transition: opacity 0.15s; padding: 6px;
        }
        .lb-close:hover { opacity: 1; }

        .lb-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,0.1); border: none;
            color: #fff; padding: 10px 14px; cursor: pointer;
            font-size: 16px; border-radius: 6px;
            transition: background 0.2s; backdrop-filter: blur(4px);
        }
        .lb-nav:hover { background: rgba(255,255,255,0.22); }
        .lb-prev { left: -52px; }
        .lb-next { right: -52px; }

        @media (max-width: 700px) {
            .lb-prev { left: 8px; } .lb-next { right: 8px; }
            .lb-close { top: 8px; right: 8px; }
        }

        .lb-counter {
            position: absolute; bottom: -32px; left: 50%;
            transform: translateX(-50%); color: rgba(255,255,255,0.55);
            font-size: 11px; font-family: 'IBM Plex Mono', monospace;
        }

        /* ── FOOTER ── */
        footer {
            text-align: center; padding: 28px 20px 20px;
            font-size: 11px; color: var(--text-dim);
            border-top: 1px solid var(--border);
        }
        footer a { color: var(--accent); text-decoration: none; }

        @keyframes fu { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:none} }

        /* stagger folder cards */
        .folder-card:nth-child(1) { animation: fu 0.35s ease 0.05s both; }
        .folder-card:nth-child(2) { animation: fu 0.35s ease 0.1s both; }
        .folder-card:nth-child(3) { animation: fu 0.35s ease 0.15s both; }
        .folder-card:nth-child(4) { animation: fu 0.35s ease 0.2s both; }
        .folder-card:nth-child(5) { animation: fu 0.35s ease 0.25s both; }
    </style>
</head>
<body>

<button class="toggle" onclick="toggleTheme()">
    <i class="fas fa-circle-half-stroke"></i>
    <span id="tlbl">Light</span>
</button>

<div class="page active" id="page-home">
    <div class="site-header">
        <a href="../index.php" class="back-btn"> <i class="fas fa-arrow-left"></i> Kembali ke Portfolio
        </a>
        <span class="logo-line">// SELAWAS VISUAL · Pekalongan</span>
        <h1>Portfolio Fotografi</h1>
        <p class="tagline">Studio fotografi independen · 2017 — 2024</p>
        <div class="meta-row">
            <div class="meta-item"><i class="fas fa-folder"></i> <?= count($data_kategori) ?> kategori</div>
            <div class="meta-item"><i class="fas fa-calendar"></i> 2017 — 2024</div>
            <div class="meta-item"><i class="fas fa-map-marker-alt"></i> Pekalongan</div>
        </div>
    </div>

    <div class="folder-section">
        <div class="section-label">Pilih Kategori</div>
        <div class="folder-grid" id="folder-grid">
            </div>
    </div>

    <footer>
        <p>© <?= date('Y') ?> SELAWAS VISUAL — <a href="mailto:rizqisubagyo07@gmail.com">rizqisubagyo07@gmail.com</a></p>
    </footer>
</div>

<div class="page" id="page-gallery">
    <div class="gallery-header">
        <div class="gallery-header-top">
            <div>
                <div class="gallery-title" id="gal-title">Wedding</div>
                <div class="gallery-sub" id="gal-sub">// 0 foto</div>
            </div>
            <button class="back-btn" onclick="goHome()">
                <i class="fas fa-arrow-left"></i> Semua Kategori
            </button>
        </div>
    </div>

    <div class="masonry-wrap">
        <div class="masonry" id="masonry-grid"></div>
    </div>

    <footer>
        <p>© <?= date('Y') ?> SELAWAS VISUAL — <a href="mailto:rizqisubagyo07@gmail.com">rizqisubagyo07@gmail.com</a></p>
    </footer>
</div>

<div class="lightbox" id="lightbox" onclick="lbClickOutside(event)">
    <div class="lb-wrap">
        <button class="lb-close" onclick="closeLb()">&times;</button>
        <img class="lb-img" id="lb-img" src="" alt="">
        <button class="lb-nav lb-prev" onclick="lbNav(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="lb-nav lb-next" onclick="lbNav(1)"><i class="fas fa-chevron-right"></i></button>
        <div class="lb-counter" id="lb-counter">1 / 1</div>
    </div>
</div>

<script>
// ── INJEKSI DATA PHP KE JAVASCRIPT ──
// Data sekarang otomatis memanggil dari Database (PHP Variable)!
const categories = <?= $json_categories ?>;

// ── STATE ──
let currentPhotos = [];
let currentLbIdx  = 0;

// ── RENDER FOLDER HOME ──
function renderHome() {
    const grid = document.getElementById('folder-grid');
    grid.innerHTML = '';
    categories.forEach(cat => {
        const card = document.createElement('div');
        card.className = 'folder-card';
        card.onclick = () => openCategory(cat.id);

        const coverHtml = cat.cover
            ? `<img class="folder-cover" src="${cat.cover}" alt="${cat.name}" loading="lazy">`
            : `<div class="folder-cover-placeholder"><i class="fas ${cat.icon}"></i></div>`;

        card.innerHTML = `
            ${coverHtml}
            <div class="folder-icon-badge">
                <i class="fas ${cat.icon}"></i>
            </div>
            <div class="folder-info">
                <div class="folder-name">${cat.name}</div>
                <div class="folder-count">${cat.photos.length} foto</div>
            </div>
        `;
        grid.appendChild(card);
    });
}

// ── OPEN CATEGORY ──
function openCategory(id) {
    const cat = categories.find(c => c.id === id);
    if (!cat) return;

    currentPhotos = cat.photos;

    document.getElementById('gal-title').textContent = cat.name;
    document.getElementById('gal-sub').textContent = `// ${cat.photos.length} foto`;

    const grid = document.getElementById('masonry-grid');
    grid.innerHTML = '';

    if (cat.photos.length === 0) {
        grid.innerHTML = `
            <div class="empty-state" style="column-span:all">
                <i class="fas fa-images"></i>
                <p>Foto akan segera ditambahkan.</p>
            </div>`;
    } else {
        cat.photos.forEach((url, idx) => {
            const item = document.createElement('div');
            item.className = 'masonry-item';
            item.innerHTML = `
                <img src="${url}" alt="${cat.name} ${idx+1}" loading="lazy">
                <div class="masonry-overlay"><i class="fas fa-expand"></i></div>
            `;
            item.onclick = () => openLb(idx);
            grid.appendChild(item);
        });
    }

    document.getElementById('page-home').classList.remove('active');
    document.getElementById('page-gallery').classList.add('active');
    window.scrollTo(0, 0);
}

// ── GO HOME ──
function goHome() {
    document.getElementById('page-gallery').classList.remove('active');
    document.getElementById('page-home').classList.add('active');
    window.scrollTo(0, 0);
}

// ── LIGHTBOX ──
function openLb(idx) {
    currentLbIdx = idx;
    updateLb();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function updateLb() {
    document.getElementById('lb-img').src = currentPhotos[currentLbIdx];
    document.getElementById('lb-counter').textContent =
        `${currentLbIdx + 1} / ${currentPhotos.length}`;
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

document.addEventListener('keydown', e => {
    if (!document.getElementById('lightbox').classList.contains('open')) return;
    if (e.key === 'ArrowRight') lbNav(1);
    if (e.key === 'ArrowLeft')  lbNav(-1);
    if (e.key === 'Escape')     closeLb();
});

// ── THEME ──
const html = document.documentElement;
function setTheme(t) {
    html.setAttribute('data-theme', t);
    document.getElementById('tlbl').textContent = t === 'dark' ? 'Light' : 'Dark';
    try { localStorage.setItem('theme', t); } catch(e){}
}
function toggleTheme() {
    setTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
}
try {
    const s = localStorage.getItem('theme');
    if (s) setTheme(s);
    else if (window.matchMedia('(prefers-color-scheme: light)').matches) setTheme('light');
} catch(e) {}

// ── INIT ──
renderHome();
</script>
</body>
</html>