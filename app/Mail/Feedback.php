<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class Feedback extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create email from parameter
     * @var string
     */
    protected string $fromEmail;

    /**
     * Create message from parameter
     * @var string
     */
    protected string $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($fromEmail, $message)
    {
        $this->fromEmail = $fromEmail;
        $this->message = $message;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address($this->fromEmail, 'Autoelements.com – Feedback'),
            subject: 'Feedback from site contact form',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.feedback',
            with: [
                'from' => $this->fromEmail,
                'message' => $this->message,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
