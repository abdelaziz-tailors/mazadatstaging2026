<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProfileCompletedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email_data)
    {
        //

        $this->email_data = $email_data;



    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {



        return $this->subject("Welcome to Dacktra Community!")->with([
            'name'=>$this->email_data['name'],
            'phone'=>$this->email_data['phone'],
            'Speciality'=>$this->email_data['Speciality'],
            'email'=>$this->email_data['email'],
            'password'=>$this->email_data['password'] ?? '',

        ])->view('email.email-profile-completed');
    }
}
