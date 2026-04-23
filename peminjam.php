<?php
session_start();
include 'koneksi.php';

// CEK KEAMANAN: Cuma Admin yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// --- LOGIKA HAPUS DATA ---
if (isset($_GET['op']) && $_GET['op'] == 'delete') {
    $id = $_GET['id'];

    // Cek dulu apakah anggota ini masih punya pinjaman?
    $cek_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE nim_peminjam = '$id'");
    
    if (mysqli_num_rows($cek_pinjam) > 0) {
        // Jika masih ada riwayat transaksi, tolak hapus (Demi keamanan data)
        echo "<script>alert('GAGAL HAPUS! Anggota ini masih memiliki riwayat peminjaman buku.'); window.location='peminjam.php';</script>";
    } else {
        // Jika bersih, baru boleh dihapus
        $query_hapus = "DELETE FROM peminjam WHERE nim_peminjam = '$id'";
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script>alert('Data Anggota Berhasil Dihapus!'); window.location='peminjam.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data.'); window.location='peminjam.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anggota - Sistem Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Gaya Tampilan Standar */
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .header { background-color: #003366; color: white; padding: 20px; text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .container { width: 90%; max-width: 1000px; margin: 0 auto 50px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .btn-back { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        tr:hover { background-color: #f1f1f1; }

        /* Tombol Hapus */
        .btn-hapus { 
            background-color: #dc3545; color: white; padding: 5px 10px; 
            text-decoration: none; border-radius: 5px; font-size: 13px; font-weight: bold; 
        }
        .btn-hapus:hover { background-color: #c82333; }
        .logo-kampus {
            position: absolute; /* Agar bisa ditaruh bebas di pojok */
            left: 30px;         /* Jarak dari sisi kiri */
            top: 50%;           /* Posisi vertikal di tengah header */
            transform: translateY(-50%); /* Trik agar pas di tengah vertikal */
            width: 150px;        /* Ukuran lebar logo (sesuaikan jika kurang besar) */
            height: auto;       /* Tinggi otomatis mengikuti lebar */
        }

        /* Pastikan header punya posisi relative agar logo menempel di dalamnya */
        .header { 
            background-color: #003366; 
            color: white; 
            padding: 20px; 
            text-align: center; 
            position: relative; /* WAJIB ADA */
        }

        /* Penyesuaian agar judul tidak tertabrak logo di layar HP kecil */
        @media (max-width: 600px) {
            .logo-kampus {
                width: 50px;
                left: 15px;
                top: 20px;
                transform: none;
            }
            .header h1 {
                font-size: 18px;
                margin-top: 40px; /* Beri jarak agar tidak tertutup logo */
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="logo.png" alt="Logo UM" class="logo-kampus">
        <h1>SISTEM INFORMASI PERPUSTAKAAN</h1>
        <p>Peminjaman Buku Perpustakaan Universitas Negeri Malang</p>
    </div>

    <div class="container">
        <a href="index.php" class="btn-back">⬅ Kembali ke Dashboard</a>

        <h2 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Daftar Anggota Terdaftar</h2>
        
        <p style="color: #666; font-size: 14px;">
            Data profil dikelola mandiri oleh mahasiswa. Admin memiliki hak akses untuk menghapus akun jika diperlukan.
        </p>

        <table>
            <tr>
                <th>NIM</th>
                <th>Nama Lengkap</th>
                <th>Fakultas</th>
                <th>Program Studi</th>
                <th width="10%">Aksi</th> </tr>
            <?php
            $tampil = mysqli_query($koneksi, "SELECT * FROM peminjam ORDER BY nim_peminjam ASC");
            while ($data = mysqli_fetch_array($tampil)) {
                echo "<tr>";
                echo "<td><b>" . $data['nim_peminjam'] . "</b></td>";
                echo "<td>" . $data['nama_peminjam'] . "</td>";
                echo "<td>" . $data['fakultas_peminjam'] . "</td>";
                echo "<td>" . $data['prodi'] . "</td>";
                
                // TOMBOL HAPUS DENGAN KONFIRMASI
                echo "<td>
                        <a href='peminjam.php?op=delete&id=" . $data['nim_peminjam'] . "' 
                           class='btn-hapus' 
                           onclick=\"return confirm('PERINGATAN: Menghapus anggota ini akan menghilangkan akses login mereka. Yakin hapus?');\">
                           Hapus
                        </a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>