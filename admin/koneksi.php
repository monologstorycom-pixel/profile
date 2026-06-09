<?php
// session_start() hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    // Hardening cookie
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Credentials lokal user. ENV var di-prioritaskan jika di-set.
$host = getenv('DB_HOST') ?: '192.168.1.109';
$db   = getenv('DB_NAME') ?: 'db_portfolio';
$user = getenv('DB_USER') ?: 'kasir';
$pass = getenv('DB_PASS') ?: 'kasir';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Koneksi database gagal. Hubungi administrator.');
}
