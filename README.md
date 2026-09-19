# Application Pemilihan Ketua OSIS (E-Voting OSIS Webbased)

Aplikasi Pemilihan Ketua OSIS berbasis web yang dibangun menggunakan **Native PHP**, **Tailwind CSS**, dan **MySQL**.

## Fitur Utama

1. **Realtime Live Count (Halaman Utama / `index.php`)**
   - Tampilan grafik interaktif menggunakan **Chart.js** (Bar Chart & Doughnut Chart).
   - Auto-refresh otomatis setiap 5 detik menggunakan AJAX.
   - Ringkasan statistik total DPT, suara masuk, belum memilih, dan persentase partisipasi.
   - Tombol cepat ke Bilik Suara Siswa & Panel Admin.

2. **Manajemen Pasangan Calon (Admin - `admin/candidates.php`)**
   - Tambah, edit, dan hapus data pasangan calon (paslon).
   - Unggah foto kandidat (format JPG, PNG, WEBP).
   - Input nomor urut, nama calon ketua, nama calon wakil, serta visi & misi.

3. **Manajemen Pemilih / DPT (Admin - `admin/voters.php`)**
   - Tambah, edit, dan hapus data pemilih (NISN & Nama).
   - **Import Batch Data Pemilih via CSV/Excel** (`nisn,nama`).
   - Fitur pencarian & filter berdasarkan status memilih.
   - Fitur reset status memilih & pembersihan data pemilih.

4. **Bilik Suara Siswa (Pemilih - `voter/login.php` & `voter/vote.php`)**
   - Login mudah menggunakan **NISN saja tanpa password**.
   - Validasi hak pilih (mencegah pemilih memilih lebih dari 1 kali).
   - Tampilan surat suara digital dengan foto, nomor urut, nama, visi, dan misi.
   - Konfirmasi modal sebelum menyimpan pilihan.
   - Otomatis logout setelah berhasil memilih.

5. **Panel Admin Security (`admin/login.php`)**
   - Otentikasi terpisah untuk administrator.
   - Akun default admin:
     - **Username:** `admin`
     - **Password:** `password123`

---

## Persyaratan Sistem

- PHP 8.0+ (PDO & PDO_MySQL enabled)
- Web Server (Apache / Nginx / PHP Built-in Server)
- MySQL / MariaDB Server

---

## Panduan Instalasi & Penggunaan

1. **Import Database Schema:**
   - Buat database MySQL dengan nama `e_voting_osis` atau import file `schema.sql` ke MySQL / phpMyAdmin Anda.
   ```sql
   mysql -u root -p < schema.sql
   ```

2. **Konfigurasi Database:**
   - Sesuaikan konfigurasi koneksi MySQL di file `config.php`:
     ```php
     $db_host = 'localhost';
     $db_name = 'e_voting_osis';
     $db_user = 'root';
     $db_pass = '';
     ```

3. **Menjalankan Aplikasi:**
   - Jika menggunakan PHP Built-in Server:
     ```bash
     php -S localhost:8000
     ```
   - Buka browser dan akses:
     - Halaman Utama / Live Count: `http://localhost:8000`
     - Bilik Suara Pemilih: `http://localhost:8000/voter/login.php`
     - Panel Admin: `http://localhost:8000/admin/login.php`
