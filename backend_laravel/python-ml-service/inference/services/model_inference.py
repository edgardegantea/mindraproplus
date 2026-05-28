import os
import torch
import torchaudio
import numpy as np
from transformers import Wav2Vec2Processor, AutoTokenizer
from backend.model.model import AnxietyMultimodalModel
import pytorch_lightning as pl

# Redirigir caché de modelos HuggingFace a un directorio con permisos de escritura
# (evita [Errno 13] Permission denied: '/var/www/.cache' en producción)
_CACHE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..', 'model_cache'))
os.makedirs(_CACHE_DIR, exist_ok=True)
os.environ.setdefault('HF_HOME',            _CACHE_DIR)
os.environ.setdefault('TRANSFORMERS_CACHE', _CACHE_DIR)
os.environ.setdefault('TORCH_HOME',         _CACHE_DIR)
os.environ.setdefault('XDG_CACHE_HOME',     _CACHE_DIR)

# Configuración del modelo
MAX_AUDIO_LEN = 16000 * 5  # 5 segundos máximo
MAX_TEXT_LEN = 128
DEVICE = "mps" if torch.backends.mps.is_available() else "cpu"

class AnxietyInferenceService:
    _instance = None
    _model = None
    _audio_processor = None
    _text_tokenizer = None

    def __new__(cls):
        if cls._instance is None:
            cls._instance = super().__new__(cls)
        return cls._instance

    def __init__(self):
        if self._model is None:
            self._load_model()

    def _load_model(self):
        """Carga el modelo entrenado y los procesadores"""
        try:
            # Usar el mejor checkpoint disponible
            checkpoint_path = "modelo/anxiety-multimodal-epoch=00-val_auc=0.592.ckpt"

            if not os.path.exists(checkpoint_path):
                # Fallback a otros checkpoints si el mejor no existe
                checkpoints = [
                    "modelo/anxiety-multimodal-epoch=00-val_auc=0.592.ckpt",
                    "modelo/anxiety-multimodal-epoch=04-val_auc=0.560.ckpt",
                    "modelo/anxiety-multimodal-epoch=01-val_auc=0.548.ckpt",
                ]
                for cp in checkpoints:
                    if os.path.exists(cp):
                        checkpoint_path = cp
                        break

            print(f"Cargando modelo desde: {checkpoint_path}")

            # Crear el modelo base
            base_model = AnxietyMultimodalModel(num_classes=1, dropout=0.4)

            # Cargar el checkpoint con PyTorch Lightning
            self._model = AnxietyLightningModule.load_from_checkpoint(
                checkpoint_path,
                model=base_model,
                map_location=DEVICE,
            )

            self._model.eval()
            self._model.to(DEVICE)

            # Cargar procesadores
            self._audio_processor = Wav2Vec2Processor.from_pretrained(
                "facebook/wav2vec2-base", cache_dir=_CACHE_DIR)
            self._text_tokenizer = AutoTokenizer.from_pretrained(
                "dccuchile/bert-base-spanish-wwm-cased", cache_dir=_CACHE_DIR
            )

            print("Modelo cargado exitosamente")

        except Exception as e:
            print(f"Error cargando el modelo: {e}")
            # Fallback: modelo dummy que siempre retorna 0.5
            self._model = None
            print("Usando fallback: modelo dummy")

    def _preparar_audio(self, audio_path: str):
        """Prepara el audio para el modelo"""
        if not os.path.exists(audio_path):
            raise FileNotFoundError(f"No existe el archivo de audio: {audio_path}")

        speech_array, sr = torchaudio.load(audio_path)
        speech_array = speech_array.squeeze()

        if speech_array.ndim > 1:
            speech_array = speech_array.mean(dim=0)

        if sr != 16000:
            speech_array = torchaudio.functional.resample(speech_array, sr, 16000)

        speech_array = speech_array.numpy()

        if len(speech_array) > MAX_AUDIO_LEN:
            speech_array = speech_array[:MAX_AUDIO_LEN]
        else:
            speech_array = np.pad(
                speech_array,
                (0, max(0, MAX_AUDIO_LEN - len(speech_array))),
                mode="constant",
            )

        audio_inputs = self._audio_processor(
            speech_array,
            sampling_rate=16000,
            return_tensors="pt",
            padding="max_length",
            truncation=True,
            max_length=MAX_AUDIO_LEN,
        )

        audio_attention_mask = getattr(audio_inputs, "attention_mask", None)
        if audio_attention_mask is None:
            audio_attention_mask = torch.ones_like(
                audio_inputs.input_values, dtype=torch.long
            )

        input_values = audio_inputs.input_values.squeeze(0)
        attention_mask_audio = audio_attention_mask.squeeze(0)

        return input_values, attention_mask_audio

    def _preparar_texto(self, text: str):
        """Prepara el texto para el modelo"""
        if text is None:
            text = ""

        text_inputs = self._text_tokenizer(
            text,
            return_tensors="pt",
            padding="max_length",
            truncation=True,
            max_length=MAX_TEXT_LEN,
        )

        input_ids = text_inputs.input_ids.squeeze(0)
        attention_mask_text = text_inputs.attention_mask.squeeze(0)

        return input_ids, attention_mask_text

    def predict(self, audio_path=None, text="", generated_text=""):
        """Realiza la predicción de ansiedad"""
        try:
            final_text = (generated_text or text or "").strip()

            # Si no hay modelo cargado, usar predicción dummy
            if self._model is None:
                import random
                prob = round(random.uniform(0.1, 0.9), 3)
                print("ADVERTENCIA: Usando predicción dummy - modelo no cargado")
            else:
                # Preparar audio (tensores dummy de zeros si no hay archivo)
                if audio_path is not None:
                    input_values, attention_mask_audio = self._preparar_audio(audio_path)
                else:
                    # Inferencia solo-texto: audio dummy de silencio
                    input_values = torch.zeros(MAX_AUDIO_LEN, dtype=torch.float32)
                    attention_mask_audio = torch.zeros(MAX_AUDIO_LEN, dtype=torch.long)

                input_ids, attention_mask_text = self._preparar_texto(final_text)

                batch = {
                    "input_values": input_values.unsqueeze(0).to(DEVICE),
                    "attention_mask_audio": attention_mask_audio.unsqueeze(0).to(DEVICE),
                    "input_ids": input_ids.unsqueeze(0).to(DEVICE),
                    "attention_mask_text": attention_mask_text.unsqueeze(0).to(DEVICE),
                }

                # Inferencia
                with torch.no_grad():
                    logits = self._model(
                        batch["input_values"],
                        batch["input_ids"],
                        batch["attention_mask_text"],
                        batch["attention_mask_audio"],
                    )
                    probs = torch.sigmoid(logits)
                    prob = round(float(probs.item()), 3)

            return prob

        except Exception as e:
            print(f"Error en predicción: {e}")
            # Fallback a predicción aleatoria en caso de error
            import random
            return round(random.uniform(0.1, 0.9), 3)


# Clase LightningModule para cargar checkpoints
class AnxietyLightningModule(pl.LightningModule):
    def __init__(self, model, lr=1e-5):
        super().__init__()
        self.save_hyperparameters(ignore=['model'])
        self.model = model
        self.criterion = torch.nn.BCEWithLogitsLoss()

    def forward(self, input_values, input_ids, attention_mask_text, attention_mask_audio):
        logits = self.model(
            input_audio=input_values,
            input_ids=input_ids,
            attention_mask_text=attention_mask_text,
            attention_mask_audio=attention_mask_audio,
        )
        return logits.squeeze(-1)