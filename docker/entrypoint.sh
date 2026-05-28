#!/bin/sh
set -e

echo "============================================"
echo "  SAMOSIR - Starting Application Setup"
echo "============================================"

cd /var/www/html

# ── Step 0: Create .env from environment variables ──────────
if [ ! -f .env ]; then
    echo "[0/6] Creating .env file from environment variables..."

    # Sanitize APP_URL - ensure it has a valid scheme
    SAFE_APP_URL="${APP_URL:-http://localhost:8000}"
    case "$SAFE_APP_URL" in
        http://*|https://*) ;;
        *) SAFE_APP_URL="http://${SAFE_APP_URL}" ;;
    esac

    # Auto-set VITE_REVERB_PORT based on scheme if not explicitly defined
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME:-http}
    if [ "$VITE_REVERB_SCHEME" = "https" ]; then
        VITE_REVERB_PORT_VAL=${VITE_REVERB_PORT:-443}
    else
        VITE_REVERB_PORT_VAL=${VITE_REVERB_PORT:-80}
    fi

    cat > .env <<ENVEOF
APP_NAME="${APP_NAME:-SAMOSIR}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${SAFE_APP_URL}

LOG_CHANNEL=stack
LOG_LEVEL=${LOG_LEVEL:-error}

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

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost}

REVERB_APP_ID=${REVERB_APP_ID:-843532}
REVERB_APP_KEY=${REVERB_APP_KEY:-samosirkey123}
REVERB_APP_SECRET=${REVERB_APP_SECRET:-samosirsecret456}
REVERB_HOST=${REVERB_HOST:-127.0.0.1}
REVERB_PORT=${REVERB_PORT:-8080}
REVERB_SCHEME=${REVERB_SCHEME:-http}

VITE_APP_NAME="${VITE_APP_NAME:-SAMOSIR}"
VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY:-samosirkey123}
VITE_REVERB_HOST=${VITE_REVERB_HOST:-localhost}
VITE_REVERB_PORT=${VITE_REVERB_PORT_VAL}
VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME:-http}

TELESCOPE_ENABLED=${TELESCOPE_ENABLED:-false}
ENVEOF
    chown www-data:www-data .env
    echo "  ✓ .env file created!"
else
    echo "[0/6] .env file already exists."
fi

# ── Step 1: Wait for database ──────────────────────────────
echo "[1/6] Waiting for database connection..."
DB_READY_HOST="${DB_HOST:-db}"
DB_READY_PORT="${DB_PORT:-3306}"
DB_READY_USER="${DB_USERNAME:-samosir}"
DB_READY_PASS="${DB_PASSWORD:-samosir_secret}"

maxTries=60
tries=0
while [ $tries -lt $maxTries ]; do
    if mysqladmin ping -h "$DB_READY_HOST" -P "$DB_READY_PORT" -u "$DB_READY_USER" -p"$DB_READY_PASS" --silent 2>/dev/null; then
        echo "  ✓ Database is ready!"
        break
    fi
    tries=$((tries + 1))
    echo "  Attempt $tries/$maxTries - Waiting for MySQL at ${DB_READY_HOST}:${DB_READY_PORT}..."
    sleep 3
done

if [ $tries -eq $maxTries ]; then
    echo "  ⚠ WARNING: Could not connect to MySQL after ${maxTries} attempts."
    echo "    DB_HOST=${DB_READY_HOST} DB_PORT=${DB_READY_PORT} DB_USER=${DB_READY_USER}"
    echo "    Continuing anyway (migrations may fail)..."
fi

# ── Step 2: Application key ────────────────────────────────
APP_KEY_VAL=$(grep "^APP_KEY=" .env | cut -d'=' -f2-)
if [ -z "$APP_KEY_VAL" ]; then
    echo "[2/6] Generating application key..."
    php artisan key:generate --force --no-interaction
else
    echo "[2/6] Application key already set."
fi

# ── Step 3: Run migrations ─────────────────────────────────
echo "[3/6] Running database migrations..."
php artisan migrate --force --no-interaction || {
    echo "  ⚠ Migration failed. Retrying in 5 seconds..."
    sleep 5
    php artisan migrate --force --no-interaction || echo "  ✗ Migration failed again. Check DB connection."
}

# ── Step 4: Storage symlink ────────────────────────────────
echo "[4/6] Creating storage symlink..."
php artisan storage:link 2>/dev/null || true

# ── Step 5: Cache configuration ────────────────────────────
echo "[5/6] Caching configuration..."
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan event:cache --no-interaction 2>/dev/null || true

# ── Step 6: Permissions ────────────────────────────────────
echo "[6/6] Setting file permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "============================================"
echo "  SAMOSIR is ready! Starting services..."
echo "  APP_URL: $(grep '^APP_URL=' .env | cut -d'=' -f2-)"
echo "  DB_HOST: ${DB_READY_HOST}:${DB_READY_PORT}"
echo "============================================"

# Execute CMD
exec "$@"
