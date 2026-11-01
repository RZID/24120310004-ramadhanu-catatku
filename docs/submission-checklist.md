# Submission checklist

Rubric row to evidence, the screenshots to take by hand, and the PDF outline.

## 1. Rubric to evidence

| Component (weight) | Evidence in the repo / URL | How to show it |
| --- | --- | --- |
| Design (15%) | `resources/css/app.css` (tokens), `resources/views/components/*`, `docs/features.md` | screenshots of the dashboard, transaction list, a form with errors, and the 375px layout |
| Implementation + database (35%) | `app/`, `database/migrations/`, `database/schema/catatku.sql`, `docs/erd.md` | ERD, `php artisan migrate:fresh --seed`, CRUD screenshots |
| Security + session (15%) | `docs/security.md` (28 items to file to test), `tests/Feature/Security/` | the audit table plus the `php artisan test` output |
| Deployment (10%) | `docs/deployment.md`, `docker-compose.production.yml`, `.github/workflows/deploy.yml` | screenshot of the live URL and of the Actions page |
| Video essay (25%) | `docs/essay-notes.md` | the recording, following the points per section |

## 2. Evidence per required item

| Item | File |
| --- | --- |
| ERD | `docs/erd.md` (Mermaid, renders on GitHub) |
| Flowchart / architecture | `docs/architecture.md` (Mermaid request flow plus the routing table) |
| MVC structure | `docs/mvc-structure.md` |
| Permissions per role | `docs/roles.md` |
| Feature list | `docs/features.md` |
| CRUD plus screenshots | see the screenshot list below |
| Migrations | `database/migrations/` (6 files) |
| Eloquent usage | `app/Models/` plus `app/Support/MonthlySummary.php` |
| Validation | `app/Http/Requests/` plus `lang/id/validation.php` |
| CSRF / XSS | rows 1-5 of `docs/security.md` plus `tests/Feature/Security/` |
| Authentication | `app/Http/Controllers/Auth/`, `app/Http/Requests/Auth/` |
| Roles and policies | `app/Policies/`, `app/Http/Middleware/EnsureUserIsAdmin.php`, `docs/roles.md` |
| Live URL | _fill in after the first live deploy_ |
| Deployment steps | `docs/deployment.md` |
| The `.sql` file | `database/schema/catatku.sql` |

## 3. Screenshots to capture

Every page is reachable after `docker compose up` at <http://localhost:8001>.

| # | Page | URL | What it proves |
| --- | --- | --- | --- |
| 1 | Login | `/login` | the form plus the error message for a wrong password |
| 2 | Register | `/register` | the password rules are visible |
| 3 | Dashboard | `/dashboard` | three cards, chart, wallet balances, 5 transactions |
| 4 | Dashboard, another month | `/dashboard?month=2026-08` | the month filter works |
| 5 | Transaction list | `/transactions` | table, filters, pagination, rupiah format |
| 6 | Transactions with filters | `/transactions?month=2026-10&type=expense` | filters stay in the URL |
| 7 | Transaction form with errors | `/transactions/create` | the type mismatch and amount messages |
| 8 | Delete dialog | `/transactions` | the confirmation modal |
| 9 | Wallets | `/wallets` | the list plus transaction counts |
| 10 | Wallet delete refused | `/wallets` | the "still has transactions" message |
| 11 | Categories | `/categories` | income/expense badges with icons |
| 12 | Admin | `/admin/users` | account list, status, actions |
| 13 | Admin, deactivated account | `/admin/users` | "Nonaktif" status plus the activate button |
| 14 | 404 | `/halaman-tidak-ada` | the Indonesian error page |
| 15 | 403 | log in as a user, open `/admin/users` | the Indonesian 403 page |
| 16 | 419 | submit the login form without a token (or let the session expire) | the 419 page |
| 17 | Mobile at 375px | `/dashboard` at 375px wide | bottom navigation, stacked cards |
| 18 | Database structure | `mysql` or a GUI on `127.0.0.1:3307` | tables plus foreign keys |
| 19 | Green tests | terminal running `php artisan test` | 106 tests passing |
| 20 | Live URL | `https://<domain>/dashboard` | deployment plus HTTPS |

Tip: take full screenshots rather than crops, show the URL in the address bar when possible, and
name them `01-login.png`, `02-register.png` and so on so the PDF is easy to assemble.

## 4. PDF outline

1. **Identity** — title "Catatku — Aplikasi Pencatat Keuangan Pribadi", name, student number,
   class, repository and live URL.
2. **Short description** — the problem, the five main features, the stack (Laravel 13, MySQL 8.4,
   Blade/Tailwind/Alpine/Chart.js).
3. **ERD** — the diagram from `docs/erd.md` plus one paragraph about relationships and
   constraints.
4. **Flowchart** — the diagram from `docs/architecture.md` plus one request explained.
5. **MVC structure** — a summary of `docs/mvc-structure.md`.
6. **Permissions** — the table from `docs/roles.md`.
7. **Feature list** — the table from `docs/features.md` (per module is enough).
8. **Screenshots** — the 10-12 most important ones from the list above, each with one line saying
   what it proves.
9. **Security** — a summary of `docs/security.md`: CSRF, XSS, SQL injection, mass assignment,
   auth, session, authorization, naming the test that proves each one.
10. **Deployment** — the steps from `docs/deployment.md` plus screenshots of the live URL and the
    Actions page.
11. **Closing** — obstacles faced and future work (password reset, CSV export).

## 5. Before submitting

```bash
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
```

- [ ] Every command above is green.
- [ ] `database/schema/catatku.sql` is up to date if the schema changed
      (`mysqldump ... catatku migrations users wallets categories transactions`).
- [ ] Repository and live URL are filled in the README, the PDF and the submission form.
- [ ] The video essay is recorded following `docs/essay-notes.md`.
- [ ] No `.env`, `vendor/`, `node_modules/` or `public/build` is committed.
