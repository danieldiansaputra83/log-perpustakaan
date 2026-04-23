<?php
session_start();
include 'koneksi.php';

// CEK KEAMANAN: Cuma Admin yang boleh masuk sini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// --- INISIALISASI VARIABEL ---
$id_edit = "";
$judul_edit = "";
$penerbit_edit = "";
$pengarang_edit = ""; // Untuk menampung ID pengarang saat edit
$tombol_label = "Simpan Buku";
$aksi_form = "";
$popup_status = "";
$popup_pesan = "";
$readonly_id = ""; // Variabel untuk mengunci input ID saat edit

// --- LOGIKA 1: MENANGKAP TOMBOL EDIT ATAU HAPUS ---
if (isset($_GET['op'])) {
    $op = $_GET['op'];
    $id = $_GET['id'];

    // LOGIKA HAPUS
    if ($op == 'delete') {
        $query_hapus = "DELETE FROM buku WHERE id_buku = '$id'";
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script>alert('Data Buku Berhasil Dihapus!'); window.location='buku.php';</script>";
        } else {
            // Pesan jika gagal (misal karena sedang dipinjam/terikat data lain)
            echo "<script>alert('Gagal Hapus! Buku ini memiliki stok (copy) atau sedang dipinjam.'); window.location='buku.php';</script>";
        }
    }
    
    // LOGIKA PERSIAPAN EDIT
    if ($op == 'edit') {
        $query_tampil = mysqli_query($koneksi, "SELECT * FROM buku WHERE id_buku = '$id'");
        $data_edit = mysqli_fetch_array($query_tampil);
        
        $id_edit = $data_edit['id_buku'];
        $judul_edit = $data_edit['judul'];
        $penerbit_edit = $data_edit['penerbit'];
        $pengarang_edit = $data_edit['id_pengarang']; // Penting untuk dropdown
        
        $tombol_label = "Update Perubahan";
        $aksi_form = "?op=update&id=$id";
        $readonly_id = "readonly style='background-color: #e9ecef; cursor: not-allowed;'"; // Kunci input ID
    }
}

// --- LOGIKA 2: SIMPAN / UPDATE ---
if (isset($_POST['proses'])) {
    $id_buku = $_POST['id_buku'];
    $judul = $_POST['judul'];
    $penerbit = $_POST['penerbit'];
    $id_pengarang = $_POST['id_pengarang'];

    if (isset($_GET['op']) && $_GET['op'] == 'update') {
        // MODE UPDATE (ID Buku tidak diupdate karena itu Primary Key)
        $id_lama = $_GET['id'];
        $query = "UPDATE buku SET 
                  judul = '$judul', 
                  penerbit = '$penerbit', 
                  id_pengarang = '$id_pengarang' 
                  WHERE id_buku = '$id_lama'";
        $pesan_sukses = "Data buku berhasil diperbarui!";
    } else {
        // MODE INSERT BARU
        // Cek dulu apakah ID Buku sudah ada
        $cek_id = mysqli_query($koneksi, "SELECT id_buku FROM buku WHERE id_buku = '$id_buku'");
        if(mysqli_num_rows($cek_id) > 0){
            echo "<script>alert('Error: ID Buku $id_buku sudah digunakan!'); window.location='buku.php';</script>";
            exit; // Hentikan proses
        }

        $query = "INSERT INTO buku (id_buku, judul, penerbit, id_pengarang) 
                  VALUES ('$id_buku', '$judul', '$penerbit', '$id_pengarang')";
        $pesan_sukses = "Buku baru berhasil ditambahkan!";
    }
    
    if (mysqli_query($koneksi, $query)) {
        $popup_status = "show";
        $popup_pesan = $pesan_sukses;
    } else {
        echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Buku - Sistem Perpustakaan</title>
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
        input, select { width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        .btn-simpan { background-color: #007bff; color: white; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-simpan:hover { background-color: #0056b3; }
        .btn-update { background-color: #28a745; }

        /* Tabel & Aksi */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        
        .btn-aksi { padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold; margin-right: 5px; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-hapus { background-color: #dc3545; }

        /* Pop-up Modal Style */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-box {
            background-color: white; padding: 30px; width: 400px; border-radius: 10px; text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); animation: fadeIn 0.3s ease-in-out;
        }
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
            <div class="modal-title">Sukses!</div>
            <div class="modal-text"><?php echo $popup_pesan; ?></div>
            <a href="buku.php" class="btn-modal">OK, Lanjutkan</a>
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

        <h2 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">
            <?php echo ($aksi_form) ? "Edit Data Buku" : "Tambah Data Buku"; ?>
        </h2>
        
        <form method="POST" action="<?php echo $aksi_form; ?>">
            <label>ID Buku / Kode Buku:</label>
            <input type="text" name="id_buku" required placeholder="Contoh: B001" value="<?php echo $id_edit; ?>" <?php echo $readonly_id; ?>>

            <label>Judul Buku:</label>
            <input type="text" name="judul" required placeholder="Contoh: Laskar Pelangi" value="<?php echo $judul_edit; ?>">

            <label>Penerbit:</label>
            <input type="text" name="penerbit" placeholder="Contoh: Bentang Pustaka" value="<?php echo $penerbit_edit; ?>">

            <label>Pengarang:</label>
            <select name="id_pengarang" required>
                <option value="">-- Pilih Pengarang --</option>
                <?php
                $sql_pengarang = mysqli_query($koneksi, "SELECT * FROM pengarang");
                while ($p = mysqli_fetch_array($sql_pengarang)) {
                    // Logika agar dropdown terpilih otomatis saat Edit
                    $selected = ($p['id_pengarang'] == $pengarang_edit) ? "selected" : "";
                    
                    echo "<option value='" . $p['id_pengarang'] . "' $selected>" . $p['nama_pengarang'] . "</option>";
                }
                ?>
            </select>

            <button type="submit" name="proses" class="btn-simpan <?php echo ($aksi_form) ? 'btn-update' : ''; ?>">
                <?php echo $tombol_label; ?>
            </button>
            
            <?php if($aksi_form): ?>
                <a href="buku.php" style="display:block; text-align:center; margin-top:10px; color:#dc3545; text-decoration:none;">Batal Edit</a>
            <?php endif; ?>
        </form>

        <br><br>
        
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Katalog Buku</h3>
        <table>
            <tr>
                <th>ID Buku</th>
                <th>Judul</th>
                <th>Penerbit</th>
                <th>Pengarang</th>
                <th width="20%">Aksi</th>
            </tr>
            <?php
            // Menampilkan data dengan JOIN untuk nama pengarang
            $query_buku = "SELECT buku.*, pengarang.nama_pengarang 
                           FROM buku 
                           LEFT JOIN pengarang ON buku.id_pengarang = pengarang.id_pengarang
                           ORDER BY buku.id_buku ASC";
            
            $tampil = mysqli_query($koneksi, $query_buku);
            while ($data = mysqli_fetch_array($tampil)) {
                echo "<tr>";
                echo "<td>" . $data['id_buku'] . "</td>";
                echo "<td><b>" . $data['judul'] . "</b></td>";
                echo "<td>" . $data['penerbit'] . "</td>";
                echo "<td>" . $data['nama_pengarang'] . "</td>";
                echo "<td>
                        <a href='buku.php?op=edit&id=" . $data['id_buku'] . "' class='btn-aksi btn-edit'>Edit</a>
                        <a href='buku.php?op=delete&id=" . $data['id_buku'] . "' class='btn-aksi btn-hapus' onclick=\"return confirm('Yakin ingin menghapus buku ini? Semua copy buku juga akan ikut terhapus!');\">Hapus</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>