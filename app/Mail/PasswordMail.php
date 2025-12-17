<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $validity;

    public function __construct(User $user, $password, $validity)
    {
        $this->user = $user;
        $this->password = $password;
        $this->validity = $validity;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'password',
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from("no-replay@octopus-software.dev")->view('password', [
            'user' => $this->user,
            'password' => $this->password,
            'validity' => $this->validity,
        ]);
    }
}
