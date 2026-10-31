# Struktur MVC

Pohon direktori yang benar-benar ada di repo, satu baris keterangan per folder atau file
penting.

```text
app/
├── Enums/
│   ├── TransactionType.php        income/expense + label() dan icon() untuk tampilan
│   └── UserRole.php               admin/user + label()
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php         kelas dasar, menyertakan AuthorizesRequests
│   │   ├── DashboardController.php  ringkasan bulan + saldo dompet + 5 transaksi
│   │   ├── TransactionController.php CRUD transaksi + filter daftar
│   │   ├── WalletController.php   CRUD dompet
│   │   ├── CategoryController.php CRUD kategori
│   │   ├── Admin/UserController.php  daftar akun, aktif/nonaktif, hapus
│   │   └── Auth/
│   │       ├── LoginController.php   form + autentikasi + regenerate sesi
│   │       ├── LogoutController.php  invalidate sesi + regenerate token CSRF
│   │       └── RegisterController.php  buat akun + login otomatis
│   ├── Middleware/
│   │   ├── EnsureUserIsActive.php  logout paksa untuk akun yang dinonaktifkan
│   │   └── EnsureUserIsAdmin.php   403 untuk non-admin di /admin
│   └── Requests/
│       ├── Auth/LoginRequest.php        kredensial + pesan gagal generik
│       ├── Auth/RegisterRequest.php     email lowercase, unique, aturan kata sandi
│       ├── CategoryRequest.php          nama + tipe, unik per (user, nama, tipe)
│       ├── DashboardRequest.php         validasi bulan (Y-m)
│       ├── TransactionFilterRequest.php validasi filter daftar
│       ├── TransactionRequest.php       kepemilikan FK, nominal, tanggal, tipe = kategori
│       └── WalletRequest.php            nama unik per pemilik
├── Models/
│   ├── User.php            role/deactivated_at cast, relasi, deactivate/activate
│   ├── Wallet.php          relasi user + transactions
│   ├── Category.php        cast tipe, relasi
│   └── Transaction.php     cast tipe/tanggal, scope forMonth & ofType
├── Policies/
│   ├── WalletPolicy.php      update/delete hanya pemilik
│   ├── CategoryPolicy.php    idem
│   └── TransactionPolicy.php idem (tanpa pengecualian admin)
├── Providers/AppServiceProvider.php  preventLazyLoading, forceScheme https di produksi
└── Support/
    ├── Money.php           satu-satunya formatter "Rp 1.250.000"
    └── MonthlySummary.php  agregasi bulanan (sum + groupBy)

bootstrap/app.php        routing, alias middleware, trusted proxy, error handling
config/                  konfigurasi; hanya MySQL dan driver sync yang tersisa
database/
├── factories/           data uji untuk empat model
├── migrations/          users, sessions, cache, wallets, categories, transactions
├── schema/catatku.sql   dump struktur + data demo (bisa diimpor sendiri)
└── seeders/             1 admin, 2 user demo, 5 dompet, 16 kategori, 60 transaksi

lang/id/                 pesan validasi dan pagination Bahasa Indonesia
public/                  document root; build Vite masuk ke public/build
resources/
├── css/app.css          token desain: aksen teal, income/expense, font
├── js/app.js            Alpine (menu pengguna, dialog konfirmasi)
├── js/chart.js          Chart.js doughnut untuk pengeluaran per kategori
└── views/
    ├── components/      layout, table, td, tr, badge, input, select, button, dll.
    ├── auth/            login, register
    ├── transactions/    index, create, edit, partials/form
    ├── wallets/         index, create, edit, partials/form
    ├── categories/      index, create, edit, partials/form
    ├── admin/users/     index
    ├── errors/          403, 404, 419, 429, 500
    └── dashboard.blade.php

routes/web.php           semua rute web (29 rute)
tests/Feature/           test fitur per resource, auth, keamanan, dashboard, admin
docker/                  Dockerfile (dev + production), vhost Apache, init.sql MySQL
docker-compose.yml       stack pengembangan (app + MySQL)
docker-compose.production.yml  stack server (app production + MySQL)
.github/workflows/deploy.yml   deploy otomatis lewat SSH
```

## Alur data satu halaman

`routes/web.php` → middleware → `Controller` → `FormRequest` (validasi) → `Policy`
(otorisasi) → `Model` (Eloquent) → `Blade view` → browser. Detailnya ada di
[`architecture.md`](architecture.md).
