import torch
import torch.nn as nn
from transformers import Wav2Vec2Model, AutoModel


class AnxietyMultimodalModel(nn.Module):
    def __init__(self, num_classes=1, dropout=0.4):
        super().__init__()

        self.audio_encoder = Wav2Vec2Model.from_pretrained("facebook/wav2vec2-base")
        self.text_encoder = AutoModel.from_pretrained(
            "dccuchile/bert-base-spanish-wwm-cased"
        )

        self.cross_attention = nn.MultiheadAttention(
            embed_dim=768, num_heads=8, batch_first=True, dropout=dropout
        )
        self.layer_norm = nn.LayerNorm(768)
        self.dropout = nn.Dropout(dropout)

        self.classifier = nn.Sequential(
            nn.Linear(768 * 2, 256),
            nn.ReLU(),
            nn.Dropout(dropout),
            nn.Linear(256, num_classes),
        )

    def freeze_all_backbones(self):
        for param in self.audio_encoder.parameters():
            param.requires_grad = False

        for param in self.text_encoder.parameters():
            param.requires_grad = False

    def unfreeze_last_bert_layers(self, n_layers=2):
        for param in self.text_encoder.parameters():
            param.requires_grad = False

        for layer in self.text_encoder.encoder.layer[-n_layers:]:
            for param in layer.parameters():
                param.requires_grad = True

        if (
            hasattr(self.text_encoder, "pooler")
            and self.text_encoder.pooler is not None
        ):
            for param in self.text_encoder.pooler.parameters():
                param.requires_grad = True

    def forward(
        self, input_audio, input_ids, attention_mask_text, attention_mask_audio=None
    ):
        audio_out = self.audio_encoder(
            input_values=input_audio, attention_mask=attention_mask_audio
        ).last_hidden_state

        audio_pooled = torch.mean(audio_out, dim=1, keepdim=True)

        text_out = self.text_encoder(
            input_ids=input_ids, attention_mask=attention_mask_text
        ).last_hidden_state

        attended, _ = self.cross_attention(
            query=text_out, key=audio_pooled, value=audio_pooled
        )

        text_fused = self.layer_norm(text_out + attended)
        text_pooled = torch.mean(text_fused, dim=1)

        audio_pooled = audio_pooled.squeeze(1)
        combined = torch.cat([text_pooled, audio_pooled], dim=1)
        combined = self.dropout(combined)

        return self.classifier(combined)
