# Yii 2.0.54 — WSC2026

A real **Yii 2.0.54** application (WorldSkills 2026 Web Technologies, TP17) backed by **MySQL** —
the same engine the deployed app uses, so what works locally works there. On start it applies the
migrations, then serves.

## Run it

```bash
cp .env.example .env
docker compose up --build
```

Then open **http://localhost**. `docker compose` starts a MySQL server alongside the app, using
the credentials from your `.env`. Stop with `docker compose down`.

## Checking the connection

```bash
curl -fsS http://localhost/api/db-check
```

```json
{ "ok": true, "driver": "mysql", "host": "db", "port": 3306, "database": "app",
  "user": "app", "server_version": "8.4.11", "latency_ms": 1,
  "demo_table": "yii_visitor present" }
```

It returns **503** when the connection fails, naming the host, database and user it tried and the
SQLSTATE — `1045` for a wrong password, `2002` for an unreachable host. The password is never in
the response. Every WSC2026 template answers the same check, so one command works whatever stack
you chose.

The endpoint is `actionDbCheck()` in `controllers/SiteController.php`, routed by the `urlManager`
rule in `config/web.php`.

## Configuration

The database connection lives in two files and **nowhere else** — not in `config/db.php`, the
`Dockerfile` or `docker-compose.yml`:

| File | Used for | In git? |
|------|----------|---------|
| `.env` | your local development database | No — gitignored |
| `.env.prod` | the deployed app; the platform fills in your credentials | Yes |

Yii does not read `.env` on its own and this template has no dependency for it, so `config/env.php`
is a small reader that `config/db.php` uses. Real environment variables win over the file, which is
how `docker compose` hands your local values to the container without touching the deployment's.

`docker-entrypoint.sh` copies `.env.prod` over `.env` when the container starts, so the deployed app
always runs the deployed configuration.

There is deliberately **no fallback connection**. If configuration is missing the app says so
loudly instead of quietly using some other database.

## Pretty URLs

`config/web.php` enables `enablePrettyUrl` with `showScriptName => false`, so routes are
`/site/about` rather than `/index.php?r=site/about`. That is what lets the connection check answer
on `/api/db-check`, the same URL as every other template.

## The database is shared

Every project you create points at the **same** MySQL database, so it already contains other
projects' tables. This template sets `tablePrefix` (from `DB_TABLE_PREFIX`, default `yii_`), so
`{{%visitor}}` becomes `yii_visitor` and even Yii's own migration history becomes `yii_migration`.
Keep a prefix of your own per project; do not drop it.

## Develop

The simplest loop is Docker: edit the source, then rebuild:

```bash
cp .env.example .env
docker compose up --build
```

Edit **controllers/ and views/** to change routes, controllers and views.

To run it natively instead you need **PHP 8.3** (with `pdo_mysql`), **Composer 2.9.5** and a MySQL
server of your own. Then:

```bash
cp .env.example .env    # point DB_HOST at your server (127.0.0.1 for a local one)
composer install
php yii migrate --interactive=0
php yii serve
```

## Stack

- PHP 8.3 / Composer 2.9.5 (`pdo_mysql`; `pdo_sqlite` also available)
- Yii 2.0.54
- MySQL 8.4 (started by `docker compose`)
