<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RedeemCoinMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $subject;
    public $datas;    
    public $total;
    public $requestcoin;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($content,$subject,$datas,$total,$requestcoin)
    {
        

        
        $this->content=$content;
        $this->subject=$subject;
        $this->datas = $datas;
        $this->total = $total;
        $this->requestcoin = $requestcoin;
    }

     /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        if(empty(  $this->datas)) {

            $this->datas = collect([]);
        }
        
        $view = $this->view('admin.mails.redeem_coin_notify');
        if(!empty($this->subject)){
            $view->subject($this->subject);
        }
    }
}
