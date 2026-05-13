<?php

namespace App\Mail;

use App\Models\AirTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AirTicketStatusUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;

    public function __construct(AirTicket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update: Your Air Ticket Request has been ' . ucfirst($this->ticket->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.air-ticket.status_update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
