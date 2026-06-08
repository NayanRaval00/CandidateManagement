<?php

namespace App\Mail;

use App\Models\TimesheetRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimesheetNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TimesheetRecord $record,
        public string $pdfData,
        public string $filename
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $batch = $this->record->batch;
        $dateRange = $batch->start_date->format('Y-m-d').' to '.$batch->end_date->format('Y-m-d');

        return new Envelope(
            subject: "Your Timesheet Report ({$dateRange})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.timesheet-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfData, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
