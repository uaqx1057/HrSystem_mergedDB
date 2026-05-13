<?php

namespace App\Mail;

use App\Models\AirTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AirTicketRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;

    public function __construct(AirTicket $ticket)
    {
        // Ensure the employee relation is loaded
        $this->ticket = $ticket;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Air Ticket Request: ' . $this->ticket->employee->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.air-ticket.request',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
