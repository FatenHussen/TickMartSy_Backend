<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceProviderApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $vendorName,
        public ?string $shopName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم قبول طلبك كمزود خدمة - سنتواصل معك قريباً',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-provider-approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
