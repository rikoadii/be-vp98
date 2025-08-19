<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

// ---- GANTI DI SINI SESUAI HPanel > MySQL Databases ----
$host = 'localhost';   // atau 'localhost'
$db   = 'u967345369_db_v98';
$user = 'u967345369_victoryproduct';
$pass = '.k8cB$@AnBPHBZU';
$port = 3306;
// -------------------------------------------------------

echo "<pre>";
echo "Testing MySQL connection to $host:$port ...\n";

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    echo "KONEKSI GAGAL\n";
    echo "mysqli_connect_errno: " . mysqli_connect_errno() . "\n";
    echo "mysqli_connect_error: " . mysqli_connect_error() . "\n";
    exit;
}

echo "KONEKSI OK ✅\n";
echo "Charset: " . mysqli_character_set_name($conn) . "\n\n";

// Coba list tabel (untuk memastikan DB benar)
$res = mysqli_query($conn, "SHOW TABLES");
if ($res === false) {
    echo "Query SHOW TABLES error: " . mysqli_error($conn) . "\n";
    exit;
}
echo "Tables:\n";
while ($row = mysqli_fetch_row($res)) {
    echo " - " . $row[0] . "\n";
}
echo "</pre>";
