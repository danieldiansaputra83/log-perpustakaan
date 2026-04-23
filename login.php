<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $input_id = $_POST['username']; // Bisa NIM atau Username Admin
    $pass = md5($_POST['password']);

    // LOGIKA 1: CEK APAKAH INPUTNYA ANGKA? (NIM)
    if (ctype_digit($input_id)) {
        // LOGIN SEBAGAI MAHASISWA (USER)
        $query = mysqli_query($koneksi, "SELECT * FROM peminjam WHERE nim_peminjam = '$input_id' AND password = '$pass'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_array($query);
            $_SESSION['role'] = 'user';
            $_SESSION['id'] = $data['nim_peminjam'];
            $_SESSION['nama'] = $data['nama_peminjam'];
            header("Location: index.php");
        } else {
            $error = "NIM atau Password Mahasiswa Salah!";
        }
    } else {
        // LOGIKA 2: JIKA HURUF, LOGIN SEBAGAI ADMIN
        $query = mysqli_query($koneksi, "SELECT * FROM user_admin WHERE username = '$input_id' AND password = '$pass'");
        if (mysqli_num_rows($query) > 0) {
            $data = mysqli_fetch_array($query);
            $_SESSION['role'] = 'admin';
            $_SESSION['id'] = $data['id_user'];
            $_SESSION['nama'] = $data['nama_lengkap'];
            header("Location: index.php");
        } else {
            $error = "Username Admin atau Password Salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login Perpustakaan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #003366; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .link { margin-top: 15px; font-size: 13px; }
        .error { color: red; font-size: 13px; margin-bottom: 10px; }
        .logo-login {
            width: 200px;        /* Ukuran logo */
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
    </style>
</head>
<body>
    <div class="card">
        <img src="logo.png" alt="Logo UM" class="logo-login">
        <h2>Perpustakaan UM</h2>
        <p>Masuk Sistem</p>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="NIM (User) atau Username (Admin)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
        <div class="link">Belum punya akun? <a href="register.php">Daftar Akun</a></div>
    </div>
</body>
</html>