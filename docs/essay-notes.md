# Video essay notes

Talking points per topic. Every bullet points at a file and line that exists in the repository,
so nothing here has to be remembered from scratch.

## 1. One request, from click to rendered page

- Every URL is registered in `routes/web.php`. `routes/web.php:28` registers the whole
  transaction CRUD with a single `Route::resource` call.
- Before a controller runs, the request passes middleware: `routes/web.php:17` (the `guest`
  group, with `throttle` on lines 19 and 22) and `routes/web.php:25` (the `auth` group).
- `bootstrap/app.php:24` adds `EnsureUserIsActive` to every web request, and
  `bootstrap/app.php:28` registers the `admin` alias.
- The controller validates through a FormRequest, persists through Eloquent, then returns a
  view. Complete example: `app/Http/Controllers/WalletController.php:31` (`store`).
- Longer example: `app/Http/Controllers/TransactionController.php:18` (`index`) — filter
  validation, query, pagination, view.

## 2. The MVC pattern in this app

- **Model** holds data and query rules: `app/Models/Transaction.php:69` (`scopeForMonth`) and
  `:82` (`scopeOfType`), so the date and type filters are never rewritten in a controller.
- **View** is pure Blade with no queries: `resources/views/transactions/index.blade.php` only
  renders variables handed over by the controller.
- **Controller** stays thin: validate, authorize, save, redirect. For example
  `app/Http/Controllers/WalletController.php:59` only handles the delete rule.
- Blade components avoid repeated markup: `resources/views/components/td.blade.php:3` turns a
  table cell into a labelled card on small screens.
- Only two helper classes, both justified: `app/Support/MonthlySummary.php:26` (dashboard
  aggregation) and `app/Support/Money.php:14` (rupiah formatting).

## 3. Database design

- Four domain tables: `users`, `wallets`, `categories`, `transactions`
  (`database/migrations/`).
- Relationships are enforced by the database:
  `database/migrations/2026_10_31_000003_create_transactions_table.php:16` uses
  `restrictOnDelete()` for wallet and category, while line 15 uses `cascadeOnDelete()` for the
  owner.
- Unique names per owner: `database/migrations/2026_10_31_000001_create_wallets_table.php:19`
  (`unique(['user_id', 'name'])`); categories use
  `database/migrations/2026_10_31_000002_create_categories_table.php:20`
  (`unique(['user_id', 'name', 'type'])`).
- Main index for the transaction list:
  `database/migrations/2026_10_31_000003_create_transactions_table.php:27`
  (`index(['user_id','transacted_on'])`).
- Money is stored as whole rupiah:
  `database/migrations/2026_10_31_000003_create_transactions_table.php:19`
  (`unsignedBigInteger('amount')`) — no float anywhere.

## 4. Validation

- One FormRequest per resource, shared by store and update:
  `app/Http/Requests/WalletRequest.php:22` and `app/Http/Requests/TransactionRequest.php:24`.
- Foreign keys are never trusted from the form: `app/Http/Requests/TransactionRequest.php:31`
  uses `Rule::exists('wallets','id')->where('user_id', $user->id)`.
- The "transaction type equals category type" invariant is enforced in
  `app/Http/Requests/TransactionRequest.php:46` (`after()`), with the message on line 59.
- Messages are Indonesian: `lang/id/validation.php:11` and the following entries; attribute
  names are translated in the `attributes` section so the messages read naturally.
- Proof: `tests/Feature/TransactionTest.php` (a formatted `1.250.000` is rejected, a future
  date is rejected, another user's wallet or category is rejected, a type mismatch is
  rejected).

## 5. Error handling

- Validation errors return to the form with the old input:
  `resources/views/components/input.blade.php:20` renders `@error` per field.
- Missing records produce a 404 through route model binding instead of a blank page:
  `routes/web.php:30` (the `{wallet}` parameter).
- Deleting a wallet or category that is still in use is refused with a message:
  `app/Http/Controllers/WalletController.php:61`.
- Dedicated error pages: `resources/views/errors/404.blade.php` and friends, rendered by
  `resources/views/components/error-page.blade.php`.
- Proof: `tests/Feature/ErrorPageTest.php` (404 and 403 live, 419/429/500 rendered).

## 6. CSRF

- Every form that changes data carries `@csrf`, for example
  `resources/views/wallets/partials/form.blade.php:10`.
- Update and delete forms use method spoofing: `@method('PUT')` on line 13 of the same file,
  and `@method('DELETE')` in `resources/views/components/confirm-delete.blade.php:32`.
- Logout is a POST too: `resources/views/components/layouts/app.blade.php:80`.
- There is no CSRF exemption in `bootstrap/app.php`.
- Proof: `tests/Feature/Security/CsrfTokenTest.php`; on the running app a `POST /login` without
  a token returns **419**.

## 7. XSS

- All output uses `{{ }}`; `{!! !!}` appears nowhere in `resources/views`.
- Tested case: a transaction note containing `<script>alert(1)</script>` is still displayed as
  text — `tests/Feature/Security/XssTest.php:34`.
- String lengths are capped in validation (`max:255` for the note, `max:100` for a name) so odd
  input is rejected rather than silently truncated:
  `app/Http/Requests/TransactionRequest.php:36`.

## 8. SQL injection

- Every query goes through Eloquent or the query builder, so values are bound; see
  `app/Http/Controllers/TransactionController.php:28`.
- The only raw SQL is in `app/Support/MonthlySummary.php:41`
  (`selectRaw('category_id, SUM(amount) as total')`), and it contains a constant expression
  with no user value.
- Filters are validated before they reach a query:
  `app/Http/Requests/TransactionFilterRequest.php:32`.
- Proof: `tests/Feature/Security/InputValidationTest.php:20` sends
  `month=2026-08' OR '1'='1` and the `date_format` rule rejects it.

## 9. Authentication and sessions

- Login uses `Auth::attempt` with already validated credentials:
  `app/Http/Requests/Auth/LoginRequest.php:32`.
- The session is regenerated after login (`app/Http/Controllers/Auth/LoginController.php:23`),
  which makes session fixation impossible.
- Logout invalidates the session and regenerates the CSRF token:
  `app/Http/Controllers/Auth/LogoutController.php:18`.
- A deactivated account is logged out on its next request:
  `app/Http/Middleware/EnsureUserIsActive.php:18`.
- Session cookie: `database` driver, `http_only`, `same_site=lax` —
  `config/session.php:21,185,202`; `secure` comes from the environment in production (line 172).
- Proof: `tests/Feature/Security/SessionConfigTest.php` and `tests/Feature/Auth/LogoutTest.php`.

## 10. Authorization (IDOR)

- Lists are always built from the owner relationship:
  `app/Http/Controllers/WalletController.php:22`.
- The id from the URL is checked by a policy: `app/Http/Controllers/WalletController.php:59`
  calls `$this->authorize('delete', $wallet)`, implemented in
  `app/Policies/WalletPolicy.php:19`.
- Transactions: `app/Policies/TransactionPolicy.php:14` — with no admin exception, following the
  privacy decision recorded as D42 in `docs/decisions.md`.
- Admin routes are guarded by middleware: `app/Http/Middleware/EnsureUserIsAdmin.php:16` →
  `abort(403)`.
- Proof: one IDOR test per resource plus `tests/Feature/Admin/UserManagementTest.php:61`.

## 11. Passwords and throttling

- Passwords are hashed by the `hashed` cast in `app/Models/User.php:50`; the plain value is never
  stored.
- Password rules: `app/Http/Requests/Auth/RegisterRequest.php:39`
  (`Password::min(8)->mixedCase()->numbers()`).
- Email is lowercased before validation so the unique rule cannot be bypassed:
  `app/Http/Requests/Auth/RegisterRequest.php:24`.
- The failed-login message is always the same, including for a deactivated account:
  `app/Http/Requests/Auth/LoginRequest.php:37` (the status is part of the credentials).
- Throttling at route level: `routes/web.php:22` (5 per minute) and `:19` (6 per minute).
- Proof: `tests/Feature/Auth/LoginTest.php` — identical message, deactivated account fails, the
  sixth attempt returns 429.

## 12. Deployment and environment configuration

- Production image: `docker/Dockerfile`, `production` target — single-process Apache, runs as
  `www-data`, listens on `$PORT`.
- Secrets never enter the repository: `.env` is ignored by `.gitignore:7`, and `.env.example`
  lists every key with a placeholder.
- Behind a TLS proxy: `bootstrap/app.php:21` (`trustProxies`) and
  `app/Providers/AppServiceProvider.php:21` (`forceScheme('https')`).
- Automated deploy: `.github/workflows/deploy.yml` — SSH from the runner, `git reset --hard`,
  build, `migrate --force`, then `up -d`.
- The full steps and the problems found along the way are in `docs/deployment.md`.

## 13. Real troubleshooting stories from this build

- **`ERROR 1451` when deleting a user.** The schema cascades on `user_id` but restricts
  `wallet_id`/`category_id`, so MySQL refused. The fix deletes the transactions first inside
  `DB::transaction` — `app/Http/Controllers/Admin/UserController.php:71`. Evidence before the
  fix: `DELETE FROM users ...` → `ERROR 1451 (... transactions_category_id_foreign)`.
- **Green tests, 500 page.** The error flash called `<x-icon>` without a `name`, so the page blew
  up while the test only inspected the session. Found by walking the delete flow with `curl`.
  Fix: an `alert` icon plus a test that actually renders the page
  (`tests/Feature/WalletTest.php`, the "cannot be deleted" case).
- **`subMonth()` on the 31st.** A dashboard test failed because `now()->subMonth()` produced the
  1st of the same month (PHP overflow). The app was right, the test was wrong — fixed with
  `now()->startOfMonth()->subDay()` (`tests/Feature/DashboardSummaryTest.php`).
- **Larastan does not read `casts()`.** Enum columns were read as `string`, so the type
  comparison in the FormRequest looked always true. Fix: `@property` tags on the model
  (`app/Models/Category.php:15`).
- **`compose run` does not wait for MySQL.** The deploy step ran `migrate` right after `build`
  and hit `Connection refused`, because `depends_on: service_healthy` is not honoured by
  `compose run` on every implementation. Fix: start `db` explicitly and poll `mysqladmin ping`
  before migrating (`.github/workflows/deploy.yml`, deploy step).
- **A CI step that failed with no message.** The deploy step printed only
  `Warning: Permanently added '...' to the list of known hosts.` and exited non-zero. The
  runner log needs authentication to read, but the annotation does not, so the step was changed
  to put the exit status and the output tail into the annotation; that showed
  `(exit 127) Output: sh: bash: not found`. The server has no `bash`. Fix: both remote scripts
  are POSIX `sh` (`set -eu`, a counted `while` loop instead of `seq`) and are invoked with
  `sh -s` (`.github/workflows/deploy.yml`). Lesson: an empty error is still data — make the
  pipeline say what it saw.

## 14. Essay questions from the lecturer

<!-- paste the actual questions here, then answer them one by one -->
