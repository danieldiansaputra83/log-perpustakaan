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
$buku_edit = ""; // ID Buku yang dipilih
$status_edit = "Tersedia"; // Default status
$tombol_label = "Tambah Stok Copy";
$aksi_form = "";
$popup_status = "";
$popup_pesan = "";

// --- LOGIKA 1: MENANGKAP TOMBOL EDIT ATAU HAPUS ---
if (isset($_GET['op'])) {
    $op = $_GET['op'];
    $id = $_GET['id'];

    // LOGIKA HAPUS
    if ($op == 'delete') {
        $query_hapus = "DELETE FROM copy_buku WHERE id_copy = '$id'";
        if (mysqli_query($koneksi, $query_hapus)) {
            echo "<script>alert('Stok fisik berhasil dihapus dari rak!'); window.location='copy.php';</script>";
        } else {
            // Error ini biasanya muncul jika buku sedang dipinjam (Foreign Key Constraint)
            echo "<script>alert('Gagal Hapus! Buku ini sedang dipinjam atau ada di riwayat transaksi.'); window.location='copy.php';</script>";
        }
    }
    
    // LOGIKA PERSIAPAN EDIT
    if ($op == 'edit') {
        $query_tampil = mysqli_query($koneksi, "SELECT * FROM copy_buku WHERE id_copy = '$id'");
        $data_edit = mysqli_fetch_array($query_tampil);
        
        $id_edit = $data_edit['id_copy'];
        $buku_edit = $data_edit['id_buku'];
        $status_edit = $data_edit['status'];
        
        $tombol_label = "Update Stok";
        $aksi_form = "?op=update&id=$id";
    }
}

// --- LOGIKA 2: SIMPAN / UPDATE ---
if (isset($_POST['proses'])) {
    $id_buku = $_POST['id_buku'];
    $status = $_POST['status'];

    if (isset($_GET['op']) && $_GET['op'] == 'update') {
        // MODE UPDATE
        $id_lama = $_GET['id'];
        $query = "UPDATE copy_buku SET id_buku = '$id_buku', status = '$status' WHERE id_copy = '$id_lama'";
        $pesan_sukses = "Data fisik buku berhasil diperbarui!";
    } else {
        // MODE INSERT (ID Copy Auto Increment)
        $query = "INSERT INTO copy_buku (id_buku, status) VALUES ('$id_buku', '$status')";
        $pesan_sukses = "Stok buku baru berhasil ditambahkan ke rak!";
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
    <title>Stok Fisik (Copy) - Sistem Perpustakaan</title>
    <style>
        /* --- CSS UTAMA (SAMA) --- */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .header { background-color: #003366; color: white; padding: 20px; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.8; }
        .container { width: 90%; max-width: 800px; margin: 0 auto 50px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        .btn-back { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background-color: #5a6268; transform: translateX(-3px); }
        
        label { font-weight: bold; color: #333; display: block; margin-top: 15px; }
        input, select { width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        .btn-simpan { background-color: #17a2b8; color: white; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-simpan:hover { background-color: #138496; }
        .btn-update { background-color: #28a745; }

        /* Tabel & Aksi */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        
        .btn-aksi { padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold; margin-right: 5px; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-hapus { background-color: #dc3545; }

        /* Status Badge Style */
        .badge { padding: 5px 10px; border-radius: 15px; color: white; font-size: 12px; font-weight: bold; display: inline-block; }
        .bg-green { background-color: #28a745; } /* Tersedia */
        .bg-red { background-color: #dc3545; }   /* Dipinjam/Rusak */

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
            <div class="modal-title">Sukses!</div>
            <div class="modal-text"><?php echo $popup_pesan; ?></div>
            <a href="copy.php" class="btn-modal">OK, Lanjutkan</a>
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
            <?php echo ($aksi_form) ? "Edit Stok Fisik (Copy)" : "Tambah Stok Buku (Copy)"; ?>
        </h2>
        
        <form method="POST" action="<?php echo $aksi_form; ?>">
            
            <?php if($aksi_form): ?>
            <label>ID Copy (Barcode):</label>
            <input type="text" value="<?php echo $id_edit; ?>" readonly style="background-color: #e9ecef;">
            <?php endif; ?>

            <label>Pilih Judul Buku:</label>
            <select name="id_buku" required>
                <option value="">-- Pilih Buku --</option>
                <?php
                // Dropdown Buku
                $sql_buku = mysqli_query($koneksi, "SELECT * FROM buku");
                while ($b = mysqli_fetch_array($sql_buku)) {
                    $selected = ($b['id_buku'] == $buku_edit) ? "selected" : "";
                    echo "<option value='" . $b['id_buku'] . "' $selected>" . $b['judul'] . "</option>";
                }
                ?>
            </select>

            <label>Status Ketersediaan:</label>
            <select name="status" required>
                <option value="Tersedia" <?php if($status_edit == 'Tersedia') echo 'selected'; ?>>Tersedia (Ada di Rak)</option>
                <option value="Dipinjam" <?php if($status_edit == 'Dipinjam') echo 'selected'; ?>>Dipinjam (Dibawa Mahasiswa)</option>
                <option value="Rusak" <?php if($status_edit == 'Rusak') echo 'selected'; ?>>Rusak (Perbaikan)</option>
                <option value="Hilang" <?php if($status_edit == 'Hilang') echo 'selected'; ?>>Hilang</option>
            </select>

            <button type="submit" name="proses" class="btn-simpan <?php echo ($aksi_form) ? 'btn-update' : ''; ?>">
                <?php echo $tombol_label; ?>
            </button>
            
            <?php if($aksi_form): ?>
                <a href="copy.php" style="display:block; text-align:center; margin-top:10px; color:#dc3545; text-decoration:none;">Batal Edit</a>
            <?php endif; ?>
        </form>

        <br><br>
        
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Daftar Stok Fisik di Rak</h3>
        <table>
            <tr>
                <th>ID Copy</th>
                <th>Judul Buku</th>
                <th>Status</th>
                <th width="20%">Aksi</th>
            </tr>
            <?php
            // JOIN agar nama buku muncul
            $query_copy = "SELECT copy_buku.*, buku.judul 
                           FROM copy_buku 
                           LEFT JOIN buku ON copy_buku.id_buku = buku.id_buku
                           ORDER BY copy_buku.id_copy DESC";
            
            $tampil = mysqli_query($koneksi, $query_copy);
            while ($data = mysqli_fetch_array($tampil)) {
                // Tentukan Warna Badge
                $warna_badge = ($data['status'] == 'Tersedia') ? 'bg-green' : 'bg-red';
                
                echo "<tr>";
                echo "<td><b>" . $data['id_copy'] . "</b></td>";
                echo "<td>" . $data['judul'] . "</td>";
                echo "<td><span class='badge $warna_badge'>" . $data['status'] . "</span></td>";
                echo "<td>
                        <a href='copy.php?op=edit&id=" . $data['id_copy'] . "' class='btn-aksi btn-edit'>Edit</a>
                        <a href='copy.php?op=delete&id=" . $data['id_copy'] . "' class='btn-aksi btn-hapus' onclick=\"return confirm('Yakin ingin menghapus stok fisik ini?');\">Hapus</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>