# Yii 2.0.54 — WSC2026

A real **Yii 2.0.54** application (WorldSkills 2026 Web Technologies, TP17) backed by a
self-contained **SQLite** database — no database server required. On start it runs a migration.

## Run it

```bash
docker compose up --build
```

Then open **http://localhost**. There is no database service: the app uses a SQLite file
(`runtime/database.sqlite`) created inside the container at startup. Stop with `docker compose down`.

## Develop

The simplest loop is Docker: edit the source (see below), then rebuild:

```bash
docker compose up --build
```

Edit **controllers/ and views/** to change routes, controllers and views.

To run it natively instead you need **PHP 8.3** (with `pdo_sqlite`) and **Composer 2.9.5**. Then:

```bash
composer install
php yii serve
```

(the SQLite file is created automatically on first migrate).

## Stack

- PHP 8.3 / Composer 2.9.5
- Yii 2.0.54
- SQLite (bundled, no server)
