<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        //

        $this->email_data = $data;



    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        return $this->subject("Password reset")->with([
            'name' => $this->email_data['name'],
            'password' => $this->email_data['password'] ?? '',
            'code' => $this->email_data['code'] ?? null,

        ])->view('email.email-password');
    }
}
