# Yii 2.0.54 — WSC2026 app (MySQL)

```bash
cp .env.example .env
docker compose up --build
```

Open **http://localhost** (Yii2 basic app).
Connection check: `GET /api/db-check` — 200 when the database is reachable, 503 with the reason
when it is not.

On start the entrypoint copies `.env.prod` over `.env`, then applies the migrations — creating a
`yii_visitor` table. Every project shares one MySQL database, so the `tablePrefix` in
`config/db.php` keeps this app's tables apart from the competitor's other applications; Yii's own
migration history lands in `yii_migration` for the same reason.

The connection is configured **only** in `.env` (local) and `.env.prod` (deployed) — never in
`config/db.php`, the Dockerfile or this compose file. Compose reads `.env` to start MySQL *and* to
configure the app, so both sides always agree. `config/env.php` is the small reader that gives
plain Yii access to those files, preferring real environment variables over them.

Migrations never fail the boot: an unreachable database leaves the app serving its page instead of
crash-looping the container, and `/api/db-check` reports exactly why.

Pinned: PHP 8.3 / Composer 2.9.5, yiisoft/yii2 2.0.54, MySQL 8.4.
