<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminRegistrationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;
    public $learnerData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content,$subject=NULL,$learnerData=[])
    {
        $this->content=$content;
        $this->subject=$subject;
        $this->learnerData = $learnerData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $view = $this->view('admin.mails.registration');
        if(!empty($this->subject)){
            $view->subject($this->subject);
        }
        return $view->with(['content'=>$this->learnerData]);
    }
}
