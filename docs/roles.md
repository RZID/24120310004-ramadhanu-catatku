# Permissions per role

Two roles: `user` (created by registration) and `admin` (seeder only). Both manage their own
financial data; an admin additionally manages accounts.

| Action | Guest | `user` | `admin` |
| --- | :---: | :---: | :---: |
| See the login / register form | yes | redirected to the dashboard | redirected to the dashboard |
| Create an account | yes | — | — |
| Log in | yes | — | — |
| Log out | — | yes | yes |
| Personal dashboard | — | yes | yes |
| View/update/delete own wallets | — | yes | yes |
| View/update/delete own categories | — | yes | yes |
| View/update/delete own transactions | — | yes | yes |
| View another user's transactions | — | no (403) | **no (403)** |
| See the list of all accounts | — | no (403) | yes |
| Deactivate / activate an account | — | no (403) | yes, except itself |
| Delete an account | — | no (403) | yes, except itself and the last active admin |

## How each rule is enforced

| Rule | Where | Evidence |
| --- | --- | --- |
| Pages require a login | `auth` middleware, `routes/web.php:25` | the "guests are redirected" test in every suite |
| Admin only under `/admin` | `EnsureUserIsAdmin` plus the `admin` alias in `bootstrap/app.php` | `tests/Feature/Admin/UserManagementTest.php` |
| Only the owner may update or delete | `WalletPolicy`, `CategoryPolicy`, `TransactionPolicy` | the IDOR test per resource |
| Lists only contain your own rows | queries built from `$user->wallets()` / `->categories()` / `->transactions()` | the "index lists only..." test in each suite |
| An admin cannot read another user's finances | no admin route exposes them; the policies have no exception | `test_an_admin_still_cannot_open_another_users_transaction` |
| A deactivated account cannot log in | `LoginRequest::authenticate` (status is part of the credentials) | `Auth/LoginTest::test_a_deactivated_user_cannot_log_in` |
| A live session of a deactivated account is cut | `EnsureUserIsActive` | `Auth/DeactivatedUserTest` |
| An admin cannot delete itself or the last active admin | `Admin\UserController` | three related tests in `UserManagementTest` |

## Notes

- Registration **always** produces a `user`; `role` is not mass assignable, so it cannot be sent
  through the form (`tests/Feature/Security/MassAssignmentTest.php`).
- An admin sees the **number** of transactions per account, never their contents. That is the
  privacy decision recorded as D42 in `docs/decisions.md`.
