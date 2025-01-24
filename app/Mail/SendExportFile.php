<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendExportFile extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $content;
    public $subject;
    public $file_name;
    public function __construct($content,$subject,$file_name)
    {
        $this->content = $content;
        $this->subject = $subject;
        $this->file_name = $file_name;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function build()
    {
        $file = $this->file_name;
        $view = $this->view('admin.mails.send_export_file',compact("file"));
        if(!empty($this->subject)){

            $view->subject($this->subject);
        }
        return $view;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
