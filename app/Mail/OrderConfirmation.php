<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Illuminate\Support\Arr;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public array $data;

    public function __construct($order_id)
    {
        $this->data = Arr::first(Order::with(['products','addresses','user'])->where('id', $order_id)->get()->toArray());
        $this->data['updated_at'] = !empty($this->data['updated_at']) ? date("F j, Y",strtotime($this->data['updated_at'])) : '';
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
            ->subject('Order Confirmation')
            ->view('mail.order_confirmation', $this->data);
    }
}
