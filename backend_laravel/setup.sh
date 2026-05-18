#!/bin/bash
# =============================================================================
# Mindra Pro — Setup & Deploy (Plesk + MySQL + MercadoPago)
# =============================================================================
# Sube mindra-deploy.zip al servidor y ejecuta:
#   bash setup.sh
#
# Si ya descomprimiste, ejecuta directo desde la carpeta del proyecto:
#   bash setup.sh --skip-unzip
# =============================================================================

set -e

# ── Colores ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

step() { echo -e "\n${CYAN}[${1}]${NC} ${BOLD}${2}${NC}"; }
ok()   { echo -e "  ${GREEN}✓${NC} ${1}"; }
warn() { echo -e "  ${YELLOW}!${NC} ${1}"; }
fail() { echo -e "  ${RED}✗${NC} ${1}"; exit 1; }

echo -e "${BOLD}"
echo "╔══════════════════════════════════════════╗"
echo "║       Mindra Pro — Setup & Deploy        ║"
echo "╚══════════════════════════════════════════╝"
echo -e "${NC}"

# ── Detectar ruta del proyecto ───────────────────────────────────────────────
PROJECT_DIR="/var/www/vhosts/cafined.org/mindra.cafined.org"

if [ ! -d "$PROJECT_DIR" ]; then
    PROJECT_DIR="$(pwd)"
    warn "No se encontró $PROJECT_DIR, usando directorio actual: $PROJECT_DIR"
fi

cd "$PROJECT_DIR"
echo -e "Directorio: ${BOLD}${PROJECT_DIR}${NC}"

# ── 1. Descomprimir zip ─────────────────────────────────────────────────────
step "1/9" "Descomprimiendo paquete..."

if [ "$1" = "--skip-unzip" ]; then
    ok "Omitido (--skip-unzip)"
else
    ZIP_FILE=""
    for f in "$HOME/mindra-deploy.zip" "$PROJECT_DIR/mindra-deploy.zip" "$HOME/Desktop/mindra-deploy.zip"; do
        [ -f "$f" ] && ZIP_FILE="$f" && break
    done

    if [ -z "$ZIP_FILE" ]; then
        fail "No se encontró mindra-deploy.zip. Súbelo al servidor primero."
    fi

    unzip -o "$ZIP_FILE" -d "$PROJECT_DIR" > /dev/null
    ok "Descomprimido desde $ZIP_FILE"
fi

# ── 2. Directorios de storage ───────────────────────────────────────────────
step "2/9" "Creando directorios de storage..."

mkdir -p storage/framework/{sessions,views,cache/data} storage/app/{public,private} storage/logs bootstrap/cache
touch storage/logs/laravel.log
ok "Directorios creados"

# ── 3. Configurar .env ──────────────────────────────────────────────────────
step "3/9" "Configurando .env..."

if [ ! -f .env ]; then
    cp .env.production .env
    ok "Creado .env desde .env.production"

    # Generar APP_KEY
    # (necesita vendor/ para key:generate, se hace después de composer install)
    NEED_KEY=true
else
    ok ".env ya existe"
    NEED_KEY=false

    # Migrar de sqlite a mysql si es necesario
    if grep -q "DB_CONNECTION=sqlite" .env; then
        sed -i.bak 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
        rm -f .env.bak
        warn "Cambiado DB_CONNECTION de sqlite a mysql"
    fi

    # Agregar variables de MySQL si faltan
    grep -q "^DB_HOST=" .env || echo "DB_HOST=localhost" >> .env
    grep -q "^DB_PORT=" .env || echo "DB_PORT=3306" >> .env
    grep -q "^DB_DATABASE=mindraprofull" .env || sed -i.bak 's|^DB_DATABASE=.*|DB_DATABASE=mindraprofull|' .env && rm -f .env.bak

    # Agregar MercadoPago si falta
    grep -q "^MERCADOPAGO_ACCESS_TOKEN" .env || echo "" >> .env && grep -q "^MERCADOPAGO_ACCESS_TOKEN" .env || echo "MERCADOPAGO_ACCESS_TOKEN=CHANGE_ME" >> .env
    grep -q "^MERCADOPAGO_PUBLIC_KEY" .env || echo "MERCADOPAGO_PUBLIC_KEY=CHANGE_ME" >> .env
fi

# ── 4. Verificar credenciales ────────────────────────────────────────────────
step "4/9" "Verificando credenciales..."

MISSING=0

DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2-)
if [ "$DB_PASS" = "CHANGE_ME" ] || [ -z "$DB_PASS" ]; then
    echo ""
    echo -e "  ${YELLOW}Necesito la contraseña de MySQL para la BD 'mindraprofull'.${NC}"
    echo -e "  ${YELLOW}Encuéntrala en Plesk > Databases > mindraprofull > Connection info${NC}"
    echo ""
    read -p "  DB_PASSWORD: " DB_PASS_INPUT
    if [ -n "$DB_PASS_INPUT" ]; then
        sed -i.bak "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS_INPUT}|" .env && rm -f .env.bak
        ok "DB_PASSWORD configurado"
    else
        warn "DB_PASSWORD vacío — tendrás que editarlo manualmente"
        MISSING=1
    fi
else
    ok "DB_PASSWORD ya configurado"
fi

MP_TOKEN=$(grep "^MERCADOPAGO_ACCESS_TOKEN=" .env | cut -d'=' -f2-)
if [ "$MP_TOKEN" = "CHANGE_ME" ] || [ -z "$MP_TOKEN" ]; then
    warn "MERCADOPAGO_ACCESS_TOKEN no configurado (edita .env después si quieres MercadoPago)"
else
    ok "MERCADOPAGO_ACCESS_TOKEN configurado"
fi

# ── 5. Instalar dependencias ────────────────────────────────────────────────
step "5/9" "Instalando dependencias (composer)..."

composer install --no-dev --no-scripts --no-interaction --optimize-autoloader 2>&1 | tail -3
ok "Dependencias instaladas"

# ── 6. Post-install + APP_KEY ────────────────────────────────────────────────
step "6/9" "Ejecutando post-install..."

php artisan package:discover --ansi 2>&1 | tail -2

if [ "$NEED_KEY" = true ]; then
    php artisan key:generate --force
    ok "APP_KEY generado"
fi

# ── 7. Limpiar caches + migraciones ─────────────────────────────────────────
step "7/9" "Limpiando caches y ejecutando migraciones..."

rm -f bootstrap/cache/*.php
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
ok "Caches limpiados"

echo -e "  Ejecutando migraciones..."
php artisan migrate --force
ok "Migraciones ejecutadas"

# ── 8. Permisos ──────────────────────────────────────────────────────────────
step "8/9" "Configurando permisos..."

chmod -R 775 storage bootstrap/cache
find storage -type f -exec chmod 664 {} \;
find storage -type d -exec chmod 775 {} \;
ok "Permisos configurados"

# ── 9. Cache de producción ───────────────────────────────────────────────────
step "9/9" "Generando cache de producción..."

php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Cache generado"

# ── Resultado ────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}${BOLD}║         Deploy completado ✓              ║${NC}"
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════╝${NC}"
echo ""
echo -e "  URL:     ${BOLD}https://mindra.cafined.org${NC}"
echo -e "  BD:      ${BOLD}mindraprofull (MySQL)${NC}"
echo -e "  Webhook: ${BOLD}https://mindra.cafined.org/api/webhooks/mercadopago${NC}"
echo ""

if [ $MISSING -eq 1 ]; then
    echo -e "${YELLOW}  ⚠ Revisa .env — hay valores pendientes.${NC}"
    echo -e "${YELLOW}  Después de editar, ejecuta: php artisan config:cache${NC}"
    echo ""
fi

echo -e "  ${CYAN}Recuerda configurar en MercadoPago > Webhooks:${NC}"
echo -e "  ${CYAN}URL: https://mindra.cafined.org/api/webhooks/mercadopago${NC}"
echo -e "  ${CYAN}Evento: payment${NC}"
echo ""
