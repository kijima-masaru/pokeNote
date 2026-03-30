#!/bin/bash
set -e
cd /var/www/html

if [ -d storage ]; then
  chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi

if [ -n "${DB_HOST:-}" ]; then
  echo "Waiting for MySQL (${DB_HOST}:${DB_PORT:-3306})..."
  for i in {1..60}; do
    if php -r "
      try {
        \$pdo = new PDO(
          'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306'),
          getenv('DB_USERNAME') ?: 'root',
          getenv('DB_PASSWORD') ?: ''
        );
        exit(0);
      } catch (Throwable \$e) {
        exit(1);
      }
    " 2>/dev/null; then
      echo "MySQL is reachable."
      break
    fi
    sleep 2
    if [ "$i" -eq 60 ]; then
      echo "MySQL did not become ready in time." >&2
      exit 1
    fi
  done
fi

if [ -f composer.json ]; then
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -f artisan ]; then
  php artisan config:clear 2>/dev/null || true
  if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
  fi
  if [ -f .env ]; then
    if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
      php artisan key:generate --force 2>/dev/null || true
    fi
  fi
  php artisan migrate --force 2>/dev/null || true
fi

exec "$@"
