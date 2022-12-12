<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Feedback extends Mailable
{
    use Queueable, SerializesModels;

    public string $messages;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($messages)
    {
        $this->messages = $messages;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from($address = env('MAIL_USERNAME'), $name = env('APP_NAME'))
            ->subject('Feedback')
            ->view('mail.feedback',['messages' => htmlspecialchars($this->messages)]);
    }
}
