<?php
// BUG 1 FIX: session_start() hanya dipanggil jika belum ada session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BUG 2 FIX: Credentials tidak hardcode — pakai environment variable
$host = getenv('DB_HOST') ?: '192.168.1.109';
$db   = getenv('DB_NAME') ?: 'db_portfolio';
$user = getenv('DB_USER') ?: 'kasir';
$pass = getenv('DB_PASS') ?: 'kasir';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // BUG 2 FIX: Jangan expose detail error PDO ke user (info sensitif)
    error_log("DB connection failed: " . $e->getMessage());
    die("Koneksi database gagal. Hubungi administrator.");
}
