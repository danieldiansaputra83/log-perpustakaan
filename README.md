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

## 📸 Tampilan Website

![Tampilan Admin]!<img width="1915" height="1004" alt="Screenshot 2026-04-22 222554" src="https://github.com/user-attachments/assets/ee25bd34-c689-44c2-afaa-d290a0b4241d" />

<img width="1919" height="1007" alt="Screenshot 2026-04-22 222414" src="https://github.com/user-attachments/assets/13c1bf3d-b99f-4612-aaae-74e5ea2dd1a8" />

<img width="1919" height="1004" alt="Screenshot 2026-04-22 222421" src="https://github.com/user-attachments/assets/c2cc6b1c-b02a-4749-b872-db8f8296e555" />

<img width="1919" height="1003" alt="Screenshot 2026-04-22 222610" src="https://github.com/user-attachments/assets/d4db7267-dc04-4a19-9dda-3510a102b688" />

<img width="1919" height="1004" alt="Screenshot 2026-04-22 222601" src="https://github.com/user-attachments/assets/a57ba4d5-2805-4d08-9343-37fecb6a4a0f" />

<img width="1919" height="1003" alt="Screenshot 2026-04-22 222632" src="https://github.com/user-attachments/assets/010ac5d5-bd56-4f9f-8f11-e8a7dedb630e" />

<img width="1919" height="1006" alt="Screenshot 2026-04-22 222658" src="https://github.com/user-attachments/assets/9f2f8b17-c0a0-47dc-8e97-e659a8f31ad5" />


![Tampilan Mahasiswa]<img width="1919" height="1000" alt="Screenshot 2026-04-22 222520" src="https://github.com/user-attachments/assets/99c5b7df-5fe2-46b7-a706-0591f4dc6841" />


## 📅 Timeline
November 2025 – December 2025
