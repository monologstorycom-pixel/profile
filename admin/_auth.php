<?php
/**
 * _auth.php — Auth gate, CSRF helper, dan rate limiter.
 * Wajib di-require PALING ATAS di setiap halaman admin yang butuh proteksi
 * (sebelum ada handler POST/GET yang melakukan perubahan).
 *
 * Contoh:
 *   require 'koneksi.php';
 *   require '_auth.php';        // <- gate auth + CSRF check
 *   ... handler POST/GET ...
 *   ... require '_layout.php';  // <- baru render UI
 */

// koneksi.php sudah memanggil session_start() jika perlu.
// Pastikan session aktif.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ─────────────────────────────────────────────
   1. AUTH GATE
   Jika belum login → redirect ke login.php
───────────────────────────────────────────── */
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

/* ─────────────────────────────────────────────
   2. SESSION SECURITY
   - Idle timeout 2 jam
   - Regenerate ID berkala (cegah session fixation)
───────────────────────────────────────────── */
$IDLE_LIMIT = 60 * 60 * 2; // 2 jam
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $IDLE_LIMIT) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['regen_at']) || (time() - $_SESSION['regen_at']) > 1800) {
    session_regenerate_id(true);
    $_SESSION['regen_at'] = time();
}

/* ─────────────────────────────────────────────
   3. CSRF TOKEN
   - csrf_token()      → ambil/generate token
   - csrf_field()      → render hidden input
   - csrf_check()      → validasi POST (auto exit jika gagal)
───────────────────────────────────────────── */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $tok = $_POST['_csrf'] ?? '';
    if (!is_string($tok) || !hash_equals($_SESSION['csrf_token'] ?? '', $tok)) {
        http_response_code(419);
        die('CSRF token invalid. Silakan refresh halaman dan coba lagi.');
    }
}

// Validasi POST otomatis jika request POST datang
csrf_check();

/* ─────────────────────────────────────────────
   3b. CSRF UNTUK GET (delete via link)
───────────────────────────────────────────── */
function csrf_check_get(): void {
    $tok = $_GET['_csrf'] ?? '';
    if (!is_string($tok) || !hash_equals($_SESSION['csrf_token'] ?? '', $tok)) {
        http_response_code(419);
        die('CSRF token invalid. Refresh halaman dan coba lagi.');
    }
}

function csrf_qs(): string {
    return '_csrf=' . urlencode(csrf_token());
}

/* ─────────────────────────────────────────────
   4. ESCAPE HELPER (singkat)
───────────────────────────────────────────── */
if (!function_exists('e')) {
    function e($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/* ─────────────────────────────────────────────
   5. ACTIVITY LOG
   Catat aksi admin (tambah/edit/hapus) ke tabel activity_log.
   Aman dipanggil walau tabel belum ada (gagal tanpa error).
   - $action : verba aksi, cth "Menambah", "Mengedit", "Menghapus"
   - $entity : jenis data, cth "Project", "Skill", "Client"
   - $note   : keterangan, cth judul/nama item
───────────────────────────────────────────── */
if (!function_exists('log_activity')) {
    function log_activity(PDO $pdo, string $action, string $entity = '', $entity_id = null, string $note = ''): void {
        try {
            $cut = static fn(string $s, int $n): string =>
                function_exists('mb_substr') ? mb_substr($s, 0, $n) : substr($s, 0, $n);
            $pdo->prepare(
                "INSERT INTO activity_log (action, entity, entity_id, note) VALUES (?,?,?,?)"
            )->execute([
                $cut($action, 100),
                $cut($entity, 80),
                $entity_id !== null ? $cut((string)$entity_id, 80) : null,
                $cut($note, 255),
            ]);
        } catch (Throwable $e) {
            // Diam saja — logging tidak boleh mengganggu operasi utama.
        }
    }
}
