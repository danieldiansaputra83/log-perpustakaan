<?php
// Koneksi database
include 'koneksi.php';
$nim_saya = $_SESSION['id'];

// AMBIL DATA PROFIL USER
$profil = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM peminjam WHERE nim_peminjam = '$nim_saya'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        
        /* HEADER FLEXBOX */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .logo-user { width: 150px; height: auto; }
        .header h2 { margin: 0; color: #333; }
        .btn-logout { background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; }

        .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h3 { border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0; color: #003366; }

        /* STYLE PENCARIAN */
        .search-box { margin-bottom: 15px; display: flex; gap: 10px; }
        .input-cari { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: 'Poppins', sans-serif; }
        .btn-cari { background: #003366; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-cari:hover { background: #002244; }
        .btn-reset { background: #6c757d; color: white; border: none; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; display: flex; align-items: center; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        
        .btn-pinjam { background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 14px; border: none; cursor: pointer; transition: 0.3s; }
        .btn-pinjam:hover { background: #218838; }

        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; color: white; }
        .bg-blue { background: #007bff; } .bg-grey { background: #6c757d; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="user-info">
            <img src="logo.png" alt="Logo UM" class="logo-user">
            <h2>Halo, <?php echo $profil['nama_peminjam']; ?></h2>
        </div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="section">
        <h3>👤 Profil Saya</h3>
        <table>
            <tr><td width="200">NIM</td><td>: <?php echo $profil['nim_peminjam']; ?></td></tr>
            <tr><td>Nama Lengkap</td><td>: <?php echo $profil['nama_peminjam']; ?></td></tr>
            <tr><td>Fakultas</td><td>: <?php echo $profil['fakultas_peminjam']; ?></td></tr>
            <tr><td>Program Studi</td><td>: <?php echo $profil['prodi']; ?></td></tr>
        </table>
    </div>

    <div class="section">
        <h3>📚 Katalog Buku</h3>
        
        <form method="GET" class="search-box">
            <input type="text" name="q" class="input-cari" placeholder="Cari judul buku atau penerbit..." 
                   value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>">
            <button type="submit" class="btn-cari">Cari</button>
            
            <?php if(isset($_GET['q'])): ?>
                <a href="index_user.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>

        <table>
            <tr>
                <th>Judul Buku</th>
                <th>Penerbit</th>
                <th>Stok Tersedia</th>
                <th>Aksi</th>
            </tr>
            <?php
            // --- LOGIKA PENCARIAN ---
            $query_dasar = "SELECT buku.*, 
                            (SELECT COUNT(*) FROM copy_buku WHERE id_buku = buku.id_buku AND status = 'Tersedia') as stok 
                            FROM buku";
            
            // Jika ada pencarian, tambahkan filter WHERE
            if (isset($_GET['q'])) {
                $keyword = $_GET['q'];
                // Cari berdasarkan Judul ATAU Penerbit
                $query_dasar .= " WHERE judul LIKE '%$keyword%' OR penerbit LIKE '%$keyword%'";
            }

            $buku = mysqli_query($koneksi, $query_dasar);
            
            // Cek apakah ada buku ditemukan?
            if (mysqli_num_rows($buku) > 0) {
                while ($b = mysqli_fetch_array($buku)) {
                    echo "<tr>";
                    echo "<td>" . $b['judul'] . "</td>";
                    echo "<td>" . $b['penerbit'] . "</td>";
                    echo "<td>" . $b['stok'] . " Copy</td>";
                    echo "<td>";
                    
                    if ($b['stok'] > 0) {
                        echo "<button onclick=\"konfirmasiPinjam('" . $b['id_buku'] . "', '" . addslashes($b['judul']) . "')\" class='btn-pinjam'>
                                Pinjam Buku
                              </button>";
                    } else {
                        echo "<span style='color:red; font-size:12px;'>Habis</span>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center; padding:20px; color:#666;'>Buku tidak ditemukan.</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="section">
        <h3>🔄 Riwayat Peminjaman Saya</h3>
        <table>
            <tr>
                <th>Judul Buku</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Status</th>
                <th>Denda</th>
            </tr>
            <?php
            $riwayat = mysqli_query($koneksi, "
                SELECT peminjaman.*, buku.judul, pengembalian.tanggal_kembali, pengembalian.denda 
                FROM peminjaman 
                JOIN copy_buku ON peminjaman.id_copy = copy_buku.id_copy
                JOIN buku ON copy_buku.id_buku = buku.id_buku
                LEFT JOIN pengembalian ON peminjaman.id_peminjaman = pengembalian.id_peminjaman
                WHERE peminjaman.nim_peminjam = '$nim_saya'
                ORDER BY peminjaman.id_peminjaman DESC
            ");

            while ($r = mysqli_fetch_array($riwayat)) {
                $status_label = ($r['status_transaksi'] == 'Berjalan') ? 'Berlangsung' : 'Selesai';
                $warna = ($r['status_transaksi'] == 'Berjalan') ? 'bg-blue' : 'bg-grey';
                $denda = ($r['denda'] > 0) ? "Rp " . number_format($r['denda']) : "-";
                $tgl_kembali = ($r['tanggal_kembali']) ? $r['tanggal_kembali'] : "-";

                echo "<tr>";
                echo "<td>" . $r['judul'] . "</td>";
                echo "<td>" . $r['tanggal_pinjam'] . "</td>";
                echo "<td>" . $tgl_kembali . "</td>";
                echo "<td><span class='badge $warna'>$status_label</span></td>";
                echo "<td>" . $denda . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

</div>

<script>
    function konfirmasiPinjam(idBuku, judulBuku) {
        Swal.fire({
            title: 'Konfirmasi Peminjaman',
            text: "Apakah Anda yakin ingin meminjam buku: " + judulBuku + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#003366', 
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Pinjam!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'proses_pinjam_user.php?id_buku=' + idBuku;
            }
        })
    }

    <?php if (isset($_GET['pesan'])): ?>
        const urlParams = new URLSearchParams(window.location.search);
        const pesan = urlParams.get('pesan');

        if (pesan == 'sukses') {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Buku berhasil dipinjam. Silakan ambil di rak.',
                icon: 'success',
                confirmButtonColor: '#003366'
            }).then(() => {
                // Hapus parameter URL agar bersih
                const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.pushState({path:newUrl},'',newUrl);
            });
        } 
        else if (pesan == 'habis') {
            Swal.fire({
                title: 'Gagal!',
                text: 'Maaf, stok buku ini baru saja habis.',
                icon: 'error',
                confirmButtonColor: '#003366'
            });
        }
        else if (pesan == 'gagal') {
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan sistem.',
                icon: 'error',
                confirmButtonColor: '#003366'
            });
        }
    <?php endif; ?>
</script>

</body>
</html>