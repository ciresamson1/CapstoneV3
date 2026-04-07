<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $inviteUrl;
    public string $role;

    public function __construct(string $inviteUrl, string $role)
    {
        $this->inviteUrl = $inviteUrl;
        $this->role = $role;
    }

    public function build()
    {
        return $this->subject('Complete your PCMS account setup')
            ->view('emails.invite-user');
    }
}
