# Security audit

Every item from the brief's security checklist, where it is implemented, and the test
that proves it. Run the suite with `docker compose exec app php artisan test`.

| # | Item | Implementation | Proof |
| --- | --- | --- | --- |
| 1 | CSRF token on every state-changing form | `components/layouts/app.blade.php` (logout), `auth/login`, `auth/register`, the three `partials/form.blade.php`, `components/confirm-delete`, `admin/users/index` | `Security/CsrfTokenTest` covers every page with a form; live `POST /login` without a token returns 419 with the Indonesian page |
| 2 | Method spoofing for PUT/DELETE | `@method('PUT')` in the wallet, category and transaction form partials; `@method('DELETE')` in `confirm-delete` | `Security/CsrfTokenTest::test_data_changing_forms_use_method_spoofing` |
| 3 | No CSRF exemptions | `bootstrap/app.php` registers no exception for any path | live 419 (above); `grep -rn "except" bootstrap/` finds nothing |
| 4 | XSS: escaped output only | `{{ }}` everywhere; `{!! !!}` appears in no view; `max` rules on every string | `Security/XssTest` (note, wallet name, category name); `grep -rn "{!!" resources/` returns nothing |
| 5 | SQL injection: bound queries | Eloquent/Query Builder only; the single `selectRaw` in `MonthlySummary` is a constant expression with no interpolated value | `Security/InputValidationTest` (two injection attempts rejected by the filter rules) |
| 6 | Mass assignment | `$fillable` on every model, `role` and `deactivated_at` excluded; controllers use `$request->validated()` and the owner relationship | `Security/MassAssignmentTest` (role on register, `user_id` on transactions, wallets, categories) |
| 7 | Passwords hashed | `password` cast to `hashed`, `Auth::attempt` on login | `Auth/RegistrationTest::test_a_guest_can_register` asserts `Hash::check()` |
| 8 | Session regenerated on login | `LoginController::store` -> `$request->session()->regenerate()` | `Security/SessionConfigTest::test_the_session_id_changes_after_login` |
| 9 | Session invalidated on logout | `LogoutController::destroy` -> `invalidate()` + `regenerateToken()` | `Auth/LogoutTest::test_logout_invalidates_the_session_and_the_csrf_token` |
| 10 | Login throttled | `throttle:5,1` on `POST /login` (`routes/web.php`) | `Auth/LoginTest::test_login_attempts_are_throttled`; live 429 + `Retry-After: 59` |
| 11 | Registration throttled | `throttle:6,1` on `POST /register` | same middleware, documented in `docs/deployment.md` |
| 12 | One generic failed-login message | `LoginRequest::authenticate()` always throws "Email atau kata sandi salah." | `Auth/LoginTest::test_an_unknown_email_gives_the_same_message_as_a_wrong_password` |
| 13 | Strong password rule | `Password::min(8)->mixedCase()->numbers()` in `RegisterRequest` | `Auth/RegistrationTest::test_the_password_must_meet_the_strength_rules` |
| 14 | Deactivated users cannot log in | `deactivated_at => null` inside the `Auth::attempt` credentials | `Auth/LoginTest::test_a_deactivated_user_cannot_log_in` |
| 15 | Deactivated session is terminated | `EnsureUserIsActive` appended to the `web` group | `Auth/DeactivatedUserTest` |
| 16 | Session cookie flags | `config/session.php`: database driver, `http_only`, `same_site=lax`, lifetime 120, `secure` from `SESSION_SECURE_COOKIE` (true on the server) | `Security/SessionConfigTest::test_the_session_cookie_is_hardened`; the `secure` flag was observed on a production container (`docs/deployment.md`) |
| 17 | IDOR: wallets | `WalletPolicy` + queries built from `$user->wallets()` | `WalletTest::test_a_user_cannot_read_update_or_delete_another_users_wallet` |
| 18 | IDOR: categories | `CategoryPolicy` + scoped queries | `CategoryTest::test_a_user_cannot_read_update_or_delete_another_users_category` |
| 19 | IDOR: transactions | `TransactionPolicy` + scoped queries | `TransactionTest::test_a_user_cannot_read_update_or_delete_another_users_transaction` |
| 20 | Foreign wallet/category ids in a transaction request | `Rule::exists('wallets', 'id')->where('user_id', ...)` and the same for categories | `TransactionTest::test_the_wallet_and_category_must_belong_to_the_authenticated_user` |
| 21 | Type must equal the category type | `TransactionRequest::after()` compares the two | `TransactionTest::test_the_transaction_type_must_match_the_category_type` |
| 22 | Admin routes need the role | `EnsureUserIsAdmin` behind the `admin` alias | `Admin/UserManagementTest::test_a_regular_user_gets_a_forbidden_response` |
| 23 | Admin cannot read other users' transactions | No admin route reaches them; `TransactionPolicy` has no admin exemption | `Admin/UserManagementTest::test_an_admin_still_cannot_open_another_users_transaction` |
| 24 | Admin cannot remove themselves or the last admin | `Admin\UserController` guards | `Admin/UserManagementTest` (self cases + deactivated admin case) |
| 25 | Guests are redirected to login | `auth` middleware on the authenticated group | `ApplicationBootTest`, and the "guests are redirected" test in each resource suite |
| 26 | Production debug off, HTTPS forced | `APP_DEBUG=false` / `APP_ENV=production` in the server `.env`; `trustProxies('*')` and `URL::forceScheme('https')` | verified on a production container: `https` redirects, 404 without a stack trace (`docs/deployment.md` §1) |
| 27 | Secrets never committed | `.env` is gitignored, `.env.example` lists every key with placeholders, `APP_KEY` generated per environment | `git check-ignore -v .env` -> `.gitignore:7:.env`; `git log --all -- .env` is empty |
| 28 | Friendly error pages | `resources/views/errors/{403,404,419,429,500}.blade.php` via `x-error-page` | `ErrorPageTest` (403, 404 live; 419, 429, 500 rendered) |

## Out of scope by design

Password reset, email verification, remember-me and API tokens are not implemented, so
their tables and columns do not exist (`docs/decisions.md` D7). Registration always
creates a plain `user`; the admin account comes from the seeder.

## Commands used for the audit

```bash
grep -rn "{!!" resources/ app/            # no raw output
grep -rn "DB::raw\|whereRaw" app/         # none
grep -rn "env(" app/ routes/ bootstrap/   # none outside config/
grep -rn "request()->all()" app/          # none
grep -rn "TODO\|FIXME\|dd(\|dump(" app/  # none
git check-ignore -v .env                  # .gitignore:7:.env
curl -i -X POST http://localhost:8001/login -d 'email=x&password=y'   # 419
```
