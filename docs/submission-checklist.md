# Checklist submission

Rubrik → bukti, daftar screenshot yang harus diambil manual, dan kerangka PDF.

## 1. Rubrik → bukti

| Komponen (bobot) | Bukti di repo / URL | Cara menunjukkan |
| --- | --- | --- |
| Desain (15%) | `resources/css/app.css` (token), `resources/views/components/*`, `docs/features.md` | screenshot dashboard, daftar transaksi, form dengan error, tampilan 375px |
| Implementasi + database (35%) | `app/`, `database/migrations/`, `database/schema/catatku.sql`, `docs/erd.md` | ERD, `php artisan migrate:fresh --seed`, screenshot CRUD |
| Keamanan + sesi (15%) | `docs/security.md` (28 item → file → test), `tests/Feature/Security/` | tabel audit + output `php artisan test` |
| Deployment (10%) | `docs/deployment.md`, `docker-compose.production.yml`, `.github/workflows/deploy.yml` | screenshot URL live, screenshot halaman Actions |
| Video esai (25%) | `docs/essay-notes.md` | video, mengikuti poin per bagian |

## 2. Bukti per butir yang diminta dosen

| Butir | File |
| --- | --- |
| ERD | `docs/erd.md` (Mermaid, bisa dirender di GitHub) |
| Flowchart / arsitektur | `docs/architecture.md` (Mermaid alur request + tabel routing) |
| Struktur MVC | `docs/mvc-structure.md` |
| Hak akses per peran | `docs/roles.md` |
| Daftar fitur | `docs/features.md` |
| CRUD + screenshot | lihat daftar screenshot di bawah |
| Migrasi | `database/migrations/` (6 file) |
| Penggunaan Eloquent | `app/Models/` + `app/Support/MonthlySummary.php` |
| Validasi | `app/Http/Requests/` + `lang/id/validation.php` |
| CSRF / XSS | `docs/security.md` baris 1–5 + `tests/Feature/Security/` |
| Autentikasi | `app/Http/Controllers/Auth/`, `app/Http/Requests/Auth/` |
| Role & policy | `app/Policies/`, `app/Http/Middleware/EnsureUserIsAdmin.php`, `docs/roles.md` |
| URL live | _isi setelah Phase 10_ |
| Langkah deployment | `docs/deployment.md` |
| File `.sql` | `database/schema/catatku.sql` |

## 3. Screenshot yang perlu kamu ambil

Semua halaman bisa diakses setelah `docker compose up` di <http://localhost:8001>.

| # | Halaman | URL | Yang dibuktikan |
| --- | --- | --- | --- |
| 1 | Login | `/login` | form + pesan error saat salah kata sandi |
| 2 | Register | `/register` | aturan kata sandi terlihat |
| 3 | Dashboard | `/dashboard` | 3 kartu, grafik, saldo dompet, 5 transaksi |
| 4 | Dashboard, bulan lain | `/dashboard?month=2026-08` | filter bulan bekerja |
| 5 | Daftar transaksi | `/transactions` | tabel, filter, pagination, format rupiah |
| 6 | Transaksi + filter | `/transactions?month=2026-10&type=expense` | filter bertahan di URL |
| 7 | Form transaksi (error) | `/transactions/create` | pesan validasi tipe tidak cocok / nominal |
| 8 | Hapus (dialog) | `/transactions` | modal konfirmasi |
| 9 | Dompet | `/wallets` | daftar + jumlah transaksi |
| 10 | Hapus dompet ditolak | `/wallets` | pesan "masih punya transaksi" |
| 11 | Kategori | `/categories` | badge Pemasukan/Pengeluaran dengan ikon |
| 12 | Admin | `/admin/users` | daftar akun, status, aksi |
| 13 | Admin, akun nonaktif | `/admin/users` | status Nonaktif + tombol Aktifkan |
| 14 | 404 | `/halaman-tidak-ada` | halaman error Indonesia |
| 15 | 403 | login sebagai user lalu buka `/admin/users` | halaman 403 Indonesia |
| 16 | 419 | submit login tanpa token (atau biarkan sesi kedaluwarsa) | halaman 419 |
| 17 | Mobile 375px | `/dashboard` dengan lebar 375px | bottom navigation, kartu bertumpuk |
| 18 | Struktur DB | `mysql` / GUI di `127.0.0.1:3307` | tabel + foreign key |
| 19 | Test hijau | terminal `php artisan test` | 106 test lulus |
| 20 | URL live | `https://<domain>/dashboard` | bukti deploy + HTTPS |

Tips: ambil screenshot penuh (bukan potongan), tampilkan URL di address bar kalau memungkinkan,
dan simpan dengan nama `01-login.png`, `02-register.png`, dst. supaya mudah disusun di PDF.

## 4. Kerangka PDF

1. **Identitas** — judul "Catatku — Aplikasi Pencatat Keuangan Pribadi", nama, NIM, kelas,
   tautan repo dan URL live.
2. **Deskripsi singkat** — masalah yang dipecahkan, 5 fitur utama, stack (Laravel 13, MySQL 8.4,
   Blade/Tailwind/Alpine/Chart.js).
3. **ERD** — gambar dari `docs/erd.md` + satu paragraf tentang relasi dan constraint.
4. **Flowchart** — gambar dari `docs/architecture.md` + penjelasan satu alur request.
5. **Struktur MVC** — ringkasan `docs/mvc-structure.md`.
6. **Hak akses** — tabel dari `docs/roles.md`.
7. **Daftar fitur** — tabel dari `docs/features.md` (boleh diringkas per modul).
8. **Screenshot** — 10–12 gambar terpenting dari daftar di atas, masing-masing satu baris
   keterangan "apa yang dibuktikan".
9. **Keamanan** — ringkasan `docs/security.md`: CSRF, XSS, SQL injection, mass assignment,
   auth, session, otorisasi; sebutkan test yang men proof.
10. **Deployment** — langkah dari `docs/deployment.md` + screenshot URL live dan halaman
    Actions.
11. **Penutup** — kendala dan rencana pengembangan (misalnya reset kata sandi, ekspor CSV).

## 5. Sebelum mengumpulkan

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
```

- [ ] Semua perintah di atas hijau.
- [ ] `database/schema/catatku.sql` sudah diperbarui kalau skema berubah
      (`mysqldump ... catatku migrations users wallets categories transactions`).
- [ ] Tautan repo dan URL live diisi di README, PDF, dan formulir pengumpulan.
- [ ] Video esai direkam mengikuti `docs/essay-notes.md`.
- [ ] Tidak ada `.env`, `vendor/`, `node_modules/`, `public/build` yang ter-commit.
