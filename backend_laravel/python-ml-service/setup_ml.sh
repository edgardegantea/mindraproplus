#!/bin/bash
# setup_ml.sh — Instala y configura el microservicio ML en el VPS
# Ejecutar desde: backend_laravel/python-ml-service/
# Uso: bash setup_ml.sh

set -e

ML_DIR="$(cd "$(dirname "$0")" && pwd)"
CHECKPOINT_NAME="anxiety-multimodal-epoch=00-val_auc=0.592.ckpt"

echo "=== Mindra ML Service Setup ==="
echo "Directorio: $ML_DIR"
echo ""

# 1. Entorno virtual
echo "[1/5] Creando entorno virtual Python..."
python3 -m venv "$ML_DIR/venv"
source "$ML_DIR/venv/bin/activate"

# 2. Dependencias
echo "[2/5] Instalando dependencias (puede tardar varios minutos)..."
pip install --upgrade pip --quiet
pip install -r "$ML_DIR/requirements.txt" --quiet
echo "      Dependencias instaladas."

# 3. Verificar checkpoint
echo "[3/5] Verificando checkpoint del modelo..."
mkdir -p "$ML_DIR/modelo"
if [ ! -f "$ML_DIR/modelo/$CHECKPOINT_NAME" ]; then
    echo ""
    echo "  !! ATENCIÓN: Checkpoint no encontrado en modelo/$CHECKPOINT_NAME"
    echo "  !! Transfiere el archivo con:"
    echo "  !!"
    echo "  !!   scp /Users/edegantea/development/doctorado/ansiedad-web/modelo/$CHECKPOINT_NAME \\"
    echo "  !!       cafined@mindra.cafined.org:$ML_DIR/modelo/"
    echo "  !!"
    echo "  !! El servicio arrancará en modo fallback (predicciones aleatorias)"
    echo "  !! hasta que el checkpoint esté disponible."
    echo ""
else
    echo "      Checkpoint OK ($(du -sh "$ML_DIR/modelo/$CHECKPOINT_NAME" | cut -f1))"
fi

# 4. Supervisor
echo "[4/5] Configurando supervisor..."
sudo cp "$ML_DIR/supervisor.conf" /etc/supervisor/conf.d/mindra-ml.conf
sudo supervisorctl reread
sudo supervisorctl update
echo "      Supervisor configurado."

# 5. Arrancar
echo "[5/5] Arrancando servicio..."
sudo supervisorctl start mindra-ml
sleep 3
sudo supervisorctl status mindra-ml

echo ""
echo "Verificando health:"
curl -s http://localhost:8001/health | python3 -m json.tool 2>/dev/null || echo "(servicio aún arrancando, verifica en 30s)"

echo ""
echo "=== Setup completo ==="
echo "Logs: sudo tail -f /var/log/supervisor/mindra-ml.out.log"
echo "      sudo tail -f /var/log/supervisor/mindra-ml.err.log"
