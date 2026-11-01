# MVC structure

The directory tree that actually exists in the repository, one line per folder or important
file.

```text
app/
├── Enums/
│   ├── TransactionType.php        income/expense plus label() and icon() for the UI
│   └── UserRole.php               admin/user plus label()
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php         base class, pulls in AuthorizesRequests
│   │   ├── DashboardController.php  monthly summary, wallet balances, 5 latest transactions
│   │   ├── TransactionController.php transaction CRUD plus list filters
│   │   ├── WalletController.php   wallet CRUD
│   │   ├── CategoryController.php category CRUD
│   │   ├── Admin/UserController.php  account list, deactivate/reactivate, delete
│   │   └── Auth/
│   │       ├── LoginController.php   form, authentication, session regeneration
│   │       ├── LogoutController.php  session invalidation plus CSRF token regeneration
│   │       └── RegisterController.php account creation plus automatic login
│   ├── Middleware/
│   │   ├── EnsureUserIsActive.php  force logout for a deactivated account
│   │   └── EnsureUserIsAdmin.php   403 for non-admins under /admin
│   └── Requests/
│       ├── Auth/LoginRequest.php        credentials plus one generic failure message
│       ├── Auth/RegisterRequest.php     lowercase email, unique rule, password rules
│       ├── CategoryRequest.php          name and type, unique per (user, name, type)
│       ├── DashboardRequest.php         month validation (Y-m)
│       ├── TransactionFilterRequest.php list filter validation
│       ├── TransactionRequest.php       FK ownership, amount, date, type equals category type
│       └── WalletRequest.php            name unique per owner
├── Models/
│   ├── User.php            role/deactivated_at casts, relationships, deactivate/activate
│   ├── Wallet.php          user and transactions relationships
│   ├── Category.php        type cast, relationships
│   └── Transaction.php     type/date casts, forMonth and ofType scopes
├── Policies/
│   ├── WalletPolicy.php      update/delete for the owner only
│   ├── CategoryPolicy.php    same
│   └── TransactionPolicy.php same, with no admin exception
├── Providers/AppServiceProvider.php  preventLazyLoading, forceScheme https in production
└── Support/
    ├── Money.php           the only "Rp 1.250.000" formatter
    └── MonthlySummary.php  monthly aggregation (sum plus groupBy)

bootstrap/app.php        routing, middleware aliases, trusted proxies, error handling
config/                  configuration; only MySQL and the sync queue driver remain
database/
├── factories/           test data for the four models
├── migrations/          users, sessions, cache, wallets, categories, transactions
├── schema/catatku.sql   structure plus demo rows (importable on its own)
└── seeders/             one admin, two demo users, 5 wallets, 16 categories, 60 transactions

lang/id/                 Indonesian validation and pagination messages
public/                  document root; the Vite build lands in public/build
resources/
├── css/app.css          design tokens: teal accent, income/expense, font
├── js/app.js            Alpine (user menu, confirmation dialog)
├── js/chart.js          Chart.js doughnut for expense per category
└── views/
    ├── components/      layout, table, td, tr, badge, input, select, button, and so on
    ├── auth/            login, register
    ├── transactions/    index, create, edit, partials/form
    ├── wallets/         index, create, edit, partials/form
    ├── categories/      index, create, edit, partials/form
    ├── admin/users/     index
    ├── errors/          403, 404, 419, 429, 500
    └── dashboard.blade.php

routes/web.php           every web route (29 routes)
tests/Feature/           feature tests per resource, auth, security, dashboard, admin
docker/                  Dockerfile (dev plus production), nginx and php-fpm config, MySQL init
docker-compose.yml       development stack (app plus MySQL)
docker-compose.production.yml  server stack (production app plus MySQL)
.github/workflows/deploy.yml   automated deploy over SSH
```

## Data flow of one page

`routes/web.php` → middleware → controller → FormRequest (validation) → policy (authorization)
→ model (Eloquent) → Blade view → browser. The details are in
[`architecture.md`](architecture.md).
