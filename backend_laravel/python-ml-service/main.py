"""
Mindra - Microservicio Python ML
=================================
FastAPI que expone los endpoints de inferencia y transcripción.
Laravel lo llama internamente; el frontend NO llama a este servicio directamente.

Endpoints:
  POST /predict    - Inferencia de ansiedad (audio + texto)
  POST /transcribe - Transcripción de audio con Whisper
  GET  /health     - Health check del servicio ML
"""

import os
import sys
import logging
from pathlib import Path

from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware

# ── Rutas del proyecto ────────────────────────────────────────────────────────
# ML_INFERENCE_ROOT: directorio que contiene el paquete `inference/`
#   (el proyecto Django con los servicios de ML).
#
# Se puede configurar con la variable de entorno ML_INFERENCE_ROOT.
# Si no está definida, se auto-detecta según la estructura de carpetas:
#
#   Desarrollo (Mac):
#     ansiedad-web/mindra-pro/backend_laravel/python-ml-service/main.py
#     → sube 4 niveles → ansiedad-web/
#
#   Producción (Plesk VPS):
#     /var/www/vhosts/mindra.cafined.org/httpdocs/python-ml-service/main.py
#     → ML_INFERENCE_ROOT=/var/www/vhosts/mindraback.cafined.org/httpdocs
#
_env_root = os.environ.get("ML_INFERENCE_ROOT", "")
if _env_root:
    ROOT_DIR = Path(_env_root).resolve()
else:
    # Auto-detect: sube hasta encontrar el directorio con inference/
    _candidate = Path(__file__).resolve()
    ROOT_DIR = _candidate.parent          # python-ml-service/
    for _ in range(6):                    # busca hasta 6 niveles arriba
        _candidate = _candidate.parent
        if (_candidate / "inference" / "services").exists():
            ROOT_DIR = _candidate
            break

sys.path.insert(0, str(ROOT_DIR))

# ── Logging ───────────────────────────────────────────────────────────────────
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger = logging.getLogger("mindra-ml")

# ── Aplicación FastAPI ────────────────────────────────────────────────────────
_is_dev = os.getenv("APP_ENV", "production") == "local"

app = FastAPI(
    title="Mindra ML Service",
    description="Microservicio interno para inferencia de ansiedad y transcripción",
    version="2.0.0",
    docs_url="/docs" if _is_dev else None,   # Swagger solo en desarrollo
    redoc_url=None,
)

# CORS solo para llamadas internas (Laravel en el mismo servidor)
# En producción puedes restringirlo más
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)

# ── Servicios (lazy import para no bloquear el arranque) ──────────────────────
_inference_service = None


def get_inference_service():
    global _inference_service
    if _inference_service is None:
        from inference.services.model_inference import AnxietyInferenceService
        _inference_service = AnxietyInferenceService()
    return _inference_service


# ── Helpers ───────────────────────────────────────────────────────────────────
async def save_upload_to_temp(upload: UploadFile) -> Path:
    """Guarda el archivo subido en un archivo temporal y devuelve su ruta."""
    import tempfile
    suffix = Path(upload.filename or "audio.webm").suffix or ".webm"
    tmp = tempfile.NamedTemporaryFile(delete=False, suffix=suffix)
    tmp_path = Path(tmp.name)
    try:
        content = await upload.read()
        tmp_path.write_bytes(content)
    except Exception as exc:
        tmp_path.unlink(missing_ok=True)
        raise exc
    return tmp_path


# ── Endpoints ─────────────────────────────────────────────────────────────────

@app.get("/health")
async def health():
    return {
        "status": "healthy",
        "service": "Mindra ML Service",
        "version": "2.0.0",
    }


@app.post("/transcribe")
async def transcribe(
    audio: UploadFile = File(...),
    language: str = Form(default="es"),
):
    """
    Transcribe un archivo de audio con OpenAI Whisper.
    Retorna: { ok, text, language }
    """
    tmp_path = None
    try:
        tmp_path = await save_upload_to_temp(audio)
        from inference.services.transcription import transcribe_audio_file
        result = transcribe_audio_file(str(tmp_path), language=language)
        return {
            "ok": True,
            "text": result["text"],
            "language": result["language"] or language,
        }
    except Exception as exc:
        logger.exception("Error en /transcribe")
        raise HTTPException(status_code=500, detail=str(exc))
    finally:
        if tmp_path and tmp_path.exists():
            tmp_path.unlink()


@app.post("/predict")
async def predict(
    audio: UploadFile = File(default=None),
    texto: str = Form(default=""),
    duration_seconds: str = Form(default=None),
):
    """
    Inferencia de ansiedad multimodal (audio + texto).
    Retorna: { ok, etiqueta, probabilidad_ansiedad, language,
               model_name, texto, archivo, bot_response }
    """
    if not audio and not texto.strip():
        raise HTTPException(
            status_code=400,
            detail="Debes enviar al menos un archivo de audio o texto.",
        )

    tmp_path = None
    try:
        # 1. Guardar audio temporalmente si existe
        if audio and audio.filename:
            tmp_path = await save_upload_to_temp(audio)

        # 2. Transcripción automática si hay audio pero no texto
        generated_text = texto.strip()
        language = ""

        if tmp_path and not generated_text:
            from inference.services.transcription import transcribe_audio_file
            transcription = transcribe_audio_file(str(tmp_path), language="es")
            generated_text = transcription["text"]
            language       = transcription["language"] or ""

        # 3. Inferencia con el modelo ML
        service = get_inference_service()
        prob = service.predict(
            audio_path=str(tmp_path) if tmp_path else None,
            text=texto.strip(),
            generated_text=generated_text,
        )

        # 4. Respuesta empática del bot
        from inference.services.bot_responses import generate_empathetic_response
        bot_response = generate_empathetic_response(generated_text, prob)

        etiqueta = (
            "Posibles indicadores de ansiedad"
            if prob > 0.5
            else "Sin indicadores fuertes"
        )

        return {
            "ok": True,
            "etiqueta": etiqueta,
            "probabilidad_ansiedad": prob,
            "language": language or "es",
            "model_name": "AnxietyMultimodalModel_Wav2Vec2_BERT",
            "texto": generated_text,
            "archivo": audio.filename if audio else "",
            "bot_response": bot_response,
        }

    except HTTPException:
        raise
    except Exception as exc:
        logger.exception("Error en /predict")
        raise HTTPException(status_code=500, detail=str(exc))
    finally:
        if tmp_path and tmp_path.exists():
            tmp_path.unlink()


# ── Arranque ──────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=int(os.getenv("PYTHON_ML_PORT", 8001)),
        reload=os.getenv("APP_ENV", "production") == "local",
        workers=1,  # 1 worker porque el modelo ML es un singleton
    )
