<?php

namespace App\Mail;

use App\Models\Holiday;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HolidayNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Holiday $holiday;

    public bool $isToday;

    public function __construct(Holiday $holiday, bool $isToday = false)
    {
        $this->holiday = $holiday;
        $this->isToday = $isToday;
    }

    public function envelope(): Envelope
    {
        $timing = $this->isToday ? 'Today' : 'Tomorrow';

        return new Envelope(
            subject: "{$timing} is a Holiday: {$this->holiday->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.holidays.notification',
            with: [
                'holiday' => $this->holiday,
                'isToday' => $this->isToday,
            ],
        );
    }
}
