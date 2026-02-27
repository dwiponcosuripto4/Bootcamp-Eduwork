<?php
// Prosedural
$host = "localhost";
$username = "root";
$password = "galaxys23";
$database = "db_sesi8";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
// echo "Koneksi berhasil";
