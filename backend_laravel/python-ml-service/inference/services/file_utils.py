import os
import tempfile
from pathlib import Path


def save_uploaded_file_temporarily(uploaded_file, suffix=None):
    original_name = uploaded_file.name or "audio.webm"
    ext = suffix or Path(original_name).suffix or ".webm"

    uploaded_file.seek(0)

    temp_file = tempfile.NamedTemporaryFile(delete=False, suffix=ext)
    temp_path = temp_file.name

    try:
        with open(temp_path, "wb+") as destination:
            for chunk in uploaded_file.chunks():
                destination.write(chunk)
    except Exception:
        if os.path.exists(temp_path):
            os.remove(temp_path)
        raise

    return temp_path


def remove_temp_file(path):
    if path and os.path.exists(path):
        os.remove(path)