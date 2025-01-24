<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManualNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;
    public $slug;
    public $redirect_type;
    public $name;
    public $image;
    public $external_link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content,$subject,$slug,$redirect_type,$name,$image,$external_link)
    {
        $this->content=$content;
        $this->subject=$subject;
        $this->slug=$slug;
        $this->redirect_type=$redirect_type;
        $this->name=$name;
        $this->image=$image;
        $this->external_link=$external_link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $view = $this->view('admin.mails.manual_notification');
        if(!empty($this->subject)){

            $view->subject($this->subject);
        }
        return $view;
    }
}
