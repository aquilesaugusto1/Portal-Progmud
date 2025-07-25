<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\User;

class AgendaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $agendas;
    public string $recado;
    public User $remetente;

    public function __construct(Collection $agendas, string $recado, User $remetente)
    {
        $this->agendas = $agendas;
        $this->recado = $recado;
        $this->remetente = $remetente;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Sua Agenda de Atividades da Semana',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.agendas',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
