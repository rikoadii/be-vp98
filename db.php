<?php
// Konfigurasi database
$host = 'https://admin.victoryproduction98.com';
$dbname = 'u967345369_db_98';
$username = 'u967345369_victoryproduct';
$password = '.k8cB$@AnBPHBZU';

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
