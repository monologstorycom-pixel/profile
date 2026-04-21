<?php
require 'koneksi.php';

$username = 'admin';
$password = 'admin123'; // Ini password yang akan kamu pakai
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
if ($stmt->execute([$username, $hashed_password])) {
    echo "User admin berhasil dibuat! Username: admin | Password: admin123 <br>";
    echo "Silakan hapus file ini dan buka <a href='login.php'>Halaman Login</a>";
} else {
    echo "Gagal membuat user.";
}
?>