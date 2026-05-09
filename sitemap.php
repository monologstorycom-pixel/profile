<?php
require 'admin/koneksi.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$baseUrl = 'https://rsby.my.id';
$today   = date('Y-m-d');

$urls = [];

// Halaman utama
$urls[] = [
    'loc'        => $baseUrl . '/',
    'lastmod'    => $today,
    'changefreq' => 'weekly',
    'priority'   => '1.0',
];

// Tambahkan project jika punya URL publik
try {
    $stmtProj = $pdo->query("SELECT link_url, updated_at FROM projects WHERE link_url IS NOT NULL AND link_url != '' ORDER BY id DESC");
    foreach ($stmtProj->fetchAll() as $proj) {
        $urls[] = [
            'loc'        => htmlspecialchars($proj['link_url']),
            'lastmod'    => isset($proj['updated_at']) ? date('Y-m-d', strtotime($proj['updated_at'])) : $today,
            'changefreq' => 'monthly',
            'priority'   => '0.6',
        ];
    }
} catch (Exception $e) {}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $url) {
    echo "  <url>\n";
    echo "    <loc>{$url['loc']}</loc>\n";
    echo "    <lastmod>{$url['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$url['changefreq']}</changefreq>\n";
    echo "    <priority>{$url['priority']}</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';