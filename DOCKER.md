# Yii 2.0.54 — WSC2026 app + MySQL 8.4

```bash
docker compose up --build
```

Open **http://localhost:8080** (Yii2 basic app). On start it waits for MySQL 8.4 and runs a
migration (creates a `visitor` table). Pinned: PHP 8.3 / Composer 2.9.5, yiisoft/yii2 2.0.54, MySQL 8.4.
