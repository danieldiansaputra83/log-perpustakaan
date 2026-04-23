<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    exit("Akses Ditolak");
}

$nim = $_SESSION['id'];
$id_buku = $_GET['id_buku'];
$tanggal = date('Y-m-d');

// 1. CARI STOK
$cari_copy = mysqli_query($koneksi, "SELECT id_copy FROM copy_buku WHERE id_buku = '$id_buku' AND status = 'Tersedia' LIMIT 1");

if (mysqli_num_rows($cari_copy) > 0) {
    $data_copy = mysqli_fetch_array($cari_copy);
    $id_copy_tersedia = $data_copy['id_copy'];

    // 2. PROSES PINJAM
    $insert = mysqli_query($koneksi, "INSERT INTO peminjaman (tanggal_pinjam, nim_peminjam, id_copy, status_transaksi) 
                                      VALUES ('$tanggal', '$nim', '$id_copy_tersedia', 'Berjalan')");
    
    $update = mysqli_query($koneksi, "UPDATE copy_buku SET status = 'Dipinjam' WHERE id_copy = '$id_copy_tersedia'");

    if ($insert && $update) {
        // SUKSES: Alihkan balik ke index dengan sinyal "pesan=sukses"
        header("Location: index.php?pesan=sukses");
    } else {
        header("Location: index.php?pesan=gagal");
    }

} else {
    // GAGAL (HABIS): Alihkan balik dengan sinyal "pesan=habis"
    header("Location: index.php?pesan=habis");
}
?>