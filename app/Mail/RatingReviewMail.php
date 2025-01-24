<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RatingReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $sender;
    public $subject;
    public $rating;
    public $course;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $sender, $subject, $rating , $course)
    {       
        $this->user = $user;
        $this->sender = $sender;
        $this->subject = $subject;
        $this->rating = $rating;
        $this->course = $course;
    }


    public function build()
    {

        $view = $this->view('admin.mails.rating-review');

        if (!empty($this->subject)) {

            $view->subject($this->subject);
        }
        return $view;
    }
}
