<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HelpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $description;


    public function __construct($description)
    {
        $this->description = $description;
    }

    public function build()
    {
        return $this->from('noahconner1512@gmail.com')->subject('Help Created')->view('helpTemplate')->with('help', $this->description);
    }


}
