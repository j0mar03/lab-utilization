#!/bin/sh
set -e

APP_DIR="/var/www/html"

echo "🔄 Lab System entrypoint starting..."

cd "${APP_DIR}"

# ── 1. Bootstrap: run composer create-project if vendor doesn't exist ─────
# This installs the full Laravel base + all our dependencies in one shot.
if [ ! -f "${APP_DIR}/vendor/autoload.php" ]; then
    echo "📦 Installing dependencies (this takes ~2 min on first run)..."
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_NO_AUDIT=1 \
    composer install \
        --working-dir="${APP_DIR}" \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-cache \
        --no-scripts \
        --no-security-blocking
    echo "✅ Dependencies installed"
else
    echo "✅ vendor/ already exists — skipping install"
fi

# ── 2. Ensure required directories exist ──────────────────────────────────
mkdir -p "${APP_DIR}/storage/framework/sessions" \
         "${APP_DIR}/storage/framework/views" \
         "${APP_DIR}/storage/framework/cache/data" \
         "${APP_DIR}/storage/logs" \
         "${APP_DIR}/bootstrap/cache"

chmod -R 775 "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" 2>/dev/null || true

# ── 3. Copy .env if missing ───────────────────────────────────────────────
if [ ! -f "${APP_DIR}/.env" ]; then
    echo "📋 Copying .env.docker → .env"
    cp "${APP_DIR}/.env.docker" "${APP_DIR}/.env"
fi

# ── 4. Generate app key if not set ───────────────────────────────────────
KEY=$(grep "^APP_KEY=" "${APP_DIR}/.env" | cut -d '=' -f2 | tr -d '[:space:]')
if [ -z "$KEY" ]; then
    echo "🔑 Generating application key..."
    php "${APP_DIR}/artisan" key:generate --force
else
    echo "✅ App key already set"
fi

# ── 5. Discover packages ──────────────────────────────────────────────────
php "${APP_DIR}/artisan" package:discover --ansi 2>/dev/null || true

# ── 6. Wait for MySQL ─────────────────────────────────────────────────────
echo "⏳ Waiting for MySQL..."
until php "${APP_DIR}/artisan" migrate:status --no-interaction > /dev/null 2>&1; do
    printf "."
    sleep 2
done
echo ""
echo "✅ MySQL is ready"

# ── 7. Run migrations ─────────────────────────────────────────────────────
echo "🗄️  Running migrations..."
php "${APP_DIR}/artisan" migrate --force --graceful --no-interaction

# ── 8. Seed only if rooms table is empty ──────────────────────────────────
ROOM_COUNT=$(php "${APP_DIR}/artisan" tinker --execute="echo \App\Models\Room::count();" 2>/dev/null | grep -E '^[0-9]+$' | head -1 || echo "0")
if [ -z "$ROOM_COUNT" ] || [ "$ROOM_COUNT" = "0" ]; then
    echo "🌱 Seeding database..."
    php "${APP_DIR}/artisan" db:seed --force --no-interaction
else
    echo "✅ Already seeded (${ROOM_COUNT} rooms found)"
fi

# ── 9. Install Node modules + build Vite assets ───────────────────────────
export HOME=/tmp
if [ ! -d "${APP_DIR}/node_modules" ]; then
    echo "📦 Running npm install..."
    npm install --no-audit --no-fund --prefix "${APP_DIR}"
fi

echo "🏗️  Building frontend assets (Tailwind + Vite)..."
npm run build --prefix "${APP_DIR}"

# ── 10. Create storage symlink ────────────────────────────────────────────
php "${APP_DIR}/artisan" storage:link --force 2>/dev/null || true

echo ""
echo "╔══════════════════════════════════════╗"
echo "║  ✅  Lab Utilization System Ready!   ║"
echo "║  →  http://localhost:8088            ║"
echo "╚══════════════════════════════════════╝"
echo ""

exec php-fpm
