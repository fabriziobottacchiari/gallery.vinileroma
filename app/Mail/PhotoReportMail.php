<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PhotoReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PhotoReportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly PhotoReport $report,
        public readonly ?string $thumbUrl,
        public readonly string $adminUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Segnalazione] Foto evento «' . $this->report->event->title . '»',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.photo-report',
        );
    }
}
