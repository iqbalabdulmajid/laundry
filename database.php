<?php
$host = 'localhost';
$dbname = 'londri';
$username = 'root';
$password = '';

try {
    // Membuat koneksi ke database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Mengatur mode error agar menampilkan exception jika terjadi kesalahan
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Menghentikan eksekusi dan menampilkan pesan error jika koneksi gagal
    die('Database connection failed: ' . $e->getMessage());
}
