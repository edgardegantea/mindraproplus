<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlusRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array  $data  Todos los campos del formulario
     * @param string $type  'confirmation' | 'notification' | 'in_review' | 'approved' | 'rejected'
     */
    public function __construct(
        public readonly array  $data,
        public readonly string $type = 'confirmation'
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'confirmation' => '✅ Hemos recibido tu solicitud — Plan Plus Mindra',
            'notification' => '📩 Nueva solicitud Plan Plus — ' . ($this->data['org_name'] ?? $this->data['requester_name'] ?? ''),
            'in_review'    => '🔍 Tu solicitud está en revisión — Plan Plus Mindra',
            'approved'     => '🎉 ¡Tu acceso Plus fue aprobado! — Mindra',
            'rejected'     => 'Actualización sobre tu solicitud — Plan Plus Mindra',
        ];

        $subject = $subjects[$this->type] ?? '📩 Solicitud Plan Plus — Mindra';

        // CC al admin en el email de confirmación al solicitante
        $cc = [];
        if ($this->type === 'confirmation') {
            $adminEmail = config('mail.admin_address', config('mail.from.address'));
            if ($adminEmail) {
                $cc[] = new Address($adminEmail, 'Mindra — Admin');
            }
        }

        return new Envelope(subject: $subject, cc: $cc);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.plus_request',
            with: [
                'data' => $this->data,
                'type' => $this->type,
            ],
        );
    }
}
