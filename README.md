# 📚 Library Management System

Sistem Manajemen Perpustakaan ini dikembangkan sebagai proyek akhir mata kuliah Basis Data untuk mengotomatisasi proses administrasi perpustakaan. Fokus utama proyek ini adalah pada integritas data dan efisiensi hubungan antar entitas dalam mengelola inventaris buku dan transaksi peminjaman.

## 🔑 Fitur Utama

- **Data Management (CRUD):** Pengelolaan data buku, kategori, penulis, dan anggota secara terstruktur.
- **Transaction Tracking:** Pencatatan otomatis untuk peminjaman dan pengembalian buku, termasuk perhitungan denda (jika ada).
- **Search Optimization:** Fitur pencarian buku berdasarkan judul, ISBN, atau kategori menggunakan kueri SQL yang teroptimasi.
- **Relational Integrity:** Memastikan konsistensi data antara peminjam dan inventaris buku yang tersedia.

## 🛠️ Tech Stack

- **Database:** MySQL
- **Language:** PHP, CSS, JavaScript
- **Modeling Tools:** MySQL Workbench / draw.io (untuk ERD)

## 📁 Struktur Tabel Utama

1. **Books:** ID, Judul, ISBN, Penulis, Stok, Kategori_ID.
2. **Members:** ID, Nama, NIM/NIP, Kontak.
3. **Transactions:** ID, Member_ID, Book_ID, Tanggal_Pinjam, Tanggal_Kembali, Status.
