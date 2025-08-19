<?php
// Konfigurasi database
$host = 'localhost';
$dbname = 'vp98';
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

// echo "Koneksi database berhasil!";

// Fungsi untuk mendapatkan koneksi database
function getConnection() {
    global $conn;
    return $conn;
}
?>