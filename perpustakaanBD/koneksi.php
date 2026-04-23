<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "log perpustakaan"; // Sesuaikan dengan nama database yang Anda buat tadi

// Melakukan koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek jika koneksi gagal
if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>