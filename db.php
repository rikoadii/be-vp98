<?php
// Konfigurasi database
$host = 'localhost'; // atau '127.0.0.1'
$dbname = 'db_v98';
$username = 'root';
$password = '';

// Membuat koneksi MySQLi
$conn = mysqli_connect($host, $username, $password, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Fungsi untuk mendapatkan koneksi database
function getConnection() {
    global $conn;
    return $conn;
}
?>
