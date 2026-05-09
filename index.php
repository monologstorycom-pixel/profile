<?php
require 'admin/koneksi.php';

// --- AMBIL DATA PROFIL ---
$stmt = $pdo->query("SELECT * FROM profile_settings LIMIT 1");
$profil = $stmt->fetch();

$nama = htmlspecialchars($profil['full_name'] ?? 'Rizqi Subagyo');
$tagline = htmlspecialchars($profil['tagline'] ?? 'IT Support Specialist | Full-stack Developer');
$status = htmlspecialchars($profil['availability_status'] ?? 'Tersedia');
$email = htmlspecialchars($profil['email'] ?? 'rizqisubagyo07@gmail.com');
$github = htmlspecialchars($profil['github_link'] ?? 'https://github.com/monologstorycom-pixel');
$linkedin = htmlspecialchars($profil['linkedin_link'] ?? 'https://www.linkedin.com/in/rizqi-subagyo-7ab331380');
$foto = !empty($profil['profile_picture']) ? $profil['profile_picture'] : 'https://avatars.githubusercontent.com/u/252295342?v=4';

// --- AMBIL DATA EXPERIENCE ---
$stmtExp = $pdo->query("SELECT * FROM experiences ORDER BY id DESC");
$experiences = $stmtExp->fetchAll();

// --- AMBIL DATA PROJECTS ---
$stmtProj = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmtProj->fetchAll();

// --- AMBIL DATA VIDEO ---
$stmtVid = $pdo->query("SELECT * FROM videos ORDER BY id DESC");
$videos = $stmtVid->fetchAll();

// --- AMBIL DATA SKILLS ---
$skills_raw = [];
try {
    $stmtSkills = $pdo->query("SELECT * FROM skills ORDER BY group_name, sort_order, skill_name");
    foreach ($stmtSkills->fetchAll() as $s) {
        $skills_raw[$s['group_name']][] = $s['skill_name'];
    }
} catch (Exception $e) {}

// Fallback hardcode jika tabel belum ada
if (empty($skills_raw)) {
    $skills_raw = [
        'Programming'    => ['Python', 'Next.js', 'PHP'],
        'Networking'     => ['LAN/WAN', 'TCP/IP', 'Firewall', 'CCTV', 'UniFi', 'Ruijie'],
        'Infrastructure' => ['MikroTik', 'Proxmox', 'Docker', 'Linux'],
    ];
}

// --- AMBIL DATA CLIENTS ---
$clients = [];
try {
    $stmtClients = $pdo->query("SELECT * FROM clients ORDER BY sort_order, id");
    $clients = $stmtClients->fetchAll();
} catch (Exception $e) {}

// --- FUNGSI HELPER: Convert Link YouTube biasa ke format Embed ---
function getYouTubeEmbed($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match);
    return isset($match[1]) ? "https://www.youtube.com/embed/" . $match[1] : htmlspecialchars($url);
}
?>

<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $nama ?> — IT Support & Full-stack Developer</title>
    <meta name="description" content="<?= $nama ?> — <?= $tagline ?>.">
    <meta name="robots" content="index, follow">
    <meta name="author" content="<?= $nama ?>">
    <link rel="canonical" href="https://rsby.my.id/">

    <!-- Open Graph / Social Preview -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://rsby.my.id/">
    <meta property="og:title" content="<?= $nama ?> — IT Support & Full-stack Developer">
    <meta property="og:description" content="<?= $tagline ?>. Tersedia untuk proyek IT Support, Networking, dan Web Development.">
    <meta property="og:image" content="<?= $foto ?>">
    <meta property="og:locale" content="id_ID">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= $nama ?> — IT Support & Full-stack Developer">
    <meta name="twitter:description" content="<?= $tagline ?>">
    <meta name="twitter:image" content="<?= $foto ?>">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "<?= $nama ?>",
      "jobTitle": "IT Support Specialist & Full-stack Developer",
      "email": "<?= $email ?>",
      "url": "https://rsby.my.id/",
      "image": "<?= $foto ?>",
      "sameAs": [
        "<?= $github ?>",
        "<?= $linkedin ?>"
      ],
      "knowsAbout": ["IT Support", "Networking", "MikroTik", "PHP", "Python", "Next.js", "Docker", "Linux"]
    }
    </script>

    <link rel="icon" type="image/png" href="<?= $foto ?>">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#090b09">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        [data-theme="dark"] {
            --bg:          #090b09;
            --surface:     #0d100d;
            --card:        #101310;
            --card-h:      #131713;
            --border:      #1c241c;
            --border-s:    #243024;
            --text:        #8bb880;
            --text-hi:     #b4d4ac;
            --text-head:   #cce8c4;
            --text-dim:    #3d5a38;
            --accent:      #52a852;
            --accent-bg:   rgba(82,168,82,0.08);
            --shadow:      0 2px 16px rgba(0,0,0,0.55);
        }
        [data-theme="light"] {
            --bg:          #f7f7f5;
            --surface:     #ffffff;
            --card:        #ffffff;
            --card-h:      #fafaf8;
            --border:      #e5e5e0;
            --border-s:    #d0d0c8;
            --text:        #404040;
            --text-hi:     #1a1a1a;
            --text-head:   #111111;
            --text-dim:    #999990;
            --accent:      #1d6ed8;
            --accent-bg:   rgba(29,110,216,0.07);
            --shadow:      0 2px 12px rgba(0,0,0,0.07);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            font-size: 14px;
            line-height: 1.65;
            transition: background 0.25s, color 0.25s;
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border-s); border-radius: 3px; }

        /* ── TOGGLE ── */
        .toggle {
            position: fixed; top: 14px; right: 14px; z-index: 200;
            background: var(--card); border: 1px solid var(--border);
            border-radius: 99px; padding: 6px 13px 6px 10px;
            display: flex; align-items: center; gap: 6px; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 500;
            color: var(--text); box-shadow: var(--shadow);
            transition: border-color 0.2s, color 0.2s;
        }
        .toggle:hover { border-color: var(--border-s); color: var(--text-hi); }
        .toggle i { font-size: 12px; }
        @media (max-width: 560px) {
            .toggle { top: 10px; right: 10px; padding: 5px 10px 5px 8px; font-size: 11px; }
            .toggle span { display: none; }
            header { padding-top: 50px; }
        }

        /* ── LAYOUT ── */
        .page { max-width: 980px; margin: 0 auto; padding: 0 20px; }
        header { padding: 64px 20px 52px; max-width: 980px; margin: 0 auto; }

        .hd { display: flex; align-items: flex-start; gap: 28px; }
        @media (max-width: 560px) {
            .hd { flex-direction: column; align-items: center; text-align: center; }
            .btns { justify-content: center; }
        }

        .av { flex-shrink: 0; position: relative; }
        .av img {
            width: 120px; height: 120px; border-radius: 50%;
            object-fit: cover; border: 1px solid var(--border-s);
            display: block; transition: border-color 0.25s, transform 0.2s; cursor: pointer;
        }
        .av img:hover { transform: scale(1.04); border-color: var(--accent); }

        .online {
            position: absolute; bottom: 3px; right: 3px; width: 11px; height: 11px;
            border-radius: 50%; background: var(--accent); border: 2px solid var(--bg);
            animation: pulse 2.5s ease infinite;
        }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.35} }

        .ht { flex: 1; }
        .pre { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--text-dim); display: block; margin-bottom: 8px; letter-spacing: 0.04em; }
        [data-theme="dark"] .pre { color: var(--accent); }
        
        h1 { font-size: clamp(1.7rem, 4vw, 2.4rem); font-weight: 600; color: var(--text-head); letter-spacing: -0.03em; line-height: 1.1; margin-bottom: 6px; }
        [data-theme="dark"] h1::after { content: '_'; color: var(--accent); animation: blink 1s step-end infinite; font-weight: 400; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }

        .sub { font-size: 13px; color: var(--text-dim); margin-bottom: 16px; }
        .avail { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--accent); margin-bottom: 18px; }
        .avail-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2.5s ease infinite; }

        .btns { display: flex; flex-wrap: wrap; gap: 7px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 15px; border-radius: 7px; font-size: 12px; font-weight: 500; text-decoration: none; border: 1px solid var(--border); background: var(--card); color: var(--text); transition: all 0.15s; }
        .btn:hover { border-color: var(--accent); color: var(--text-hi); }
        .btn-hi { border-color: var(--accent); color: var(--accent); background: var(--accent-bg); }
        .btn-hi:hover { background: var(--accent); color: var(--bg); }

        /* ── ABOUT BIO ── */
        .about-bio {
            max-width: 980px; margin: 0 auto;
            padding: 0 20px 32px;
            font-size: 13px; color: var(--text); line-height: 1.8;
        }
        .about-bio p { max-width: 640px; }
        .about-bio strong { color: var(--text-hi); font-weight: 500; }

        /* ── BODY GRID ── */
        hr.sep { border: none; border-top: 1px solid var(--border); }
        .grid { display: grid; grid-template-columns: 220px 1fr; }
        @media (max-width: 680px) { .grid { grid-template-columns: 1fr; } .sb { border-right: none; border-bottom: 1px solid var(--border); } }

        /* ── SIDEBAR ── */
        .sb { border-right: 1px solid var(--border); padding: 28px 20px; }
        .sg + .sg { margin-top: 26px; }
        .slabel { font-family: 'IBM Plex Mono', monospace; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: var(--text-dim); display: block; margin-bottom: 10px; }
        
        .tags { display: flex; flex-wrap: wrap; gap: 5px; }
        .tag { font-size: 11px; padding: 3px 9px; border-radius: 5px; border: 1px solid var(--border); color: var(--text); transition: all 0.15s; }
        [data-theme="dark"] .tag { font-family: 'IBM Plex Mono', monospace; font-size: 10.5px; }
        .tag:hover { border-color: var(--accent); color: var(--accent); }

        .clients { display: flex; flex-direction: column; gap: 10px; }
        .cl { display: flex; gap: 9px; align-items: flex-start; }
        .cl-ic { width: 26px; height: 26px; border: 1px solid var(--border); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 11px; color: var(--text-dim); flex-shrink: 0; transition: border-color 0.15s, color 0.15s; }
        .cl:hover .cl-ic { border-color: var(--accent); color: var(--accent); }
        .cl-name { font-size: 12px; font-weight: 500; color: var(--text-hi); text-decoration: none; display: block; line-height: 1.25; transition: color 0.15s; }
        .cl-name:hover { color: var(--accent); }
        .cl-loc { font-size: 11px; color: var(--text-dim); margin-top: 1px; }

        /* ── RIGHT CONTENT ── */
        .rc { padding: 28px 28px; }
        @media (max-width: 460px) { .rc { padding: 22px 16px; } }
        .sec + .sec { margin-top: 36px; }
        .sh { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .sh h2 { font-size: 11px; font-weight: 600; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.1em; white-space: nowrap; }
        .sh-line { flex: 1; height: 1px; background: var(--border); }

        /* ── TIMELINE ── */
        .tl { position: relative; }
        .ti { position: relative; padding-left: 18px; padding-bottom: 24px; }
        .ti:last-child { padding-bottom: 0; }
        .ti::before { content: ''; position: absolute; left: 4px; top: 10px; bottom: 0; width: 1px; background: var(--border); }
        .ti:last-child::before { display: none; }
        .ti-dot { position: absolute; left: 0; top: 5px; width: 10px; height: 10px; border-radius: 50%; border: 1px solid var(--border-s); background: var(--card); }
        .ti-dot.on { background: var(--accent); border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-bg); }

        .ti-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; flex-wrap: wrap; }
        .ti-title { font-size: 14px; font-weight: 600; color: var(--text-head); line-height: 1.2; }
        .ti-yr { font-size: 11px; color: var(--text-dim); white-space: nowrap; font-family: 'IBM Plex Mono', monospace; }
        .ti-co { font-size: 12px; color: var(--accent); font-weight: 500; margin: 3px 0 8px; }

        details.det > summary { list-style: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text-dim); transition: color 0.15s; padding: 2px 0; user-select: none; }
        details.det > summary::-webkit-details-marker { display: none; }
        details.det > summary:hover { color: var(--text); }
        details.det > summary .cv { font-size: 9px; transition: transform 0.2s; }
        details.det[open] > summary .cv { transform: rotate(90deg); }

        .buls { margin-top: 9px; display: flex; flex-direction: column; gap: 6px; }
        .bul { display: flex; gap: 8px; font-size: 13px; color: var(--text); line-height: 1.55; align-items: flex-start; }
        .bd { width: 4px; height: 4px; border-radius: 50%; background: var(--accent); flex-shrink: 0; margin-top: 7px; }

        /* ── GRID CARDS (PROJECTS & VIDEOS) ── */
        .pg { display: grid; grid-template-columns: repeat(auto-fill, minmax(185px, 1fr)); gap: 10px; }
        @media (max-width: 380px) { .pg { grid-template-columns: 1fr; } }
        
        .pg-vid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
        @media (max-width: 480px) { .pg-vid { grid-template-columns: 1fr; } }

        .pc { border: 1px solid var(--border); border-radius: 10px; padding: 16px; text-decoration: none; display: block; background: var(--card); transition: border-color 0.18s, background 0.18s; }
        a.pc:hover { border-color: var(--accent); background: var(--card-h); }
        
        .pi { font-size: 16px; color: var(--accent); margin-bottom: 10px; display: block; }
        .pt { font-size: 13px; font-weight: 600; color: var(--text-head); margin-bottom: 4px; line-height: 1.3; }
        .pd { font-size: 12px; color: var(--text-dim); line-height: 1.55; }

        .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 6px; margin-bottom: 12px; border: 1px solid var(--border-s); background: #000; }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; }

        .btn-wa { border-color: #25d366; color: #25d366; background: rgba(37,211,102,0.07); }
        .btn-wa:hover { background: #25d366; color: #fff; border-color: #25d366; }

        /* ── BACK TO TOP ── */
        .btt {
            position: fixed; bottom: 24px; right: 18px; z-index: 199;
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--card); border: 1px solid var(--border-s);
            color: var(--text); font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: var(--shadow); transition: all 0.2s;
            opacity: 0; pointer-events: none;
        }
        .btt.show { opacity: 1; pointer-events: auto; }
        .btt:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-2px); }

        /* ── SKELETON LOADING ── */
        .skeleton {
            background: linear-gradient(90deg, var(--border) 25%, var(--border-s) 50%, var(--border) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.4s infinite;
            border-radius: 6px;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
        .sk-line { height: 12px; margin-bottom: 8px; }
        .sk-line.wide { width: 80%; }
        .sk-line.mid  { width: 55%; }
        .sk-line.slim { width: 35%; }
        .sk-block { height: 60px; margin-bottom: 16px; }
        #skeleton-exp, #skeleton-proj { display: none; }

        /* ── PWA manifest link added in head ── */
        footer { text-align: center; padding: 36px 20px 24px; font-size: 12px; color: var(--text-dim); border-top: 1px solid var(--border); margin-top: 56px; }
        footer a { color: var(--accent); text-decoration: none; }
        
        .lb { display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.82); align-items: center; justify-content: center; }
        .lb.open { display: flex; animation: lbIn 0.2s ease; }
        @keyframes lbIn { from{opacity:0} to{opacity:1} }
        .lb-img { max-width: min(480px, 90vw); max-height: 85vh; border-radius: 14px; object-fit: cover; box-shadow: 0 24px 64px rgba(0,0,0,0.7); animation: lbUp 0.22s ease; }
        @keyframes lbUp { from{transform:scale(0.93);opacity:0} to{transform:scale(1);opacity:1} }
        .lb-close { position: absolute; top: 18px; right: 22px; font-size: 28px; color: #fff; cursor: pointer; opacity: 0.65; transition: opacity 0.15s; border: none; background: none; line-height: 1; }
        .lb-close:hover { opacity: 1; }

        /* fade-in */
        @keyframes fu { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:none} }
        .f  { animation: fu 0.4s ease both; }
        .d1 { animation-delay: 0.05s; }
        .d2 { animation-delay: 0.13s; }
        .d3 { animation-delay: 0.21s; }
    </style>
</head>
<body>

<button class="toggle" onclick="toggleTheme()" aria-label="Toggle dark/light mode">
    <i class="fas fa-circle-half-stroke" aria-hidden="true"></i>
    <span id="tlbl">Light</span>
</button>

<header class="f">
    <div class="hd">
        <div class="av">
            <img src="<?= $foto ?>" alt="Foto profil <?= $nama ?>" width="120" height="120" loading="eager" onclick="document.getElementById('lb').classList.add('open')">
            <div class="online" title="Tersedia untuk proyek"></div>
        </div>
        <div class="ht">
            <span class="pre">// <?= $email ?></span>
            <h1><?= $nama ?></h1>
            <p class="sub"><?= $tagline ?></p>
            <div class="avail">
                <span class="avail-dot"></span>
                <?= $status ?>
            </div>
            <div class="btns">
                <a href="<?= $github ?>" target="_blank" rel="noopener" class="btn">
                    <i class="fab fa-github"></i> GitHub
                </a>
                <a href="<?= $linkedin ?>" target="_blank" rel="noopener" class="btn">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </a>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $profil['whatsapp'] ?? '') ?>" target="_blank" rel="noopener" class="btn btn-wa">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="mailto:<?= $email ?>" class="btn btn-hi">
                    <i class="fas fa-paper-plane"></i> Hire Me
                </a>
            </div>
        </div>
    </div>
</header>

<div class="about-bio f d1">
    <p>Saya seorang <strong>IT Support & Full-stack Developer</strong> berbasis di Jawa Tengah, dengan pengalaman menangani infrastruktur jaringan, server, dan pengembangan aplikasi web untuk instansi pemerintah maupun swasta. Fokus utama saya adalah memberikan solusi teknologi yang reliabel, efisien, dan tepat sasaran — mulai dari setup jaringan enterprise hingga membangun sistem berbasis web dari nol.</p>
</div>

<hr class="sep">

<div class="page">
    <div class="grid">

        <aside class="sb f d2">
            <?php foreach ($skills_raw as $group_name => $skill_list): ?>
            <div class="sg">
                <span class="slabel"><?= htmlspecialchars($group_name) ?></span>
                <div class="tags">
                    <?php foreach ($skill_list as $skill): ?>
                        <span class="tag"><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="sg">
                <span class="slabel">Clients</span>
                <div class="clients">
                    <?php if (!empty($clients)): ?>
                        <?php foreach ($clients as $c): ?>
                        <div class="cl">
                            <div class="cl-ic"><i class="<?= htmlspecialchars($c['icon_class']) ?>"></i></div>
                            <div>
                                <?php if (!empty($c['url'])): ?>
                                    <a class="cl-name" href="<?= htmlspecialchars($c['url']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($c['name']) ?></a>
                                <?php else: ?>
                                    <span class="cl-name" style="cursor:default"><?= htmlspecialchars($c['name']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($c['location'])): ?>
                                    <div class="cl-loc"><?= htmlspecialchars($c['location']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback hardcoded jika tabel clients belum ada -->
                        <div class="cl"><div class="cl-ic"><i class="fas fa-landmark"></i></div><div><a class="cl-name" href="https://bpkad.pekalongankota.go.id/" target="_blank" rel="noopener">Badan Keuangan Daerah</a><div class="cl-loc">Kota Pekalongan</div></div></div>
                        <div class="cl"><div class="cl-ic"><i class="fas fa-clinic-medical"></i></div><div><span class="cl-name" style="cursor:default">Klinik Kukuh Subekti</span><div class="cl-loc">Comal, Pemalang</div></div></div>
                        <div class="cl"><div class="cl-ic"><i class="fas fa-industry"></i></div><div><span class="cl-name" style="cursor:default">PT Duta Albasy</span><div class="cl-loc">Kajen, Kab. Pekalongan</div></div></div>
                        <div class="cl"><div class="cl-ic"><i class="fas fa-hospital"></i></div><div><a class="cl-name" href="https://puskeskaranganyar.karanganyarkab.go.id/" target="_blank" rel="noopener">Puskesmas Karanganyar</a><div class="cl-loc">Kab. Pekalongan</div></div></div>
                        <div class="cl"><div class="cl-ic"><i class="fas fa-hospital-alt"></i></div><div><a class="cl-name" href="https://rsudkajen.pekalongankab.go.id/" target="_blank" rel="noopener">RSUD Kajen</a><div class="cl-loc">Kab. Pekalongan</div></div></div>
                        <div class="cl"><div class="cl-ic"><i class="fas fa-tshirt"></i></div><div><a class="cl-name" href="https://www.behaestex.co.id/" target="_blank" rel="noopener">PT Behaestex</a><div class="cl-loc">Wonopringgo, Kab. Pekalongan</div></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <main class="rc f d3">

            <section class="sec" aria-labelledby="exp-h">
                <div class="sh">
                    <h2 id="exp-h">Experience</h2>
                    <div class="sh-line"></div>
                </div>
                <div class="tl">
                    <?php if (empty($experiences)): ?>
                        <p class="text-dim">Belum ada riwayat kerja.</p>
                    <?php else: ?>
                        <?php foreach ($experiences as $exp): ?>
                        <article class="ti">
                            <div class="ti-dot <?= $exp['is_active'] ? 'on' : '' ?>"></div>
                            <div class="ti-row">
                                <div class="ti-title"><?= htmlspecialchars($exp['job_title']) ?></div>
                                <div class="ti-yr"><?= htmlspecialchars($exp['year_range']) ?></div>
                            </div>
                            <div class="ti-co"><?= htmlspecialchars($exp['company']) ?></div>
                            
                            <?php if (!$exp['is_active']): ?>
                                <details class="det">
                                    <summary><i class="fas fa-chevron-right cv"></i> Lihat detail</summary>
                            <?php endif; ?>
                            
                            <div class="buls">
                                <?php 
                                $bullets = explode("\n", trim($exp['description']));
                                foreach ($bullets as $bullet):
                                    if (trim($bullet) !== ''):
                                ?>
                                    <div class="bul"><div class="bd"></div><span><?= htmlspecialchars(trim($bullet)) ?></span></div>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>

                            <?php if (!$exp['is_active']): ?>
                                </details>
                            <?php endif; ?>
                        </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <section class="sec" aria-labelledby="proj-h">
                <div class="sh">
                    <h2 id="proj-h">Projects</h2>
                    <div class="sh-line"></div>
                </div>
                <div class="pg">
                    <?php if (empty($projects)): ?>
                        <p class="text-dim">Belum ada project.</p>
                    <?php else: ?>
                        <?php foreach ($projects as $proj): ?>
                            <?php if (!empty($proj['link_url'])): ?>
                                <a href="<?= htmlspecialchars($proj['link_url']) ?>" target="_blank" rel="noopener" class="pc">
                            <?php else: ?>
                                <div class="pc" style="cursor: default;">
                            <?php endif; ?>

                                <i class="<?= htmlspecialchars($proj['icon_class']) ?> pi" aria-hidden="true"></i>
                                <div class="pt"><?= htmlspecialchars($proj['title']) ?></div>
                                <p class="pd"><?= htmlspecialchars($proj['description']) ?></p>

                            <?php if (!empty($proj['link_url'])): ?>
                                </a>
                            <?php else: ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            

        </main>
    </div>
</div>

<button class="btt" id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Kembali ke atas">
    <i class="fas fa-chevron-up"></i>
</button>

<footer>
    <p>© <?= date('Y') ?> <?= $nama ?> · <a href="mailto:<?= $email ?>"><?= $email ?></a></p>
</footer>

<div class="lb" id="lb" onclick="this.classList.remove('open')">
    <button class="lb-close" onclick="document.getElementById('lb').classList.remove('open')" aria-label="Tutup foto">×</button>
    <img class="lb-img" src="<?= $foto ?>" alt="Foto <?= $nama ?>" onclick="event.stopPropagation()">
</div>

<script>
    const html = document.documentElement;
    const lbl  = document.getElementById('tlbl');

    function setTheme(t) {
        html.setAttribute('data-theme', t);
        lbl.textContent = t === 'dark' ? 'Light' : 'Dark';
        try { localStorage.setItem('theme', t); } catch(e){}
    }
    function toggleTheme() {
        setTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    }

    // Back to top
    const btt = document.getElementById('btt');
    window.addEventListener('scroll', () => {
        btt.classList.toggle('show', window.scrollY > 300);
    }, { passive: true });

    // PWA install
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
    });

    try {
        const s = localStorage.getItem('theme');
        if (s) setTheme(s);
        else if (window.matchMedia('(prefers-color-scheme: light)').matches) setTheme('light');
    } catch(e) {}
</script>
</body>
</html>