#!/bin/sh
set -e

echo "============================================"
echo "  SAMOSIR - Starting Application Setup"
echo "============================================"

cd /var/www/html

# Create .env file from environment variables if it doesn't exist
if [ ! -f .env ]; then
    echo "[0/6] Creating .env file from environment variables..."
    cat > .env <<EOF
APP_NAME=${APP_NAME:-SAMOSIR}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost:8000}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-samosir_db}
DB_USERNAME=${DB_USERNAME:-samosir}
DB_PASSWORD=${DB_PASSWORD:-samosir_secret}

SESSION_DRIVER=${SESSION_DRIVER:-database}
SESSION_LIFETIME=120
SESSION_DOMAIN=${SESSION_DOMAIN:-null}

BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-reverb}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
CACHE_STORE=${CACHE_STORE:-database}

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost:8000}

REVERB_APP_ID=${REVERB_APP_ID:-843532}
REVERB_APP_KEY=${REVERB_APP_KEY:-samosirkey123}
REVERB_APP_SECRET=${REVERB_APP_SECRET:-samosirsecret456}
REVERB_HOST=${REVERB_HOST:-0.0.0.0}
REVERB_PORT=${REVERB_PORT:-8080}
REVERB_SCHEME=${REVERB_SCHEME:-http}

VITE_APP_NAME=${VITE_APP_NAME:-SAMOSIR}
VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY:-samosirkey123}
VITE_REVERB_HOST=${VITE_REVERB_HOST:-localhost}
VITE_REVERB_PORT=${VITE_REVERB_PORT:-8080}
VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME:-http}

TELESCOPE_ENABLED=${TELESCOPE_ENABLED:-false}
EOF
    chown www-data:www-data .env
    echo "  ✓ .env file created!"
else
    echo "[0/6] .env file already exists."
fi

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
