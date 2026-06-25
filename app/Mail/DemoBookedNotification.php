<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoBookedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $demoData;

    public function __construct(array $demoData)
    {
        $this->demoData = $demoData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Demo Booking - ' . $this->demoData['first_name'] . ' ' . $this->demoData['last_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.demo-booked',
        );
    }
}