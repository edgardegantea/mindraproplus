import os
import whisper

# Redirigir caché a un directorio con permisos de escritura
# (evita [Errno 13] Permission denied: '/var/www/.cache' en producción)
_CACHE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'model_cache'))
os.makedirs(_CACHE_DIR, exist_ok=True)
os.environ.setdefault('XDG_CACHE_HOME', _CACHE_DIR)
os.environ.setdefault('HF_HOME',        _CACHE_DIR)

_MODEL = None


def get_whisper_model():
    global _MODEL
    if _MODEL is None:
        _MODEL = whisper.load_model("base", download_root=_CACHE_DIR)
    return _MODEL


def transcribe_audio_file(file_path, language="es"):
    model = get_whisper_model()

    result = model.transcribe(
        file_path,
        language=language,
        fp16=False
    )

    return {
        "text": (result.get("text") or "").strip(),
        "language": result.get("language"),
        "raw": result,
    }