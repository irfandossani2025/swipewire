<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @param array{name: string, email: string, subject: string, message: string} $supportRequest */
    public function __construct(public array $supportRequest) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('swipewire.mail.welcome_from'), 'Swipewire Support'),
            replyTo: [new Address($this->supportRequest['email'], $this->supportRequest['name'])],
            subject: 'Support: '.$this->supportRequest['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.support-request',
            with: ['supportRequest' => $this->supportRequest],
        );
    }
}
