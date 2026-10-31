# Catatan untuk video esai

Bahan bicara per bagian, dalam Bahasa Indonesia sederhana. Setiap poin menunjuk ke file
dan baris yang benar-benar ada di repo.

## 1. Alur satu request (dari klik sampai halaman jadi)

- Semua alamat web didaftarkan di `routes/web.php`. Contoh: `routes/web.php:28` mendaftarkan
  seluruh CRUD transaksi dengan satu baris `Route::resource`.
- Sebelum masuk controller, request melewati middleware. `routes/web.php:17` (grup `guest`,
  ditambah `throttle` di baris 19 dan 22) dan `routes/web.php:25` (grup `auth`).
- `bootstrap/app.php:24` menambahkan `EnsureUserIsActive` ke semua request web, dan
  `bootstrap/app.php:28` mendaftarkan alias `admin`.
- Controller memvalidasi lewat FormRequest, menyimpan lewat Eloquent, lalu mengembalikan
  view. Contoh utuh: `app/Http/Controllers/WalletController.php:31` (`store`).
- Contoh yang lebih panjang: `app/Http/Controllers/TransactionController.php:18` (`index`)
  — validasi filter, query, pagination, view.

## 2. Pola MVC di aplikasi ini

- **Model** = data dan aturan query: `app/Models/Transaction.php:69` (`scopeForMonth`) dan
  `:82` (`scopeOfType`) supaya filter tanggal/tipe tidak ditulis ulang di controller.
- **View** = Blade murni tanpa query: `resources/views/transactions/index.blade.php`
  hanya menampilkan variabel dari controller.
- **Controller** = tipis: validasi, otorisasi, simpan, redirect. Contoh
  `app/Http/Controllers/WalletController.php:59` hanya mengurus aturan hapus.
- Komponen Blade menghindari markup berulang: `resources/views/components/td.blade.php:3`
  mengatur tabel yang berubah jadi kartu di layar kecil.
- Kelas bantu hanya dua dan beralasan: `app/Support/MonthlySummary.php:26` (agregasi
  dashboard) dan `app/Support/Money.php:14` (format rupiah).

## 3. Desain database

- Empat tabel domain: `users`, `wallets`, `categories`, `transactions`
  (`database/migrations/`).
- Relasi dijaga di level database: `database/migrations/2026_10_31_000003_create_transactions_table.php:16`
  memakai `restrictOnDelete()` untuk dompet dan kategori, sementara baris 15 memakai
  `cascadeOnDelete()` untuk pemilik.
- Nama unik per pemilik: `database/migrations/2026_10_31_000001_create_wallets_table.php:19`
  (`unique(['user_id', 'name'])`); kategori memakai
  `database/migrations/2026_10_31_000002_create_categories_table.php:20`
  (`unique(['user_id', 'name', 'type'])`).
- Indeks utama daftar transaksi:
  `database/migrations/2026_10_31_000003_create_transactions_table.php:27`
  (`index(['user_id','transacted_on'])`).
- Nominal uang disimpan sebagai bilangan bulat rupiah:
  `database/migrations/2026_10_31_000003_create_transactions_table.php:19`
  (`unsignedBigInteger('amount')`) — tidak ada float.

## 4. Validasi

- Satu FormRequest per resource dipakai store dan update: `app/Http/Requests/WalletRequest.php:22`
  dan `app/Http/Requests/TransactionRequest.php:24`.
- Kunci asing tidak dipercaya dari form: `app/Http/Requests/TransactionRequest.php:31`
  memakai `Rule::exists('wallets','id')->where('user_id', $user->id)`.
- Invarian "tipe transaksi = tipe kategori" ditegakkan di
  `app/Http/Requests/TransactionRequest.php:46` (`after()`), dengan pesan di baris 59.
- Pesan Bahasa Indonesia: `lang/id/validation.php:11` dan seterusnya; atribut diterjemahkan
  di bagian `attributes` supaya pesan terbaca wajar.
- Bukti: `tests/Feature/TransactionTest.php` (nominal `1.250.000` ditolak, tanggal masa
  depan ditolak, dompet/kategori orang lain ditolak, tipe tidak cocok ditolak).

## 5. Error handling

- Kesalahan validasi kembali ke form dengan input lama: `resources/views/components/input.blade.php:20`
  menampilkan `@error` per field.
- Data yang tidak ada menghasilkan 404 lewat route model binding, bukan halaman putih:
  `routes/web.php:30` (parameter `{wallet}`).
- Hapus dompet/kategori yang masih dipakai ditolak dengan pesan:
  `app/Http/Controllers/WalletController.php:61`.
- Halaman error sendiri: `resources/views/errors/404.blade.php` dan kawan-kawan, dirender
  oleh `resources/views/components/error-page.blade.php`.
- Bukti: `tests/Feature/ErrorPageTest.php` (404 dan 403 live, 419/429/500 dirender).

## 6. CSRF

- Setiap form yang mengubah data memasang `@csrf`, misalnya
  `resources/views/wallets/partials/form.blade.php:10`.
- Form ubah/hapus memakai method spoofing: `@method('PUT')` di baris 13 file yang sama, dan
  `@method('DELETE')` di `resources/views/components/confirm-delete.blade.php:32`.
- Logout juga POST: `resources/views/components/layouts/app.blade.php:80`.
- Tidak ada pengecualian CSRF di `bootstrap/app.php`.
- Bukti: `tests/Feature/Security/CsrfTokenTest.php`; di aplikasi nyata `POST /login` tanpa
  token menghasilkan **419**.

## 7. XSS

- Semua keluaran memakai `{{ }}`; `{!! !!}` tidak ada di seluruh `resources/views`.
- Contoh yang diuji: catatan transaksi berisi `<script>alert(1)</script>` tetap tampil
  sebagai teks — `tests/Feature/Security/XssTest.php:34`.
- Panjang string dibatasi di validasi (`max:255` catatan, `max:100` nama) supaya input
  aneh ditolak, bukan dipotong diam-diam: `app/Http/Requests/TransactionRequest.php:36`.

## 8. SQL injection

- Semua query lewat Eloquent/Query Builder sehingga nilainya di-bind, contoh
  `app/Http/Controllers/TransactionController.php:28`.
- Satu-satunya SQL mentah ada di `app/Support/MonthlySummary.php:41`
  (`selectRaw('category_id, SUM(amount) as total')`) dan isinya ekspresi tetap tanpa nilai
  dari user.
- Filter divalidasi sebelum menyentuh query: `app/Http/Requests/TransactionFilterRequest.php:32`.
- Bukti: `tests/Feature/Security/InputValidationTest.php:20` mencoba
  `month=2026-08' OR '1'='1` dan ditolak oleh aturan `date_format`.

## 9. Autentikasi dan sesi

- Login memakai `Auth::attempt` dengan kredensial yang sudah divalidasi:
  `app/Http/Requests/Auth/LoginRequest.php:32`.
- Sesi dibuat ulang setelah login (`app/Http/Controllers/Auth/LoginController.php:23`) agar
  session fixation tidak mungkin.
- Logout meng-invalidate sesi dan membuat token CSRF baru:
  `app/Http/Controllers/Auth/LogoutController.php:18`.
- Akun nonaktif diputus di request berikutnya: `app/Http/Middleware/EnsureUserIsActive.php:18`.
- Cookie sesi: driver `database`, `http_only`, `same_site=lax` —
  `config/session.php:21,185,202`; `secure` diambil dari env saat produksi (baris 172).
- Bukti: `tests/Feature/Security/SessionConfigTest.php` dan `tests/Feature/Auth/LogoutTest.php`.

## 10. Otorisasi (IDOR)

- Daftar selalu dibangun dari relasi pemilik: `app/Http/Controllers/WalletController.php:22`.
- Id dari URL diperiksa policy: `app/Http/Controllers/WalletController.php:59` memanggil
  `$this->authorize('delete', $wallet)`, isinya di `app/Policies/WalletPolicy.php:19`.
- Transaksi: `app/Policies/TransactionPolicy.php:14` — tanpa pengecualian admin, sesuai
  keputusan privasi di `docs/decisions.md` D42.
- Rute admin dijaga middleware: `app/Http/Middleware/EnsureUserIsAdmin.php:16` →
  `abort(403)`.
- Bukti: satu test IDOR per resource + `tests/Feature/Admin/UserManagementTest.php:61`.

## 11. Kata sandi dan pembatasan percobaan

- Kata sandi di-hash oleh cast `hashed` di `app/Models/User.php:50`; tidak pernah disimpan
  apa adanya.
- Aturan kata sandi: `app/Http/Requests/Auth/RegisterRequest.php:39`
  (`Password::min(8)->mixedCase()->numbers()`).
- Email dinormalkan ke huruf kecil sebelum validasi supaya aturan unik tidak bisa diakali:
  `app/Http/Requests/Auth/RegisterRequest.php:24`.
- Pesan gagal login selalu sama, termasuk untuk akun nonaktif:
  `app/Http/Requests/Auth/LoginRequest.php:37` (status ikut jadi bagian kredensial).
- Throttle di level rute: `routes/web.php:22` (5/menit) dan `:19` (6/menit).
- Bukti: `tests/Feature/Auth/LoginTest.php` — pesan identik, akun nonaktif gagal, percobaan
  ke-6 menghasilkan 429.

## 12. Deployment dan konfigurasi environment

- Image produksi: `docker/Dockerfile` target `production` — Apache satu proses, jalan sebagai
  `www-data`, mendengarkan `$PORT`.
- Rahasia tidak pernah masuk repo: `.env` diabaikan `.gitignore:7`, dan `.env.example`
  memuat semua kunci dengan nilai contoh.
- Di belakang proxy TLS: `bootstrap/app.php:21` (`trustProxies`) dan
  `app/Providers/AppServiceProvider.php:21` (`forceScheme('https')`).
- Deploy otomatis: `.github/workflows/deploy.yml` — runner SSH, `git reset --hard`, build,
  `migrate --force`, lalu `up -d`.
- Langkah lengkap dan masalah yang ditemukan ada di `docs/deployment.md`.

## 13. Cerita troubleshooting nyata (dari build ini)

- **`ERROR 1451` saat menghapus user.** Skema memakai `cascade` di `user_id` tetapi
  `restrict` di `wallet_id`/`category_id`, jadi MySQL menolak. Solusinya menghapus transaksi
  lebih dulu di dalam `DB::transaction` — `app/Http/Controllers/Admin/UserController.php:71`.
  Bukti sebelum diperbaiki: `DELETE FROM users ...` → `ERROR 1451 (... transactions_category_id_foreign)`.
- **Test hijau tapi halaman 500.** Flash error memanggil `<x-icon>` tanpa `name`; halaman
  meledak padahal test hanya memeriksa session. Ketemu saat mencoba alur delete dengan
  `curl`. Perbaikan: ikon `alert` + test yang benar-benar merender halaman
  (`tests/Feature/WalletTest.php`, kasus "cannot be deleted").
- **`subMonth()` pada tanggal 31.** Test dashboard gagal karena `now()->subMonth()`
  menghasilkan tanggal 1 di bulan yang sama (overflow PHP). Aplikasi benar, test salah —
  diperbaiki dengan `now()->startOfMonth()->subDay()`
  (`tests/Feature/DashboardSummaryTest.php`).
- **Larastan tidak membaca `casts()`.** Kolom enum terbaca `string`, sehingga perbandingan
  tipe di FormRequest dianggap selalu benar. Solusi: tag `@property` di model
  (`app/Models/Category.php:15`).

## 14. Pertanyaan esai dari dosen

<!-- tempel pertanyaan asli dari dosen di sini, lalu jawab satu per satu -->
