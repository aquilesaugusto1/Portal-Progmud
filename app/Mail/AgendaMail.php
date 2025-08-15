<?php

namespace App\Mail;

use App\Models\Agenda;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AgendaMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Collection<int, Agenda>
     */
    public Collection $agendas;

    public string $recado;

    public User $remetente;

    /**
     * @param  Collection<int, Agenda>  $agendas
     */
    public function __construct(Collection $agendas, string $recado, User $remetente)
    {
        $this->agendas = $agendas;
        $this->recado = $recado;
        $this->remetente = $remetente;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((string) config('mail.from.address'), (string) config('mail.from.name')),
            subject: 'Sua Agenda de Atividades da Semana',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.agendas',
        );
    }

    /**
     * @return array{}
     */
    public function attachments(): array
    {
        return [];
    }
}
