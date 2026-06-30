#!/usr/bin/env bash
set -e
cd /app
echo "Waiting for database + running migrations..."
for i in $(seq 1 40); do
  if php yii migrate --interactive=0 >/tmp/migrate.log 2>&1; then echo "migrations applied"; break; fi
  echo "  db not ready yet ($i)"; sleep 3
done
exec php yii serve 0.0.0.0:8080
