<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User  $user,
        public readonly array $stats
    ) {}

    public function envelope(): Envelope
    {
        $weekStart = now()->subWeek()->format('d/m');
        $weekEnd   = now()->format('d/m/Y');

        return new Envelope(
            subject: "Tu resumen semanal de Mindra ({$weekStart} – {$weekEnd}) 📊"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly_report',
            with: [
                'user'  => $this->user,
                'stats' => $this->stats,
            ]
        );
    }
}
