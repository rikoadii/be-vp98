<?php
// Konfigurasi database
$host = 'localhost'; // atau '127.0.0.1'
$dbname = 'u967345369_db_98';
$username = 'u967345369_victoryproduct';
$password = '.k8cB$@AnBPHBZU';

// Membuat koneksi MySQLi
$conn = mysqli_connect($host, $username, $password, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
} else {
    echo "Koneksi database berhasil!";
}

// Set charset
mysqli_set_charset($conn, "utf8");

// Fungsi untuk mendapatkan koneksi database
function getConnection() {
    global $conn;
    return $conn;
}
?>
