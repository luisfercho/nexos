<?php

namespace Nexos\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Nexos\Account;

class AccountCreatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $account;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Account $account){
        $this->account = $account;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.accounts');
    }
}
