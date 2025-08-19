<?php

namespace App\Mail;

use App\Models\Apontamento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApontamentoRejeitadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $apontamento;

    public function __construct(Apontamento $apontamento)
    {
        $this->apontamento = $apontamento;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Seu Apontamento foi Rejeitado',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.apontamento-rejeitado',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}