<?php
/**
 * slws/api_photos.php
 * Endpoint ringan — kembalikan foto milik 1 kategori sebagai JSON.
 * Dipanggil fetch() saat user klik folder, bukan saat halaman load.
 *
 * GET ?cat=wedding          → foto kategori "wedding"
 * GET ?cat=wedding&cover=1  → hanya 1 foto (untuk cover folder grid)
 */

require '../admin/koneksi.php';

header('Content-Type: application/json; charset=utf-8');
// Cache 5 menit di browser — foto tidak berubah terlalu sering
header('Cache-Control: public, max-age=300');

$cat   = trim($_GET['cat'] ?? '');
$cover = isset($_GET['cover']) && $_GET['cover'] == '1';

if ($cat === '') {
    echo json_encode(['ok' => false, 'msg' => 'cat required']);
    exit;
}

// Sanitasi: hanya huruf, angka, strip/dash
if (!preg_match('/^[a-z0-9\-_]+$/i', $cat)) {
    echo json_encode(['ok' => false, 'msg' => 'invalid cat']);
    exit;
}

$limit = $cover ? 1 : 9999;
$stmt  = $pdo->prepare(
    "SELECT image_path FROM slws_photos
     WHERE category_id = ?
     ORDER BY id DESC
     LIMIT ?"
);
$stmt->bindValue(1, $cat);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Tambahkan prefix ../ agar path valid dari folder /slws/
$urls = array_map(fn($p) => '../' . $p, $rows);

echo json_encode(['ok' => true, 'cat' => $cat, 'photos' => $urls]);