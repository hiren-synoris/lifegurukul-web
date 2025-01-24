<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeleteLearnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $sender;
    public $get_learner;
    public $url;
    public $subject;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $sender, $get_learner , $url ,$subject)
    {
        $this->user = $user;
        $this->sender = $sender;
        $this->get_learner = $get_learner;
        $this->url = $url;
        $this->subject = $subject;        
    }
   

    public function build()
    {
        return $this->view('admin.mails.delete-learner')
                    ->subject($this->subject)
                    ->from($this->sender)
                    ->with([
                        'user' => $this->user,
                        'get_learner' => $this->get_learner,
                        'url' => $this->url,
                    ]);
    }

    

    
}
