<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public string $token;
    public string $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token,$email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    protected function resetUrl():string
    {
        return url(route('password.reset', [
            'token' => $this->token,
            'email' =>  $this->email,
        ], false));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->from($address = env('MAIL_USERNAME'), $name = env('APP_NAME'))
            ->subject('Reset Password')
            ->view('mail.reset_password',['url' => $this->resetUrl()]);
    }
}
