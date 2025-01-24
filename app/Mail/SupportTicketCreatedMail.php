<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $instructor;
    public $support;
    public $url;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $instructor, $support, $url, $subject)
    {
        $this->user = $user;
        $this->instructor = $instructor;
        $this->support = $support;
        $this->url = $url;
        $this->subject = $subject;
    }
        

    public function build()
    {

        $view = $this->view('admin.mails.support-ticket-created');
        
        if (!empty($this->subject)) {

            $view->subject($this->subject);
        }
        return $view;
    }
}
