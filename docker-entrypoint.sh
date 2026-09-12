#!/usr/bin/env bash
set -e
cd /app

# The platform writes this competitor's own credentials into .env.prod. Copy it
# over .env so the deployed app reads the deployed configuration rather than
# whatever .env happened to be built into the image.
#
# Local development keeps its own .env, which is gitignored and never shipped;
# docker-compose.yml passes those values as environment variables instead, and
# config/env.php prefers a real environment variable over the file.
if [ -f .env.prod ]; then
  cp .env.prod .env
fi

mkdir -p runtime

# Never fatal: a database that is unreachable for a moment should leave the app
# serving its error page — and /api/db-check reporting exactly why — rather than
# crash-looping the container.
php yii migrate --interactive=0 || \
  echo "migrations not applied — see /api/db-check for the reason" >&2

exec php yii serve 0.0.0.0:80
