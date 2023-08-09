<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $email;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noahconner1512@gmail.com',"Me")
        ->to($this->email)
        ->subject('User Credentials')
        ->view('passwordTemplate')->with([
            'email' => $this->email,
            'password' => $this->password,
        ]);
    }
}

?>
