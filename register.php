<?php
include 'koneksi.php';

// Variabel untuk menampung status pendaftaran
$status_registrasi = "";
$pesan_error = "";

if (isset($_POST['daftar'])) {
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $fakultas = $_POST['fakultas'];
    
    // PERUBAHAN 1: Pakai fungsi strtoupper() agar data yang masuk database KAPITAL
    $prodi = strtoupper($_POST['prodi']); 
    
    $pass = md5($_POST['password']);

    // 1. Validasi NIM harus Angka
    if (!ctype_digit($nim)) {
        $status_registrasi = "gagal";
        $pesan_error = "NIM harus berupa angka!";
    } else {
        // 2. Cek apakah NIM sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM peminjam WHERE nim_peminjam = '$nim'");
        if (mysqli_num_rows($cek) > 0) {
            $status_registrasi = "gagal";
            $pesan_error = "NIM tersebut sudah terdaftar!";
        } else {
            // 3. Insert Data
            $query = "INSERT INTO peminjam (nim_peminjam, nama_peminjam, fakultas_peminjam, prodi, password) 
                      VALUES ('$nim', '$nama', '$fakultas', '$prodi', '$pass')";
            
            if (mysqli_query($koneksi, $query)) {
                $status_registrasi = "sukses";
            } else {
                $status_registrasi = "gagal";
                $pesan_error = "Terjadi kesalahan database.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Mahasiswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 10px; width: 100%; max-width: 400px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { color: #003366; margin-bottom: 20px; }
        
        input, select { 
            width: 100%; padding: 12px; margin: 8px 0; 
            border: 1px solid #ddd; border-radius: 5px; 
            box-sizing: border-box; font-family: 'Poppins', sans-serif; 
            background-color: #fff;
        }
        
        .btn { width: 100%; padding: 12px; background: #003366; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 15px; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn:hover { background: #002244; }
        
        .link { margin-top: 15px; font-size: 13px; color: #666; }
        .link a { color: #003366; text-decoration: none; font-weight: bold; }
        .link a:hover { text-decoration: underline; }
        .logo-login {
            width: 80px;        /* Ukuran logo */
            height: auto;
            margin-bottom: 15px; /* Jarak antara logo dan tulisan Judul */
            display: inline-block;
        }

        h2 { margin: 0 0 5px 0; color: #333; }
        p { margin: 0 0 20px 0; color: #666; font-size: 14px; }

        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        .btn { width: 100%; padding: 12px; background: #003366; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn:hover { background: #002244; }
        
        .link { margin-top: 15px; font-size: 13px; color: #666; }
        .link a { color: #003366; text-decoration: none; font-weight: bold; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; font-size: 13px; margin-bottom: 15px; }
    </style>
</head>
    </style>
</head>
<body>
	<img src="logo.png" alt="Logo UM" class="logo-login">
    <div class="card">
        <img src="logo.png" alt="Logo UM" class="logo-login">
        <h2>Daftar Mahasiswa</h2>
        <form method="POST">
            <input type="text" name="nim" placeholder="NIM" required pattern="[0-9]+">
            <input type="text" name="nama" placeholder="Nama Lengkap" required 
                   oninput="this.value = this.value.toUpperCase()" style="text-transform:uppercase">
            
            <select name="fakultas" required>
                <option value="">-- Pilih Fakultas --</option>
                <option value="Fakultas Matematika dan Ilmu Pengetahuan Alam">FMIPA (Fakultas Matematika dan Ilmu Pengetahuan Alam)</option>
                <option value="Fakultas Teknik">FT (Fakultas Teknik)</option>
                <option value="Fakultas Sastra">FS (Fakultas Sastra)</option>
                <option value="Fakultas Ekonomi dan Bisnis">FEB (Fakultas Ekonomi dan Bisnis)</option>
                <option value="Fakultas Ilmu Pendidikan">FIP (Fakultas Ilmu Pendidikan)</option>
                <option value="Fakultas Ilmu Keolahragaan">FIK (Fakultas Ilmu Keolahragaan)</option>
                <option value="Fakultas Ilmu Sosial">FIS (Fakultas Ilmu Sosial)</option>
                <option value="Fakultas Psikologi">FPsi (Fakultas Psikologi)</option>
                <option value="Fakultas Vokasi">FV (Fakultas Vokasi)</option>
                <option value="Fakultas Kedokteran">FK (Fakultas Kedokteran)</option>
            </select>

            <input type="text" name="prodi" placeholder="PROGRAM STUDI (CONTOH: S1 MATEMATIKA)" required 
                   oninput="this.value = this.value.toUpperCase()" style="text-transform:uppercase">
            
            <input type="password" name="password" placeholder="Password" required>
            
            <button type="submit" name="daftar" class="btn">Daftar Sekarang</button>
        </form>
        <div class="link">Sudah punya akun? <a href="login.php">Login disini</a></div>
    </div>

    <script>
        let status = "<?php echo $status_registrasi; ?>";
        let pesanError = "<?php echo $pesan_error; ?>";

        if (status == 'sukses') {
            Swal.fire({
                title: 'Pendaftaran Berhasil!',
                text: 'Akun Anda telah dibuat. Silakan login untuk melanjutkan.',
                icon: 'success',
                confirmButtonText: 'OK, Login',
                confirmButtonColor: '#003366'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location = 'login.php';
                }
            });
        } 
        else if (status == 'gagal') {
            Swal.fire({
                title: 'Gagal!',
                text: pesanError,
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    </script>

</body>
</html>