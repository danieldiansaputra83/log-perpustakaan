<?php
session_start();

// 1. Cek Login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// 2. Router (Pengarah Jalan)
if ($_SESSION['role'] == 'admin') {
    include 'index_admin.php'; // Kita akan buat file ini
} else {
    include 'index_user.php';  // Kita akan buat file ini
}
?>