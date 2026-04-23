<?php
session_start();
include 'koneksi.php';

// CEK KEAMANAN: Cuma Admin yang boleh masuk sini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Inisialisasi variabel pop-up
$popup_status = "";
$popup_pesan = "";

// --- LOGIKA PEMINJAMAN ---
if (isset($_POST['pinjam'])) {
    $nim = $_POST['nim_peminjam'];
    $id_copy = $_POST['id_copy'];
    $tanggal = date('Y-m-d'); // Otomatis tanggal hari ini (Sesuai aturan bisnis)

    // 1. CEK VALIDASI: Pastikan status buku benar-benar 'Tersedia' sebelum dipinjam
    // (Mencegah konflik jika ada 2 admin meminjamkan buku yang sama bersamaan)
    $cek_status = mysqli_query($koneksi, "SELECT status FROM copy_buku WHERE id_copy = '$id_copy'");
    $data_buku = mysqli_fetch_array($cek_status);

    if ($data_buku['status'] == 'Tersedia') {
        
        // 2. CATAT KE TABEL PEMINJAMAN
        $query_pinjam = "INSERT INTO peminjaman (tanggal_pinjam, nim_peminjam, id_copy, status_transaksi) 
                         VALUES ('$tanggal', '$nim', '$id_copy', 'Berjalan')";
        
        // 3. UPDATE STATUS FISIK BUKU MENJADI 'DIPINJAM'
        $query_update = "UPDATE copy_buku SET status = 'Dipinjam' WHERE id_copy = '$id_copy'";

        // Jalankan kedua query
        if (mysqli_query($koneksi, $query_pinjam) && mysqli_query($koneksi, $query_update)) {
            $popup_status = "show";
            $popup_pesan = "Transaksi berhasil! Buku statusnya kini 'Dipinjam'.";
        } else {
            echo "<script>alert('Gagal menyimpan transaksi.');</script>";
        }

    } else {
        echo "<script>alert('Maaf, buku ini baru saja dipinjam orang lain!'); window.location='transaksi_peminjaman.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Peminjaman - Sistem Perpustakaan</title>
    <style>
        /* --- CSS UTAMA (SAMA) --- */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .header { background-color: #003366; color: white; padding: 20px; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.8; }
        .container { width: 90%; max-width: 900px; margin: 0 auto 50px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        .btn-back { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background-color: #5a6268; transform: translateX(-3px); }
        
        label { font-weight: bold; color: #333; display: block; margin-top: 15px; }
        select { width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        .btn-simpan { background-color: #28a745; color: white; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-simpan:hover { background-color: #218838; }

        /* Tabel & Badge */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        
        .badge { padding: 5px 12px; border-radius: 20px; color: white; font-size: 12px; font-weight: bold; }
        .bg-blue { background-color: #007bff; }
        .bg-green { background-color: #28a745; }

        /* Pop-up Modal Style */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background-color: white; padding: 30px; width: 400px; border-radius: 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: fadeIn 0.3s ease-in-out; }
        .modal-icon { font-size: 50px; color: #28a745; margin-bottom: 10px; }
        .modal-title { font-size: 22px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .modal-text { font-size: 16px; color: #666; margin-bottom: 25px; }
        .btn-modal { background-color: #007bff; color: white; padding: 10px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
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

    <?php if ($popup_status == "show"): ?>
    <div class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">✓</div>
            <div class="modal-title">Berhasil!</div>
            <div class="modal-text"><?php echo $popup_pesan; ?></div>
            <a href="transaksi_peminjaman.php" class="btn-modal">OK, Lanjutkan</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="header">
        <img src="logo.png" alt="Logo UM" class="logo-kampus">
        <h1>SISTEM INFORMASI PERPUSTAKAAN</h1>
        <p>Peminjaman Buku Perpustakaan Universitas Negeri Malang</p>
    </div>

    <div class="container">
        <a href="index.php" class="btn-back">⬅ Kembali ke Dashboard</a>

        <h2 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Transaksi Peminjaman Baru</h2>
        
        <form method="POST">
            <label>Pilih Peminjam (Mahasiswa):</label>
            <select name="nim_peminjam" required>
                <option value="">-- Cari Nama Mahasiswa --</option>
                <?php
                // Mengambil data mahasiswa untuk dropdown
                $sql_mhs = mysqli_query($koneksi, "SELECT * FROM peminjam ORDER BY nama_peminjam ASC");
                while ($m = mysqli_fetch_array($sql_mhs)) {
                    echo "<option value='" . $m['nim_peminjam'] . "'>" . $m['nim_peminjam'] . " - " . $m['nama_peminjam'] . "</option>";
                }
                ?>
            </select>

            <label>Pilih Copy Buku (Hanya yang Tersedia):</label>
            <select name="id_copy" required>
                <option value="">-- Pilih Buku --</option>
                <?php
                // LOGIKA PENTING: Hanya menampilkan buku yang statusnya 'Tersedia'
                // Menggunakan JOIN agar kita bisa melihat Judul Bukunya (bukan cuma kode angka)
                $sql_buku = mysqli_query($koneksi, "SELECT copy_buku.*, buku.judul 
                                                    FROM copy_buku 
                                                    JOIN buku ON copy_buku.id_buku = buku.id_buku
                                                    WHERE copy_buku.status = 'Tersedia'
                                                    ORDER BY buku.judul ASC");
                
                while ($b = mysqli_fetch_array($sql_buku)) {
                    echo "<option value='" . $b['id_copy'] . "'>ID: " . $b['id_copy'] . " - " . $b['judul'] . "</option>";
                }
                ?>
            </select>

            <button type="submit" name="pinjam" class="btn-simpan">Proses Peminjaman</button>
        </form>

        <br><br>
        
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Riwayat Peminjaman Berjalan</h3>
        <p style="font-size:14px; color:#666;">Daftar di bawah ini adalah buku yang sedang dipinjam dan belum dikembalikan.</p>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Tanggal Pinjam</th>
                <th>Peminjam</th>
                <th>Buku (ID Copy)</th>
                <th>Status</th>
            </tr>
            <?php
            // JOIN 3 Tabel untuk menampilkan informasi lengkap
            $query_list = "SELECT peminjaman.*, peminjam.nama_peminjam, buku.judul 
                           FROM peminjaman
                           JOIN peminjam ON peminjaman.nim_peminjam = peminjam.nim_peminjam
                           JOIN copy_buku ON peminjaman.id_copy = copy_buku.id_copy
                           JOIN buku ON copy_buku.id_buku = buku.id_buku
                           WHERE status_transaksi = 'Berjalan'
                           ORDER BY id_peminjaman DESC";
            
            $tampil = mysqli_query($koneksi, $query_list);
            while ($data = mysqli_fetch_array($tampil)) {
                echo "<tr>";
                echo "<td>" . $data['id_peminjaman'] . "</td>";
                echo "<td>" . $data['tanggal_pinjam'] . "</td>";
                echo "<td>" . $data['nama_peminjam'] . "</td>";
                echo "<td>" . $data['judul'] . " (Copy #" . $data['id_copy'] . ")</td>";
                echo "<td><span class='badge bg-blue'>" . $data['status_transaksi'] . "</span></td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>