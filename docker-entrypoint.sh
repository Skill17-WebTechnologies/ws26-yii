#!/usr/bin/env bash
set -e
cd /app

# SQLite only — no external database server. Apply migrations to the local file, then serve.
mkdir -p runtime
php yii migrate --interactive=0 || true
exec php yii serve 0.0.0.0:80
