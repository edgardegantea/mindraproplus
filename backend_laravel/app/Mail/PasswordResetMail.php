<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public readonly string $resetUrl;

    public function __construct(
        public readonly User   $user,
        string                 $token,
    ) {
        // El enlace lleva el token + email para que la app/web pueda usarlos
        // como parámetros en POST /api/auth/reset-password.
        $this->resetUrl = config('app.url')
            . '/reset-password'
            . '?token='  . urlencode($token)
            . '&email='  . urlencode($user->email);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '🔑 Restablece tu contraseña — Mindra');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password_reset',
            with: [
                'user'     => $this->user,
                'resetUrl' => $this->resetUrl,
            ],
        );
    }
}
