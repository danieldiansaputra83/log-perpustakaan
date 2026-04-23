<?php
session_start();
include 'koneksi.php';

// CEK KEAMANAN: Cuma Admin yang boleh masuk sini
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Variabel default
$id_edit = "";
$nama_edit = "";
$tombol_label = "Simpan Data"; 
$aksi_form = ""; 
$popup_status = ""; // Variabel untuk memicu pop-up
$popup_pesan = "";

// --- LOGIKA 1: MENANGKAP TOMBOL EDIT ATAU HAPUS ---
if (isset($_GET['op'])) {
    $op = $_GET['op'];
    $id = $_GET['id'];

    // HAPUS
    if ($op == 'delete') {
        $query_hapus = "DELETE FROM pengarang WHERE id_pengarang = '$id'";
        if (mysqli_query($koneksi, $query_hapus)) {
            // Trik: Redirect dulu agar URL bersih, lalu tampilkan pesan (opsional, disini kita pakai alert standar untuk hapus agar cepat)
            echo "<script>alert('Data Berhasil Dihapus!'); window.location='pengarang.php';</script>";
        } else {
            echo "<script>alert('Gagal Hapus! Pengarang masih memiliki buku.'); window.location='pengarang.php';</script>";
        }
    }
    
    // EDIT
    if ($op == 'edit') {
        $query_tampil = mysqli_query($koneksi, "SELECT * FROM pengarang WHERE id_pengarang = '$id'");
        $data_edit = mysqli_fetch_array($query_tampil);
        $id_edit = $data_edit['id_pengarang'];
        $nama_edit = $data_edit['nama_pengarang'];
        $tombol_label = "Update Perubahan";
        $aksi_form = "?op=update&id=$id";
    }
}

// --- LOGIKA 2: SIMPAN / UPDATE ---
if (isset($_POST['proses'])) {
    $nama = $_POST['nama_pengarang'];
    
    if (isset($_GET['op']) && $_GET['op'] == 'update') {
        // UPDATE
        $id_lama = $_GET['id'];
        $query = "UPDATE pengarang SET nama_pengarang = '$nama' WHERE id_pengarang = '$id_lama'";
        $pesan_sukses = "Data pengarang berhasil diperbarui!";
    } else {
        // INSERT
        $query = "INSERT INTO pengarang (nama_pengarang) VALUES ('$nama')";
        $pesan_sukses = "Pengarang baru berhasil ditambahkan!";
    }
    
    if (mysqli_query($koneksi, $query)) {
        // SUKSES: Jangan redirect dulu, tapi set variabel pop-up
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
    <title>Data Pengarang - Sistem Perpustakaan</title>
    <style>
        /* --- CSS UTAMA (SAMA SEPERTI SEBELUMNYA) --- */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f9; margin: 0; padding: 0; }
        .header { background-color: #003366; color: white; padding: 20px; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; font-size: 14px; opacity: 0.8; }
        .container { width: 90%; max-width: 800px; margin: 0 auto 50px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        
        .btn-back { display: inline-block; margin-bottom: 20px; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-back:hover { background-color: #5a6268; transform: translateX(-3px); }
        
        label { font-weight: bold; color: #333; display: block; margin-top: 15px; }
        input { width: 100%; padding: 12px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        .btn-simpan { background-color: #007bff; color: white; padding: 12px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-simpan:hover { background-color: #0056b3; }
        .btn-update { background-color: #28a745; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; color: #333; }
        
        .btn-aksi { padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; font-weight: bold; margin-right: 5px; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-hapus { background-color: #dc3545; }

        /* --- CSS BARU UNTUK POP-UP MODAL --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Latar belakang gelap transparan */
            display: flex; justify-content: center; align-items: center;
            z-index: 1000;
        }

        .modal-box {
            background-color: white;
            padding: 30px;
            width: 400px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-icon { font-size: 50px; color: #28a745; margin-bottom: 10px; }
        .modal-title { font-size: 22px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .modal-text { font-size: 16px; color: #666; margin-bottom: 25px; }

        .btn-modal {
            background-color: #007bff; color: white; padding: 10px 30px;
            text-decoration: none; border-radius: 5px; font-weight: bold;
        }
        .btn-modal:hover { background-color: #0056b3; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
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
            
            <a href="pengarang.php" class="btn-modal">OK, Lanjutkan</a>
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
            <?php echo ($aksi_form) ? "Edit Data Pengarang" : "Tambah Pengarang Baru"; ?>
        </h2>
        
        <form method="POST" action="<?php echo $aksi_form; ?>">
            <label>Nama Pengarang:</label>
            <input type="text" name="nama_pengarang" required placeholder="Contoh: Tere Liye" value="<?php echo $nama_edit; ?>">

            <button type="submit" name="proses" class="btn-simpan <?php echo ($aksi_form) ? 'btn-update' : ''; ?>">
                <?php echo $tombol_label; ?>
            </button>
            
            <?php if($aksi_form): ?>
                <a href="pengarang.php" style="display:block; text-align:center; margin-top:10px; color:#dc3545; text-decoration:none;">Batal Edit</a>
            <?php endif; ?>
        </form>

        <br><br>
        
        <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Daftar Pengarang</h3>
        <table>
            <tr>
                <th width="10%">ID</th>
                <th>Nama Pengarang</th>
                <th width="20%">Aksi</th>
            </tr>
            <?php
            $tampil = mysqli_query($koneksi, "SELECT * FROM pengarang ORDER BY id_pengarang DESC");
            while ($data = mysqli_fetch_array($tampil)) {
                echo "<tr>
                        <td>" . $data['id_pengarang'] . "</td>
                        <td><b>" . $data['nama_pengarang'] . "</b></td>
                        <td>
                            <a href='pengarang.php?op=edit&id=" . $data['id_pengarang'] . "' class='btn-aksi btn-edit'>Edit</a>
                            <a href='pengarang.php?op=delete&id=" . $data['id_pengarang'] . "' class='btn-aksi btn-hapus' onclick=\"return confirm('Yakin ingin menghapus?');\">Hapus</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>