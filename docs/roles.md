# Hak akses per peran

Dua peran: `user` (hasil registrasi) dan `admin` (hanya dari seeder). Keduanya boleh
mengelola data keuangannya sendiri; admin mendapat tambahan pengelolaan akun.

| Aksi | Tamu | `user` | `admin` |
| --- | :---: | :---: | :---: |
| Lihat form login / register | ya | dialihkan ke dashboard | dialihkan ke dashboard |
| Daftar akun baru | ya | — | — |
| Masuk | ya | — | — |
| Keluar | — | ya | ya |
| Dashboard pribadi | — | ya | ya |
| Lihat/ubah/hapus dompet sendiri | — | ya | ya |
| Lihat/ubah/hapus kategori sendiri | — | ya | ya |
| Lihat/ubah/hapus transaksi sendiri | — | ya | ya |
| Lihat transaksi pengguna lain | — | tidak (403) | **tidak (403)** |
| Lihat daftar semua akun | — | tidak (403) | ya |
| Nonaktifkan / aktifkan akun | — | tidak (403) | ya, kecuali dirinya sendiri |
| Hapus akun | — | tidak (403) | ya, kecuali dirinya sendiri dan admin aktif terakhir |

## Bagaimana aturan itu ditegakkan

| Aturan | Tempat | Bukti |
| --- | --- | --- |
| Halaman butuh login | middleware `auth`, `routes/web.php:25` | test "guests are redirected" di tiap suite |
| Hanya admin di `/admin` | `EnsureUserIsAdmin` + alias `admin` di `bootstrap/app.php` | `tests/Feature/Admin/UserManagementTest.php` |
| Hanya pemilik yang boleh ubah/hapus | `WalletPolicy`, `CategoryPolicy`, `TransactionPolicy` | test IDOR per resource |
| Daftar hanya berisi data sendiri | query dari `$user->wallets()` / `->categories()` / `->transactions()` | test "index lists only..." |
| Admin tidak membaca data keuangan user lain | tidak ada rute admin untuk itu; policy tanpa pengecualian | `test_an_admin_still_cannot_open_another_users_transaction` |
| Akun nonaktif tidak bisa masuk | `LoginRequest::authenticate` (deactivated_at di kredensial) | `Auth/LoginTest::test_a_deactivated_user_cannot_log_in` |
| Sesi akun nonaktif diputus | `EnsureUserIsActive` | `Auth/DeactivatedUserTest` |
| Admin tidak bisa menghapus diri sendiri / admin aktif terakhir | `Admin\UserController` | 3 test terkait di `UserManagementTest` |

## Catatan

- Registrasi **selalu** menghasilkan `user`; `role` tidak mass-assignable sehingga tidak
  bisa dikirim lewat form (`tests/Feature/Security/MassAssignmentTest.php`).
- Admin melihat **jumlah** transaksi tiap akun, bukan isinya. Ini keputusan privasi yang
  dicatat di `docs/decisions.md` D42.
