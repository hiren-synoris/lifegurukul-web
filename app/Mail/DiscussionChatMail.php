<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscussionChatMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $user;
    public $sender;
    public $url;
    public $course;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $user, $sender, $course, $url)
    {
        $this->subject = $subject;
        $this->user = $user;
        $this->sender = $sender;
        $this->course = $course;
        $this->url = $url;
    }
   

    public function build()
    {

        $view = $this->view('admin.mails.discussion-chat');
        if (!empty($this->subject)) {

            $view->subject($this->subject);
        }
        return $view;
    }


    
}
