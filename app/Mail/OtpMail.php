<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;
    public $validity;

    public function __construct(User $user, $otp, $validity)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->validity = $validity;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Otp Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail',
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from("no-replay@octopus-software.dev")->view('mail', [
            'user' => $this->user,
            'otp' => $this->otp,
            'validity' => $this->validity,
        ]);
    }
}
