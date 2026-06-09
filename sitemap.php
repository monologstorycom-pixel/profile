<?php
require 'admin/koneksi.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$baseUrl = 'https://rsby.my.id';
$today   = date('Y-m-d');

$urls = [
    [
        'loc'        => $baseUrl . '/',
        'lastmod'    => $today,
        'changefreq' => 'weekly',
        'priority'   => '1.0',
    ],
    [
        'loc'        => $baseUrl . '/slws/',
        'lastmod'    => $today,
        'changefreq' => 'weekly',
        'priority'   => '0.8',
    ],
];

// Project URL eksternal (hanya yang valid dan https)
try {
    $stmt = $pdo->query("SELECT link_url, COALESCE(updated_at, created_at) AS mod_at
                         FROM projects
                         WHERE link_url IS NOT NULL AND link_url != ''
                         ORDER BY id DESC");
    foreach ($stmt->fetchAll() as $proj) {
        $u = $proj['link_url'];
        if (!filter_var($u, FILTER_VALIDATE_URL)) continue;
        if (!preg_match('/^https?:\/\//i', $u)) continue;
        $urls[] = [
            'loc'        => htmlspecialchars($u, ENT_XML1),
            'lastmod'    => !empty($proj['mod_at']) ? date('Y-m-d', strtotime($proj['mod_at'])) : $today,
            'changefreq' => 'monthly',
            'priority'   => '0.6',
        ];
    }
} catch (Exception $e) {}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo "    <loc>{$u['loc']}</loc>\n";
    echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
    echo "    <priority>{$u['priority']}</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
