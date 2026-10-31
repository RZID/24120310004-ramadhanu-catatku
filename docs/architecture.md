# Architecture — Catatku

Aplikasi server-rendered: satu request masuk lewat router, melewati middleware, diarahkan
ke controller, divalidasi FormRequest, menyentuh Eloquent, lalu mengembalikan Blade atau
redirect. Tidak ada API, SPA, atau service layer.

## Alur satu request

```mermaid
flowchart TD
    A[Browser] --> B[public/index.php]
    B --> C[bootstrap/app.php]
    C --> D[Router routes/web.php]
    D --> E{Middleware grup web}
    E --> E1[StartSession]
    E1 --> E2[VerifyCsrfToken]
    E2 --> E3[EnsureUserIsActive]
    E3 --> E4{Middleware rute}
    E4 -->|guest| F1[Auth\\LoginController]
    E4 -->|auth| F2[Wallet/Category/Transaction/DashboardController]
    E4 -->|auth + admin| F3[Admin\\UserController]
    F1 --> G[FormRequest validasi]
    F2 --> G
    F3 --> H[Policy authorize / query milik user]
    G --> H
    H --> I[(Eloquent -> MySQL)]
    I --> J[Blade view / redirect]
    J --> K[Browser]
```

Contoh konkret untuk `GET /transactions?month=2026-08`:

1. `routes/web.php:28` — resource route di dalam grup `auth`.
2. `EnsureUserIsActive` (`app/Http/Middleware/EnsureUserIsActive.php`) — user nonaktif
   langsung di-logout.
3. `TransactionFilterRequest` — `month`, `type`, `category_id` divalidasi sebelum dipakai.
4. `TransactionController::index` — query dibangun dari `$user->transactions()`.
5. `scopeForMonth` (`app/Models/Transaction.php:69`) menambah rentang tanggal.
6. `paginate(15)->withQueryString()` mengembalikan paginator.
7. `resources/views/transactions/index.blade.php` merender tabel + filter + pagination.

## Tabel routing

Dihasilkan dari `php artisan route:list --except-vendor` (29 rute).

### Publik

| Method | URI | Nama | Handler | Middleware |
| --- | --- | --- | --- | --- |
| GET | `/` | — | redirect ke dashboard/login | web |
| GET | `/register` | `register` | `Auth\RegisterController@create` | guest |
| POST | `/register` | — | `Auth\RegisterController@store` | guest + `throttle:6,1` |
| GET | `/login` | `login` | `Auth\LoginController@create` | guest |
| POST | `/login` | — | `Auth\LoginController@store` | guest + `throttle:5,1` |

### Perlu login

| Method | URI | Nama | Handler |
| --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | `DashboardController` (invokable) |
| GET | `/transactions` | `transactions.index` | `TransactionController@index` |
| POST | `/transactions` | `transactions.store` | `TransactionController@store` |
| GET | `/transactions/create` | `transactions.create` | `TransactionController@create` |
| GET | `/transactions/{transaction}/edit` | `transactions.edit` | `TransactionController@edit` |
| PUT/PATCH | `/transactions/{transaction}` | `transactions.update` | `TransactionController@update` |
| DELETE | `/transactions/{transaction}` | `transactions.destroy` | `TransactionController@destroy` |
| GET | `/wallets` | `wallets.index` | `WalletController@index` |
| POST | `/wallets` | `wallets.store` | `WalletController@store` |
| GET | `/wallets/create` | `wallets.create` | `WalletController@create` |
| GET | `/wallets/{wallet}/edit` | `wallets.edit` | `WalletController@edit` |
| PUT/PATCH | `/wallets/{wallet}` | `wallets.update` | `WalletController@update` |
| DELETE | `/wallets/{wallet}` | `wallets.destroy` | `WalletController@destroy` |
| GET | `/categories` | `categories.index` | `CategoryController@index` |
| POST | `/categories` | `categories.store` | `CategoryController@store` |
| GET | `/categories/create` | `categories.create` | `CategoryController@create` |
| GET | `/categories/{category}/edit` | `categories.edit` | `CategoryController@edit` |
| PUT/PATCH | `/categories/{category}` | `categories.update` | `CategoryController@update` |
| DELETE | `/categories/{category}` | `categories.destroy` | `CategoryController@destroy` |
| POST | `/logout` | `logout` | `Auth\LogoutController@destroy` |

### Perlu login + role admin

| Method | URI | Nama | Handler |
| --- | --- | --- | --- |
| GET | `/admin/users` | `admin.users.index` | `Admin\UserController@index` |
| POST | `/admin/users/{user}/deactivate` | `admin.users.deactivate` | `Admin\UserController@deactivate` |
| POST | `/admin/users/{user}/activate` | `admin.users.activate` | `Admin\UserController@activate` |
| DELETE | `/admin/users/{user}` | `admin.users.destroy` | `Admin\UserController@destroy` |

## Lapisan dan tanggung jawab

| Lapisan | Lokasi | Isi |
| --- | --- | --- |
| Routing | `routes/web.php` | tiga grup: `guest`, `auth`, `auth` + `admin` |
| Middleware | `app/Http/Middleware`, `bootstrap/app.php` | `EnsureUserIsActive` (grup web), `EnsureUserIsAdmin` (alias `admin`) |
| Validasi | `app/Http/Requests` | satu request per resource, dipakai store dan update |
| Otorisasi | `app/Policies`, query ber-scope | policy untuk id dari URL, relasi user untuk daftar |
| Domain | `app/Models`, `app/Enums` | relasi, cast, scope, label/ikon enum |
| Agregasi | `app/Support` | `MonthlySummary`, `Money` |
| Tampilan | `resources/views` | layout, komponen, halaman per resource |

Tidak ada Repository, DTO, atau service layer: controller tipis, logika query ada di model,
dan satu-satunya kelas bantu adalah agregasi dashboard serta format uang.
