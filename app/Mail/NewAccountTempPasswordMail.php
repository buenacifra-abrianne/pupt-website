<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewAccountTempPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullName,
        public string $email,
        public string $roleLabel,
        public string $tempPassword
    ) {}

    public function build()
    {
        return $this->subject('Your PUP Taguig CMS Account (Temporary Password)')
            ->view('emails.new_account_temp_password');
    }
}