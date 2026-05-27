<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param User   $user
     * @param string $planSlug  'pro' | 'plus'
     * @param string $planName  'Pro' | 'Plus'
     * @param array  $features  Features activas
     */
    public function __construct(
        public readonly User   $user,
        public readonly string $planSlug,
        public readonly string $planName,
        public readonly array  $features = [],
    ) {}

    public function envelope(): Envelope
    {
        $emoji = $this->planSlug === 'plus' ? '✦' : '⭐';
        return new Envelope(
            subject: "{$emoji} ¡Tu plan {$this->planName} de Mindra está activo!"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.plan_activated',
            with: [
                'user'     => $this->user,
                'planSlug' => $this->planSlug,
                'planName' => $this->planName,
                'features' => $this->features,
            ],
        );
    }
}
