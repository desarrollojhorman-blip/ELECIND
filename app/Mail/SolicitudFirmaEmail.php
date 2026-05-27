<?php

namespace App\Mail;

use App\Models\TokenFirma;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SolicitudFirmaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly TokenFirma $tokenFirma,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de firma — ' . ($this->tokenFirma->firmable->numero ?? 'Documento'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.solicitud-firma',
        );
    }
}
