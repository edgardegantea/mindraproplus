#!/bin/bash
# Deploy script for Plesk server (MySQL)
# Run this on the server: bash deploy.sh
#
# Prerequisites:
#   - PHP 8.3 with pdo_mysql extension
#   - Composer installed globally
#   - MySQL database "mindraprofull" already created in Plesk
#   - Document root in Plesk pointing to public/

set -e

echo "=== Mindra Deploy ==="
echo ""

cd "$(dirname "$0")"

# 1. Ensure storage directory structure exists (Plesk doesn't preserve empty dirs)
echo "[1/8] Creating storage directories..."
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p storage/app/{public,private}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# 2. Install dependencies (production only, no scripts to avoid stale provider errors)
echo "[2/8] Installing dependencies..."
composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

# Clear stale bootstrap cache BEFORE package:discover — dev packages removed by
# --no-dev (e.g. Laravel\Pail) are still referenced in the old packages.php cache,
# which causes package:discover to fail when it tries to load the Application.
rm -f bootstrap/cache/*.php

# 3. Run post-autoload scripts manually (package:discover needs vendor/ present)
echo "[3/8] Running post-install scripts..."
php artisan package:discover --ansi

# 4. Setup .env if not exists
if [ ! -f .env ]; then
    echo "[4/8] Creating .env from .env.production..."
    cp .env.production .env
    php artisan key:generate --force
    echo ""
    echo "  !! IMPORTANT: Edit .env and set these values:               !!"
    echo "  !!   DB_USERNAME=your_mysql_user                            !!"
    echo "  !!   DB_PASSWORD=your_mysql_password                        !!"
    echo "  !!   MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxx                  !!"
    echo "  !!   MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxx                    !!"
    echo "  !! Then re-run: bash deploy.sh                              !!"
    echo ""
else
    echo "[4/8] .env already exists, merging new variables..."
    # Add new env vars if missing
    grep -q "MERCADOPAGO_ACCESS_TOKEN"    .env || echo -e "\nMERCADOPAGO_ACCESS_TOKEN=CHANGE_ME"    >> .env
    grep -q "MERCADOPAGO_PUBLIC_KEY"      .env || echo -e "MERCADOPAGO_PUBLIC_KEY=CHANGE_ME"        >> .env
    grep -q "MERCADOPAGO_WEBHOOK_SECRET"  .env || echo -e "MERCADOPAGO_WEBHOOK_SECRET=CHANGE_ME"    >> .env
    grep -q "MINDRABACK_URL"              .env || echo -e "\nMINDRABACK_URL=http://localhost:8001"   >> .env
    grep -q "MINDRABACK_TIMEOUT"          .env || echo -e "MINDRABACK_TIMEOUT=60"                   >> .env
    grep -q "MINDRABACK_CONNECT_TIMEOUT"  .env || echo -e "MINDRABACK_CONNECT_TIMEOUT=8"            >> .env
    # Asegurar que app.mindra.cafined.org esté en los dominios stateful de Sanctum
    grep -q "SANCTUM_STATEFUL_DOMAINS" .env || echo -e "\nSANCTUM_STATEFUL_DOMAINS=mindra.cafined.org,app.mindra.cafined.org" >> .env
    grep -q "app\.mindra\.cafined\.org" .env || sed -i.bak 's/SANCTUM_STATEFUL_DOMAINS=mindra\.cafined\.org$/SANCTUM_STATEFUL_DOMAINS=mindra.cafined.org,app.mindra.cafined.org/' .env && rm -f .env.bak
    grep -q "DB_CONNECTION" .env && sed -i.bak 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env && rm -f .env.bak
fi

# 5. Clear all caches (remove compiled files that may reference removed packages)
echo "[5/8] Clearing caches..."
rm -f bootstrap/cache/*.php
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 6. Test database connection and run migrations
echo "[6/8] Running migrations..."
php artisan migrate --force

# 7. Set permissions (web server needs write access)
echo "[7/8] Setting permissions..."
chmod -R 775 storage bootstrap/cache
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;

# 8. Cache for production
echo "[8/8] Building production cache..."
# Leer APP_KEY del .env y pasarla explícitamente para evitar que variables de
# sistema vacías (e.g. APP_KEY= en el entorno de Plesk) sobreescriban el .env.
_APP_KEY=$(grep "^APP_KEY=" .env | cut -d'=' -f2-)
APP_KEY="$_APP_KEY" php artisan config:cache
APP_KEY="$_APP_KEY" php artisan route:cache
APP_KEY="$_APP_KEY" php artisan view:cache

echo ""
echo "=== Deploy complete ==="
echo "Visit: https://mindra.cafined.org"
echo ""
echo "Health check: curl https://mindra.cafined.org/api/health"
