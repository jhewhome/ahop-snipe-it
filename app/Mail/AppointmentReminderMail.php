<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends BaseMailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        $this->appointment->loadMissing('patient', 'physician');
    }

    public function envelope(): Envelope
    {
        $from = new Address(config('mail.from.address'), config('mail.from.name'));

        return new Envelope(
            from: $from,
            subject: trans('admin/appointments/mail.reminder_subject', [
                'site' => config('ahop.default_site_name', 'AgilityCare'),
                'date' => $this->appointment->scheduled_at?->format('M j, Y'),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'notifications.markdown.appointment-reminder',
            with: [
                'appointment' => $this->appointment,
                'patient' => $this->appointment->patient,
                'siteName' => config('ahop.default_site_name', 'AgilityCare Health Operations Platform'),
            ],
        );
    }
}
