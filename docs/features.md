# Fitur

Daftar lengkap perilaku yang bisa dipakai, beserta tempat implementasinya. Tidak ada
tombol atau menu yang mengarah ke halaman kosong.

## Autentikasi

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Registrasi | nama, email (disimpan lowercase), kata sandi min 8 karakter campuran huruf besar/kecil dan angka; langsung login | `Auth/RegisterController`, `Requests/Auth/RegisterRequest`, `views/auth/register` |
| Login | email + kata sandi; email salah, kata sandi salah, dan akun nonaktif memberi pesan yang sama | `Requests/Auth/LoginRequest::authenticate` |
| Logout | POST, sesi di-invalidate dan token CSRF dibuat ulang | `Auth/LogoutController` |
| Batas percobaan | 5 percobaan login/menit, 6 registrasi/menit, halaman 429 | `routes/web.php:19,22`, `views/errors/429` |
| Akun nonaktif | tidak bisa login; sesi yang masih hidup diputus pada request berikutnya | `LoginRequest`, `Middleware/EnsureUserIsActive` |

## Dompet

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Daftar | hanya dompet sendiri, dengan jumlah transaksi per dompet | `WalletController@index` |
| Tambah / ubah | nama wajib, maks 100, unik per pemilik | `Requests/WalletRequest` |
| Hapus | ditolak dengan pesan jika masih punya transaksi | `WalletController@destroy` |
| Kosong | empty state + tombol tambah | `views/wallets/index` |

## Kategori

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Daftar | hanya kategori sendiri, badge tipe (ikon + teks, bukan warna saja) | `CategoryController@index` |
| Tambah / ubah | nama unik per (pemilik, nama, tipe); tipe `income`/`expense` | `Requests/CategoryRequest` |
| Hapus | ditolak jika masih dipakai transaksi | `CategoryController@destroy` |

## Transaksi

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Daftar | 15 baris per halaman, urut tanggal terbaru | `TransactionController@index` |
| Filter | bulan (`Y-m`), tipe, kategori; nilai filter tetap ada saat pindah halaman | `TransactionFilterRequest`, `withQueryString()` |
| Tambah / ubah | dompet dan kategori harus milik sendiri, nominal bulat, tanggal tidak melewati hari ini, catatan maks 255 | `Requests/TransactionRequest` |
| Invarian tipe | tipe transaksi harus sama dengan tipe kategori | `TransactionRequest::after` |
| Hapus | konfirmasi dialog, lalu hapus | `components/confirm-delete` |
| Format uang | `Rp 1.250.000`, tanda +/- dan ikon panah | `Support/Money`, `components/amount` |

## Dashboard

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Pemilih bulan | default bulan berjalan, bisa diganti lewat `?month=YYYY-MM` | `Requests/DashboardRequest` |
| Tiga kartu | pemasukan, pengeluaran, saldo bulan terpilih | `Support/MonthlySummary` |
| Grafik | doughnut pengeluaran per kategori, plus daftar angkanya | `resources/js/chart.js`, `views/dashboard` |
| Saldo per dompet | total pemasukan - pengeluaran sepanjang waktu | `DashboardController::walletBalances` |
| Transaksi terakhir | 5 baris terbaru | `DashboardController` |

## Admin

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Daftar akun | nama, email, peran, jumlah transaksi, status | `Admin/UserController@index` |
| Nonaktif / aktifkan | langsung berlaku; akun nonaktif tidak bisa login | `Admin/UserController` + `User::deactivate/activate` |
| Hapus akun | menghapus transaksi dulu lalu akun (dompet & kategori ikut cascade) | `Admin/UserController@destroy` |
| Proteksi | tidak bisa menonaktifkan/menghapus diri sendiri, tidak bisa menghilangkan admin aktif terakhir | `Admin/UserController` |

## Lintas fitur

| Fitur | Perilaku | Implementasi |
| --- | --- | --- |
| Pesan flash | sukses (hijau) dan gagal (merah) di satu komponen | `components/flash` |
| Error page | 403, 404, 419, 429, 500 Bahasa Indonesia | `views/errors`, `components/error-page` |
| Navigasi | sidebar desktop, bottom bar mobile, item aktif ditandai | `components/layouts/app` |
| Validasi | pesan Bahasa Indonesia, error per field, input lama dipertahankan | `lang/id/validation.php`, `components/input` |
| Data demo | 1 admin, 2 user, 5 dompet, 16 kategori, 60 transaksi Agustus-Oktober 2026 | `database/seeders` |
