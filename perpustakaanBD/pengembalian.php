<?php
session_start();
include 'koneksi.php';

// CEK KEAMANAN: Cuma Admin yang boleh masuk sini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// --- KONFIGURASI DENDA ---
$lama_pinjam_maksimal = 7; // Batas waktu pinjam (hari)
$denda_per_hari = 1000;    // Nominal denda per hari (Rp)

// Variabel Pop-up
$popup_status = "";
$popup_pesan = "";
$popup_subpesan = ""; // Pesan tambahan untuk rincian denda

// --- LOGIKA PENGEMBALIAN ---
if (isset($_POST['kembalikan'])) {
    $id_peminjaman = $_POST['id_peminjaman'];
    $tanggal_kembali = date('Y-m-d'); // Tanggal hari ini

    // 1. AMBIL DATA PEMINJAMAN SEBELUMNYA
    $cek_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE id_peminjaman = '$id_peminjaman'");
    $data_pinjam = mysqli_fetch_array($cek_pinjam);
    
    $id_copy = $data_pinjam['id_copy'];
    $tanggal_pinjam = $data_pinjam['tanggal_pinjam'];

    // 2. HITUNG SELISIH HARI & DENDA
    $tgl1 = new DateTime($tanggal_pinjam);
    $tgl2 = new DateTime($tanggal_kembali);
    $selisih = $tgl2->diff($tgl1)->days;

    $denda = 0;
    $info_telat = "Tepat Waktu";
    
    // Jika selisih hari lebih besar dari batas maksimal, hitung denda
    if ($selisih > $lama_pinjam_maksimal) {
        $hari_terlambat = $selisih - $lama_pinjam_maksimal;
        $denda = $hari_terlambat * $denda_per_hari;
        $info_telat = "Terlambat $hari_terlambat hari";
    }

    // 3. SIMPAN KE TABEL PENGEMBALIAN
    $query_kembali = "INSERT INTO pengembalian (id_peminjaman, tanggal_kembali, denda) 
                      VALUES ('$id_peminjaman', '$tanggal_kembali', '$denda')";

    // 4. UPDATE STATUS PEMINJAMAN JADI 'SELESAI'
    $update_pinjam = "UPDATE peminjaman SET status_transaksi = 'Selesai' WHERE id_peminjaman = '$id_peminjaman'";

    // 5. UPDATE STATUS BUKU JADI 'TERSEDIA' (Agar bisa dipinjam orang lain)
    $update_buku = "UPDATE copy_buku SET status = 'Tersedia' WHERE id_copy = '$id_copy'";

    // Eksekusi ketiga query (Transaction Safety)
    if (mysqli_query($koneksi, $query_kembali) && mysqli_query($koneksi, $update_pinjam) && mysqli_query($koneksi, $update_buku)) {
        
        $popup_status = "show";
        $popup_pesan = "Buku Berhasil Dikembalikan!";
        
        // Custom pesan denda
        if($denda > 0){
            $popup_subpesan = "Mahasiswa terkena denda sebesar <b>Rp " . number_format($denda,0,',','.') . "</b> ($info_telat).";
        } else {
            $popup_subpesan = "Tidak ada denda (Pengembalian Tepat Waktu).";
        }

    } else {
        echo "<script>alert('Gagal memproses pengembalian.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian & Denda - Sistem Perpustakaan</title>
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
        
        .btn-simpan { background-color: #ffc107; color: black; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-simpan:hover { background-color: #e0a800; }

        /* Tabel & Badge */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        
        .badge { padding: 5px 12px; border-radius: 20px; color: white; font-size: 12px; font-weight: bold; }
        .bg-red { background-color: #dc3545; }
        .bg-grey { background-color: #6c757d; }

        /* Pop-up Modal Style */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background-color: white; padding: 30px; width: 450px; border-radius: 10px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: fadeIn 0.3s ease-in-out; }
        .modal-icon { font-size: 50px; color: #28a745; margin-bottom: 10px; }
        .modal-title { font-size: 22px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .modal-text { font-size: 16px; color: #333; margin-bottom: 5px; }
        .modal-subtext { font-size: 14px; color: #666; margin-bottom: 25px; line-height: 1.5; }
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
            <div class="modal-title"><?php echo $popup_pesan; ?></div>
            <div class="modal-subtext"><?php echo $popup_subpesan; ?></div>
            
            <a href="pengembalian.php" class="btn-modal">OK, Lanjutkan</a>
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

        <h2 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Proses Pengembalian Buku</h2>
        
        <form method="POST">
            <label>Pilih Transaksi yang akan dikembalikan:</label>
            <select name="id_peminjaman" required>
                <option value="">-- Pilih Buku yang Sedang Dipinjam --</option>
                <?php
                // LOGIKA PENTING: Hanya tampilkan transaksi yang statusnya 'Berjalan'
                // Admin tidak bisa mengembalikan buku yang sudah dikembalikan (mencegah duplikasi data pengembalian)
                $sql_pinjam = mysqli_query($koneksi, "SELECT peminjaman.*, peminjam.nama_peminjam, buku.judul 
                                                      FROM peminjaman 
                                                      JOIN peminjam ON peminjaman.nim_peminjam = peminjam.nim_peminjam
                                                      JOIN copy_buku ON peminjaman.id_copy = copy_buku.id_copy
                                                      JOIN buku ON copy_buku.id_buku = buku.id_buku
                                                      WHERE peminjaman.status_transaksi = 'Berjalan'
                                                      ORDER BY peminjaman.id_peminjaman ASC");
                
                while ($p = mysqli_fetch_array($sql_pinjam)) {
                    // Format tampilan dropdown: Nama Peminjam - Judul Buku - Tgl Pinjam
                    echo "<option value='" . $p['id_peminjaman'] . "'>
                            " . $p['nama_peminjam'] . " - " . $p['judul'] . " (Pinjam: " . $p['tanggal_pinjam'] . ")
                          </option>";
                }
                ?>
            </select>
            
            <button type="submit" name="kembalikan" class="btn-simpan">Hitung Denda & Kembalikan</button>
        </form>

        <br><br>
        
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Riwayat Pengembalian & Denda</h3>
        <table>
            <tr>
                <th>Peminjam</th>
                <th>Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Denda</th>
            </tr>
            <?php
            // Menampilkan data pengembalian yang sudah selesai
            $query_riwayat = "SELECT pengembalian.*, peminjaman.tanggal_pinjam, peminjam.nama_peminjam, buku.judul 
                              FROM pengembalian
                              JOIN peminjaman ON pengembalian.id_peminjaman = peminjaman.id_peminjaman
                              JOIN peminjam ON peminjaman.nim_peminjam = peminjam.nim_peminjam
                              JOIN copy_buku ON peminjaman.id_copy = copy_buku.id_copy
                              JOIN buku ON copy_buku.id_buku = buku.id_buku
                              ORDER BY id_pengembalian DESC";

            $tampil = mysqli_query($koneksi, $query_riwayat);
            while ($data = mysqli_fetch_array($tampil)) {
                
                // Format Rupiah
                $denda_text = "Rp " . number_format($data['denda'], 0, ',', '.');
                
                // Beri warna merah pada teks denda jika ada dendanya
                $style_denda = ($data['denda'] > 0) ? "color: red; font-weight: bold;" : "color: green;";

                echo "<tr>";
                echo "<td>" . $data['nama_peminjam'] . "</td>";
                echo "<td>" . $data['judul'] . "</td>";
                echo "<td>" . $data['tanggal_pinjam'] . "</td>";
                echo "<td>" . $data['tanggal_kembali'] . "</td>";
                echo "<td style='$style_denda'>" . $denda_text . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>