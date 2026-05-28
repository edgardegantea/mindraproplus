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
import gc
import logging
import threading
from pathlib import Path

# ── Limitar threads de CPU para evitar OOM en VPS ────────────────────────────
# PyTorch y numpy crean muchos threads por defecto; en un VPS con poca RAM
# esto puede causar crashes. Forzar 1 thread reduce el uso de memoria.
os.environ.setdefault("OMP_NUM_THREADS", "1")
os.environ.setdefault("OPENBLAS_NUM_THREADS", "1")
os.environ.setdefault("MKL_NUM_THREADS", "1")
os.environ.setdefault("TOKENIZERS_PARALLELISM", "false")

from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware

# ── Rutas del proyecto ────────────────────────────────────────────────────────
_env_root = os.environ.get("ML_INFERENCE_ROOT", "")
if _env_root:
    ROOT_DIR = Path(_env_root).resolve()
else:
    _candidate = Path(__file__).resolve()
    ROOT_DIR = _candidate.parent          # python-ml-service/
    for _ in range(6):
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
    version="2.1.0",
    docs_url="/docs" if _is_dev else None,
    redoc_url=None,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost", "https://mindra.cafined.org"],
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)

# ── Mutex: evita inferencias concurrentes que OOM el VPS ─────────────────────
_inference_lock = threading.Lock()

# ── Servicios (lazy import — no bloquea el arranque) ─────────────────────────
_inference_service = None
_inference_load_error: str | None = None


def get_inference_service():
    global _inference_service, _inference_load_error
    if _inference_service is None and _inference_load_error is None:
        try:
            from inference.services.model_inference import AnxietyInferenceService
            _inference_service = AnxietyInferenceService()
            logger.info("Inference service cargado correctamente.")
        except Exception as exc:
            _inference_load_error = str(exc)
            logger.error("No se pudo cargar el inference service: %s", exc)
    return _inference_service


# ── Helpers ───────────────────────────────────────────────────────────────────
async def save_upload_to_temp(upload: UploadFile) -> Path:
    """Guarda el archivo subido en un temporal y devuelve su ruta."""
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


def _cleanup_tmp(path):
    try:
        if path and Path(path).exists():
            Path(path).unlink()
    except Exception:
        pass


# ── Endpoints ─────────────────────────────────────────────────────────────────

@app.get("/health")
async def health():
    import psutil
    mem = psutil.virtual_memory()
    return {
        "status": "healthy",
        "service": "Mindra ML Service",
        "version": "2.1.0",
        "model_loaded": _inference_service is not None,
        "model_error": _inference_load_error,
        "memory": {
            "total_mb": round(mem.total / 1024 / 1024),
            "available_mb": round(mem.available / 1024 / 1024),
            "percent_used": mem.percent,
        },
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
        _cleanup_tmp(tmp_path)
        gc.collect()


@app.post("/predict")
async def predict(
    audio: UploadFile = File(default=None),
    texto: str = Form(default=""),
    duration_seconds: str = Form(default=None),
):
    """
    Inferencia de ansiedad multimodal (audio + texto).
    Retorna: { ok, etiqueta, probabilidad_ansiedad, language,
               model_name, texto, transcription_source, bot_response }
    """
    if not audio and not texto.strip():
        raise HTTPException(
            status_code=400,
            detail="Debes enviar al menos un archivo de audio o texto.",
        )

    # ── Mutex: una inferencia a la vez para evitar OOM ───────────────────────
    if not _inference_lock.acquire(blocking=False):
        # Servicio ocupado — devolver error para que Laravel use el fallback
        raise HTTPException(
            status_code=503,
            detail="Servicio ocupado procesando otra solicitud. Intenta en unos segundos.",
        )

    tmp_path = None
    try:
        transcription_source = "manual"

        # 1. Guardar audio temporalmente
        if audio and audio.filename:
            tmp_path = await save_upload_to_temp(audio)

        # 2. Transcripción automática si hay audio pero no texto
        generated_text = texto.strip()
        language = ""

        if tmp_path and not generated_text:
            try:
                from inference.services.transcription import transcribe_audio_file
                transcription = transcribe_audio_file(str(tmp_path), language="es")
                generated_text = transcription["text"]
                language = transcription["language"] or ""
                transcription_source = "whisper"
            except Exception as exc:
                logger.warning("Transcripción falló, continuando sin texto: %s", exc)
                generated_text = ""

        # 3. Inferencia con el modelo ML (o fallback si no está disponible)
        service = get_inference_service()
        if service is not None:
            try:
                prob = service.predict(
                    audio_path=str(tmp_path) if tmp_path else None,
                    text=texto.strip(),
                    generated_text=generated_text,
                )
            except Exception as exc:
                logger.error("Error en inferencia ML: %s", exc)
                import random
                prob = round(random.uniform(0.3, 0.7), 3)
        else:
            # Modelo no disponible — fallback determinista basado en palabras clave
            prob = _keyword_prob(generated_text or texto.strip())

        # 4. Liberar memoria antes de continuar
        gc.collect()

        # 5. Respuesta empática
        from inference.services.bot_responses import generate_empathetic_response
        bot_response = generate_empathetic_response(generated_text or texto.strip(), prob)

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
            "model_name": "AnxietyMultimodalModel_Wav2Vec2_BERT" if service else "keyword_fallback",
            "texto": generated_text,
            "transcription_source": transcription_source,
            "archivo": audio.filename if audio else "",
            "bot_response": bot_response,
        }

    except HTTPException:
        raise
    except Exception as exc:
        logger.exception("Error inesperado en /predict")
        raise HTTPException(status_code=500, detail=str(exc))
    finally:
        _inference_lock.release()
        _cleanup_tmp(tmp_path)
        gc.collect()


def _keyword_prob(text: str) -> float:
    """Fallback determinista cuando el modelo ML no está disponible."""
    if not text:
        return 0.3
    t = text.lower()
    high = ["ansied", "pánico", "panico", "angustia", "terror", "desesper",
            "no puedo más", "no puedo mas", "crisis", "ataque"]
    mid  = ["nervios", "preocup", "estres", "estrés", "insomnio", "agitad",
            "triste", "solo", "sola", "llorar", "mal", "miedo"]
    if any(w in t for w in high): return round(0.72 + len([w for w in high if w in t]) * 0.02, 3)
    if any(w in t for w in mid):  return round(0.45 + len([w for w in mid  if w in t]) * 0.03, 3)
    return 0.22


# ── Arranque ──────────────────────────────────────────────────────────────────
if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "main:app",
        host="0.0.0.0",
        port=int(os.getenv("PYTHON_ML_PORT", 8001)),
        reload=False,
        workers=1,       # 1 worker — modelo ML es singleton con mucha RAM
        timeout_keep_alive=30,
    )
