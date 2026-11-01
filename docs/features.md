# Features

Every behaviour the app offers, and where it is implemented. No button or menu item leads to an
empty page.

## Authentication

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| Registration | name, email (stored lowercase), password with at least 8 characters mixing upper and lower case and digits; logs in immediately | `Auth/RegisterController`, `Requests/Auth/RegisterRequest`, `views/auth/register` |
| Login | email plus password; wrong email, wrong password and a deactivated account all give the same message | `Requests/Auth/LoginRequest::authenticate` |
| Logout | POST, the session is invalidated and the CSRF token regenerated | `Auth/LogoutController` |
| Attempt limit | 5 logins per minute, 6 registrations per minute, 429 page | `routes/web.php:19,22`, `views/errors/429` |
| Deactivated account | cannot log in; a live session is cut on the next request | `LoginRequest`, `Middleware/EnsureUserIsActive` |

## Wallets

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| List | own wallets only, with the transaction count per wallet | `WalletController@index` |
| Create / update | name required, max 100, unique per owner | `Requests/WalletRequest` |
| Delete | refused with a message while transactions still use it | `WalletController@destroy` |
| Empty state | message plus an add button | `views/wallets/index` |

## Categories

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| List | own categories only, type badge (icon plus text, never colour alone) | `CategoryController@index` |
| Create / update | name unique per (owner, name, type); type is `income` or `expense` | `Requests/CategoryRequest` |
| Delete | refused while transactions still use it | `CategoryController@destroy` |

## Transactions

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| List | 15 rows per page, newest date first | `TransactionController@index` |
| Filters | month (`Y-m`), type, category; filter values survive pagination | `TransactionFilterRequest`, `withQueryString()` |
| Create / update | wallet and category must belong to the user, whole rupiah amount, date not in the future, note max 255 | `Requests/TransactionRequest` |
| Type invariant | the transaction type must equal the category type | `TransactionRequest::after` |
| Delete | confirmation dialog, then delete | `components/confirm-delete` |
| Money format | `Rp 1.250.000`, with a +/- sign and an arrow icon | `Support/Money`, `components/amount` |

## Dashboard

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| Month picker | current month by default, switchable through `?month=YYYY-MM` | `Requests/DashboardRequest` |
| Three cards | income, expense and balance for the selected month | `Support/MonthlySummary` |
| Chart | doughnut of expense per category, plus a list of the numbers | `resources/js/chart.js`, `views/dashboard` |
| Balance per wallet | total income minus expense over all time | `DashboardController::walletBalances` |
| Latest transactions | the 5 newest rows | `DashboardController` |

## Admin

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| Account list | name, email, role, transaction count, status | `Admin/UserController@index` |
| Deactivate / activate | effective immediately; a deactivated account cannot log in | `Admin/UserController` plus `User::deactivate/activate` |
| Delete account | deletes the transactions first, then the account (wallets and categories cascade) | `Admin/UserController@destroy` |
| Guards | cannot deactivate or delete itself, cannot remove the last active admin | `Admin/UserController` |

## Cross-cutting

| Feature | Behaviour | Implementation |
| --- | --- | --- |
| Flash messages | success (green) and failure (red) in one component | `components/flash` |
| Error pages | 403, 404, 419, 429, 500 in Indonesian | `views/errors`, `components/error-page` |
| Navigation | desktop sidebar, mobile bottom bar, active item marked | `components/layouts/app` |
| Validation | Indonesian messages, one error per field, old input kept | `lang/id/validation.php`, `components/input` |
| Demo data | 1 admin, 2 users, 5 wallets, 16 categories, 60 transactions from August to October 2026 | `database/seeders` |
