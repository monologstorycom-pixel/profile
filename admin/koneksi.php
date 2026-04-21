<?php
session_start(); // Mulai session untuk login

$host = '192.168.1.109';
$db   = 'db_portfolio'; // Sesuaikan dengan nama database kamu
$user = 'kasir';         // Sesuaikan username database (biasanya root)
$pass = 'kasir';             // Sesuaikan password database (biasanya kosong di XAMPP/Laragon)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>