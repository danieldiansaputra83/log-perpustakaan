<?php
// HAPUS session_start(); DISINI KARENA SUDAH ADA DI index.php
// include 'koneksi.php'; // Koneksi juga biasanya sudah terbuka, tapi biar aman kita include lagi dengan "include_once"
include_once 'koneksi.php';

// --- LOGIKA DASHBOARD ---
$sql_buku = mysqli_query($koneksi, "SELECT * FROM buku");
$jml_buku = mysqli_num_rows($sql_buku);

$sql_stok = mysqli_query($koneksi, "SELECT * FROM copy_buku WHERE status = 'Tersedia'");
$jml_stok = mysqli_num_rows($sql_stok);

$sql_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE status_transaksi = 'Berjalan'");
$jml_pinjam = mysqli_num_rows($sql_pinjam);

$sql_mhs = mysqli_query($koneksi, "SELECT * FROM peminjam");
$jml_mhs = mysqli_num_rows($sql_mhs);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan UM</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        
        .header { background-color: #003366; color: white; padding: 20px; text-align: center; position: relative; }
        .header h1 { margin: 0; font-size: 24px; font-family: 'Poppins', sans-serif; font-weight: 600; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.8; }

        .container { width: 90%; max-width: 1000px; margin: 30px auto; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; border-bottom: 4px solid #ddd; }
        .card h3 { margin: 0; font-size: 36px; color: #333; font-family: 'Poppins', sans-serif; }
        .card p { margin: 5px 0 0; color: #777; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        
        .c-blue { border-bottom-color: #007bff; }
        .c-green { border-bottom-color: #28a745; }
        .c-orange { border-bottom-color: #ffc107; }
        .c-red { border-bottom-color: #dc3545; }

        .menu-section { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        
        .menu-title { 
            font-family: 'Poppins', sans-serif; 
            font-size: 18px; font-weight: 500; color: #444; margin-bottom: 20px; 
            border-bottom: 2px solid #eee; padding-bottom: 10px; letter-spacing: 0.5px;
        }
        
        .btn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }

        .btn { 
            display: flex; justify-content: center; align-items: center; gap: 10px;
            padding: 15px; text-align: center; background: #e9ecef; color: #333; 
            text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s;
        }
        .btn:hover { background: #003366; color: white; transform: translateY(-2px); }
        .group { margin-bottom: 30px; }

        /* Style Tombol Logout */
        .btn-auth {
            position: absolute; top: 20px; right: 20px;
            padding: 8px 20px; text-decoration: none; border-radius: 5px;
            font-size: 14px; font-weight: bold; font-family: 'Poppins', sans-serif;
            transition: 0.3s;
            background: #dc3545; color: white; /* Merah Logout */
        }
        .btn-auth:hover { background: #c82333; }
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
        <a href="logout.php" class="btn-auth">
            Logout ➜
        </a>
    </div>

    <div class="container">
        
        <div class="stats-grid">
            <div class="card c-blue">
                <h3><?php echo $jml_buku; ?></h3>
                <p>Judul Buku</p>
            </div>
            <div class="card c-green">
                <h3><?php echo $jml_stok; ?></h3>
                <p>Stok Tersedia</p>
            </div>
            <div class="card c-orange">
                <h3><?php echo $jml_pinjam; ?></h3>
                <p>Sedang Dipinjam</p>
            </div>
            <div class="card c-red">
                <h3><?php echo $jml_mhs; ?></h3>
                <p>Anggota Terdaftar</p>
            </div>
        </div>

        <div class="menu-section group">
            <div class="menu-title">📂 Data Master & Inventaris</div>
            <div class="btn-grid">
                <a href="pengarang.php" class="btn">1. Data Pengarang</a>
                <a href="buku.php" class="btn">2. Data Buku (Judul)</a>
                <a href="copy.php" class="btn">3. Stok Fisik (Copy)</a>
                <a href="peminjam.php" class="btn">4. Data Anggota</a>
            </div>
        </div>

        <div class="menu-section">
            <div class="menu-title">🔄 Transaksi Sirkulasi</div>
            <div class="btn-grid">
                <a href="transaksi_peminjaman.php" class="btn" style="background-color: #d4edda; color: #155724;">
                    ➕ Peminjaman Baru
                </a>
                <a href="pengembalian.php" class="btn" style="background-color: #fff3cd; color: #856404;">
                    ↩️ Pengembalian & Denda
                </a>
            </div>
        </div>

    </div>

    <div style="text-align: center; padding: 20px; color: #aaa; font-size: 12px;">
        &copy; 2025@Kelompok1BasDar. All Rights Reserved.
    </div>

</body>
</html>