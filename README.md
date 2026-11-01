# Catatku

Personal finance tracker built with Laravel and MySQL: wallets, income/expense
categories, transactions, and a monthly dashboard. Final project (UAS) for a
web programming course.

## Stack

| Layer     | Choice                                                        |
| --------- | ------------------------------------------------------------- |
| Framework | Laravel 13.39, PHP 8.4                                          |
| Database  | MySQL 8.4 (development, test, production)                       |
| Frontend  | Blade, Tailwind CSS 4, Vite 8, Alpine.js, Chart.js              |
| Quality   | Laravel Pint 1.27 (`laravel` preset), Larastan 3.13 level 6, PHPUnit 12.5 |
| Runtime   | Podman (Docker-compatible CLI), images built from `docker/`      |

PHP, Composer, Artisan, Pint, PHPStan and MySQL run **inside containers**. Only
Node and npm run on the host, for the Vite build.

## Prerequisites

- A container runtime with a `docker` CLI. This machine runs Podman 5.7 with
  `podman-compose`; a shim at `~/.local/bin/docker` forwards `docker` to `podman`.
- Node.js 22+ and npm 10+.
- Git.

## Local setup

```bash
git clone <repository-url> catatku
cd catatku

cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

npm install
npm run build
```

Open <http://localhost:8001>.

The database is published on `127.0.0.1:3307` so a GUI client (TablePlus, DBeaver)
can connect without clashing with a locally installed MySQL on 3306.

### Everyday commands

```bash
docker compose up -d                      # start app + database
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
npm run dev                               # Vite dev server with hot reload
```

After changing `docker/Dockerfile`, recreate the container so it uses the new image:

```bash
docker compose up -d --build --force-recreate app
```

## Tests

```bash
docker compose exec app php artisan test
```

The suite runs on the MySQL database `catatku_testing` (created by
`docker/mysql/init.sql`), not on SQLite. `phpunit.xml` overrides only the database
name; host, user and password come from `.env`.

## Demo accounts

Created by `php artisan migrate:fresh --seed`. Every account uses the password
`password`.

| Role  | Email                |
| ----- | -------------------- |
| admin | `admin@catatku.test` |
| user  | `budi@catatku.test`  |
| user  | `siti@catatku.test`  |

The two demo users own 5 wallets, 16 categories and 60 transactions spread over
August, September and October 2026.

## Database schema export

`database/schema/catatku.sql` is a `mysqldump` of the tables plus the demo rows. It can be
imported into an empty database with any name:

```bash
mysql -u root -p -e "CREATE DATABASE catatku_impor"
mysql -u root -p catatku_impor < database/schema/catatku.sql
```

The file contains no `CREATE DATABASE` or `USE`, so it never touches the database you are
already working in.

## Project structure

```text
app/            Models, controllers, form requests, policies, providers
bootstrap/      Framework bootstrap and middleware registration (no Kernel.php)
config/         Configuration, read from .env
database/       Migrations, factories, seeders
docker/         Dockerfile (dev + production), nginx/php-fpm config, MySQL init
docs/           ERD, architecture, MVC structure, roles and deployment notes
public/         Web root, built Vite assets
resources/      Blade views, CSS, JavaScript
routes/         web.php, console.php
tests/          PHPUnit feature tests (auth, resources, security, admin)
```

## Documentation

| Document | Content |
| --- | --- |
| [`docs/erd.md`](docs/erd.md) | ERD (Mermaid), constraints and indexes |
| [`docs/architecture.md`](docs/architecture.md) | request flow and the full routing table |
| [`docs/mvc-structure.md`](docs/mvc-structure.md) | directory tree with one line per file |
| [`docs/roles.md`](docs/roles.md) | role × action permission matrix |
| [`docs/features.md`](docs/features.md) | every feature and where it lives |
| [`docs/security.md`](docs/security.md) | security checklist mapped to file and test |
| [`docs/deployment.md`](docs/deployment.md) | production image, server setup, Cloudflare Tunnel, GitHub Actions |
| [`docs/submission-checklist.md`](docs/submission-checklist.md) | rubric row → evidence, screenshots to take, PDF outline |
| [`docs/essay-notes.md`](docs/essay-notes.md) | speaking notes for the video essay, with file and line references |
