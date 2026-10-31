# Deployment

Target: one Railway service built from the `production` stage of `docker/Dockerfile`,
plus Railway's managed MySQL. Everything below was executed locally first; the steps
that need account access are marked as such.

## 1. Production image, verified locally

```bash
docker build -f docker/Dockerfile --target production -t catatku-prod .
```

The image is `php:8.4-apache` with `mod_rewrite`, the Vite assets copied from the Node
stage, `composer install --no-dev --optimize-autoloader`, and it runs as `www-data` on
`$PORT` (default 8080).

Run it against the local MySQL to prove it boots without the dev stack:

```bash
podman run -d --name catatku-prod-test --network catatku_default -p 8002:8080 \
  -e APP_ENV=production -e APP_DEBUG=false -e APP_KEY="$(php artisan key:generate --show)" \
  -e APP_URL=https://catatku.example.test -e APP_LOCALE=id -e LOG_CHANNEL=stderr \
  -e DB_CONNECTION=mysql -e DB_HOST=db -e DB_PORT=3306 \
  -e DB_DATABASE=catatku -e DB_USERNAME=catatku -e DB_PASSWORD=catatku \
  -e SESSION_DRIVER=database -e CACHE_STORE=database -e QUEUE_CONNECTION=sync \
  -e SESSION_SECURE_COOKIE=false catatku-prod
```

Result (real output):

```text
podman exec catatku-prod-test id      -> uid=33(www-data) gid=33(www-data)
GET /up                               -> 200
GET /                                -> 302, Location: https://localhost:8002/login
GET /login                            -> 200, contains name="_token"
GET /halaman-tidak-ada                -> 404, no stack trace, no vendor path
POST /login (budi@catatku.test)       -> 302, Location: https://localhost:8002/dashboard
GET /dashboard with the session       -> 200, shows "Budi Santoso"
mysql> SELECT COUNT(*) FROM sessions  -> 2
Set-Cookie: catatku-session=...; httponly; samesite=lax
```

With `SESSION_SECURE_COOKIE=true` (the production setting) the same cookie becomes
`secure; httponly; samesite=lax`.

Two pieces of code make the production output above possible:

- `bootstrap/app.php` -> `$middleware->trustProxies(at: '*')`, because Railway
  terminates TLS on its load balancer and only the forwarded headers reveal the real
  scheme and client IP.
- `AppServiceProvider::boot()` -> `URL::forceScheme('https')` when the app is in
  production, so generated URLs and redirects never fall back to `http`.

## 2. Environment variables on Railway

| Variable | Value |
| --- | --- |
| `APP_NAME` | `Catatku` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `php artisan key:generate --show` once, then paste |
| `APP_URL` | the generated `https://...up.railway.app` domain |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `id` / `en` |
| `APP_FAKER_LOCALE` | `id_ID` |
| `LOG_CHANNEL` | `stderr` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` / `DB_PORT` | `${{ MySQL.MYSQLHOST }}` / `${{ MySQL.MYSQLPORT }}` |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `${{ MySQL.MYSQLDATABASE }}` / `${{ MySQL.MYSQLUSER }}` / `${{ MySQL.MYSQLPASSWORD }}` |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` |

`DB_ROOT_PASSWORD`, `DB_URL` and `MYSQL_ATTR_SSL_CA` are not needed: Railway's MySQL
gives one database and one user.

## 3. Service settings

| Setting | Value | Why |
| --- | --- | --- |
| Build | Dockerfile | The repo ships a multi-stage `docker/Dockerfile` |
| Dockerfile path | `docker/Dockerfile` | The file is not at the repository root |
| Healthcheck path | `/up` | Laravel's built-in health route, registered in `bootstrap/app.php` |
| Restart policy | `ON_FAILURE` | A crashed container should come back, a deployed one should stay up |
| Pre-deploy command | `php artisan migrate --force` | Runs before the new container receives traffic |

No `railway.json` is committed: Railway marked config-as-code as deprecated in favour of
Infrastructure as Code, so these settings live in the dashboard instead.

The Vite assets are built inside the Docker image (`assets` stage), so no build command
is needed on Railway and `npm` never runs on the host at deploy time.

## 4. First deploy checklist

1. Push `main` to GitHub (the repository has no remote configured yet).
2. Railway: **New Project -> Deploy from GitHub repo** -> pick `Catatku`.
3. **New -> Database -> Add MySQL** in the same project.
4. Fill the variables from the table above. `APP_URL` is the Railway domain, so deploy
   once, read the domain, then set it and redeploy.
5. Apply the settings from section 3, then **Deploy**.
6. Seed the demo data once, from the Railway shell:
   `php artisan db:seed --force`
7. Verify on the live URL:
   - `GET /` -> `302` to `/login` over `https`
   - `GET /login` -> `200`, form contains `name="_token"`
   - `POST /login` without a token -> `419`
   - login `budi@catatku.test` / `password` -> dashboard with the user name
   - `POST /logout` -> back to `/login`, and `/dashboard` redirects again
   - response cookie shows `secure; httponly; samesite=lax`

## 5. Problems found and how they were fixed

| Problem | Fix |
| --- | --- |
| Redirects pointed at `http://` behind the TLS proxy | `trustProxies(at: '*')` + `URL::forceScheme('https')` in production |
| `SESSION_SECURE_COOKIE` left at its default would send the session over http | Set to `true` on Railway; verified the `secure` flag on a local production container |
| Railway builds the Dockerfile at the repository root by default | Dockerfile path set to `docker/Dockerfile` in the service settings |
| A bare `docker build .` fails because the Dockerfile is not at the root | Always build with `-f docker/Dockerfile` |
| `config:cache` is not run at build time, so `.env` values are never baked into the image | Configuration is read from the injected environment variables at runtime |
| `podman-compose` keeps the old container after a rebuild | `docker compose up -d --build --force-recreate app` after Dockerfile changes |

## 6. Subsequent deploys

```bash
git push origin main                       # Railway redeploys on push
docker compose exec app php artisan config:clear   # only if config files changed locally
```

Production database changes happen only through the pre-deploy command
(`php artisan migrate --force`). Never run `migrate:fresh`, `db:wipe` or `migrate:rollback`
against the Railway database.
