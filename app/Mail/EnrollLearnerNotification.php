<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollLearnerNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content,$subject)
    {
        $this->content=$content;
        $this->subject=$subject;
    }

     /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view = $this->view('admin.mails.enroll_learners');
        if(!empty($this->subject)){
            $view->subject($this->subject);
        }
        return $view->with(['content'=>$this->content]);
    }
}
