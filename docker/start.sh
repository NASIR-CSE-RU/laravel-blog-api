#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  composer install \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-scripts
fi

if ! grep -Eq '^APP_KEY=.+$' .env; then
  php artisan key:generate --force
fi

attempt=0
max_attempts=30

while true; do
  if php artisan migrate --force; then
    break
  fi

  attempt=$((attempt + 1))

  if php artisan db:show >/dev/null 2>&1; then
    echo "Migration failed after database connection was established."
    exit 1
  fi

  if [ "$attempt" -ge "$max_attempts" ]; then
    echo "Database is still unavailable after ${max_attempts} attempts."
    exit 1
  fi

  echo "Waiting for database connection..."
  sleep 2
done

php artisan app:passport-init

exec php artisan serve --host=0.0.0.0 --port=8000
