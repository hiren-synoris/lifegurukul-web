<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketChatMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $sender;
    public $support;
    public $supportChat;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $sender, $support, $supportChat, $url , $subject)
    {
        $this->user = $user;
        $this->sender = $sender;
        $this->support = $support;
        $this->supportChat = $supportChat;
        $this->url = $url;
        $this->subject = $subject;
    }
   

    public function build()
    {

        $view = $this->view('admin.mails.support-ticket-chat');
        
        if (!empty($this->subject)) {

            $view->subject($this->subject);
        }
        return $view;
    }

   
}
