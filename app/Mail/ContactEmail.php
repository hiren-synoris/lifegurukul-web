<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contactArray;
    public $flag;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public function __construct($contactArray, $flag = 0)
    {
        $this->contactArray = $contactArray;
        $this->flag = $flag;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = $this->view('admin.mails.contact');
        if(isset($this->flag) && !empty($this->flag) && $this->flag == 1){
            $subject = "Life Gurukul";
        }else{
            $subject = "Contact Us";
        }
        $view->subject($subject);
        return $view->with([ 'content' => $this->contactArray, 'flag' => $this->flag]);
    }
}
