<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GalleryUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GalleryVerifyEmailMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly GalleryUser $user,
        public readonly string $verifyUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verifica la tua email — Vinile Roma Gallery',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gallery-verify-email',
        );
    }
}
