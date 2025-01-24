<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    private $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $url = env('APP_URL').'/backoffice/forgot-password/'.Crypt::encrypt($this->user->id).'/edit';
        return $this->view('admin.mails.forgot-password')->subject('Reset Password link for Lifegurukul')->with(['user' => $this->user, 'url' => $url]);
    }
}
