# Catatku

Personal finance tracker built with Laravel and MySQL: wallets, income/expense
categories, transactions, and a monthly dashboard. Final project (UAS) for a
web programming course.

Every decision that was not obvious from the brief is recorded in
[`docs/decisions.md`](docs/decisions.md).

## Stack

| Layer     | Choice                                                        |
| --------- | ------------------------------------------------------------- |
| Framework | Laravel 13.39, PHP 8.4                                          |
| Database  | MySQL 8.4 (development, test, production)                       |
| Frontend  | Blade, Tailwind CSS 4, Vite 8, Alpine.js, Chart.js              |
| Quality   | Laravel Pint (`laravel` preset), Larastan level 6, PHPUnit 12    |
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

Created by the seeders (Phase 1). Credentials will be listed here once the seeders
exist.

## Project structure

```text
app/            Models, controllers, form requests, policies, providers
bootstrap/      Framework bootstrap and middleware registration (no Kernel.php)
config/         Configuration, read from .env
database/       Migrations, factories, seeders
docker/         Dockerfile (dev + production targets), Apache vhost, MySQL init
docs/           ERD, architecture, decisions, deployment notes
public/         Web root, built Vite assets
resources/      Blade views, CSS, JavaScript
routes/         web.php, console.php
tests/          PHPUnit feature and unit tests
```

## Documentation

- [`docs/decisions.md`](docs/decisions.md) — decision log with rationale and cost
