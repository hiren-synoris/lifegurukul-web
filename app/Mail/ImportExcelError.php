<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ImportExcelError extends Mailable
{
    use Queueable, SerializesModels;

    public $failures;
    public $subject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$failures)
    {
        $this->failures=$failures;
        $this->subject=$subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $view = $this->view('admin.mails.import_excel_error');
        if(!empty($this->subject)){

            $view->subject($this->subject);
        }
        return $view;
    }
}
