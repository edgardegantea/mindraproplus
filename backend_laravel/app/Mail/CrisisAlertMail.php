<?php

namespace App\Mail;

use App\Models\InferenceRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CrisisAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User            $user,
        public readonly InferenceRecord $record,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Mindra detectó un momento de alta ansiedad',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.crisis_alert',
            with: [
                'user'        => $this->user,
                'record'      => $this->record,
                'probability' => round(($this->record->predicted_probability ?? 0) * 100),
                'label'       => $this->record->predicted_label ?? 'Alta ansiedad',
            ],
        );
    }
}
