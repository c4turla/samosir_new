#!/bin/sh
set -e

echo "============================================"
echo "  SAMOSIR - Starting Application Setup"
echo "============================================"

cd /var/www/html

# Wait for database to be ready
echo "[1/6] Waiting for database connection..."
maxTries=30
tries=0
while ! php artisan db:monitor --databases=mysql > /dev/null 2>&1; do
    tries=$((tries + 1))
    if [ $tries -gt $maxTries ]; then
        echo "ERROR: Database connection timeout after ${maxTries} attempts."
        echo "Trying direct MySQL ping..."
        break
    fi
    echo "  Attempt $tries/$maxTries - Waiting for MySQL..."
    sleep 2
done
echo "  ✓ Database is ready!"

# Generate key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "[2/6] Generating application key..."
    php artisan key:generate --force
else
    echo "[2/6] Application key already set."
fi

# Run migrations
echo "[3/6] Running database migrations..."
php artisan migrate --force

# Create storage symlink
echo "[4/6] Creating storage symlink..."
php artisan storage:link 2>/dev/null || true

# Cache configuration for production
echo "[5/6] Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set permissions
echo "[6/6] Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "============================================"
echo "  SAMOSIR is ready! Starting services..."
echo "============================================"

# Execute CMD
exec "$@"
