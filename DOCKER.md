# Yii 2.0.54 — WSC2026 app (SQLite)

```bash
docker compose up --build
```

Open **http://localhost** (Yii2 basic app). On start it runs a migration (creates a `visitor`
table) against a self-contained SQLite database file under `runtime/`. No database server is
used. Pinned: PHP 8.3 / Composer 2.9.5, yiisoft/yii2 2.0.54.
