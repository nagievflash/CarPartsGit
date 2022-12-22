<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingReceipt extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public array $product;

    public function __construct($product)
    {
        $this->product = $product;
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
            ->subject('PendingReceipt')
            ->view('mail.pending_receipt',$this->product);
    }
}
