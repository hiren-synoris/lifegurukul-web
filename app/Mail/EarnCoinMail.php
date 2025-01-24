<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EarnCoinMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;
    public $datas;
    // public $user_coin;
    public $total;
    public $requestcoin;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content,$subject,$datas,$requestcoin,$total)
    {
        
        $this->content=$content;
        $this->subject=$subject;
        $this->datas = $datas;
        // $this->user_coin = $user_coin;        
        $this->requestcoin = $requestcoin;
        $this->total = $total;
    }

     /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        if(empty($this->datas)) {

            $this->datas = collect([]);
        }

        $view = $this->view('admin.mails.earn_coin_notify');
        if(!empty($this->subject)){
            $view->subject($this->subject);
        }
    }
}
