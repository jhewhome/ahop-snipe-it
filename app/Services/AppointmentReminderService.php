<?php

namespace App\Services;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppointmentReminderService
{
    /**
     * Appointments starting within the reminder window that have not been reminded yet.
     */
    public function dueAppointments(?Carbon $now = null): Collection
    {
        $now = $now ?? now();
        $hours = (int) config('ahop.appointment_reminders.hours_before', 24);
        $windowEnd = $now->copy()->addHours($hours);

        return Appointment::query()
            ->with(['patient', 'physician'])
            ->where('status', Appointment::STATUS_SCHEDULED)
            ->whereNull('reminder_sent_at')
            ->whereBetween('scheduled_at', [$now, $windowEnd])
            ->orderBy('scheduled_at')
            ->get();
    }

    public function sendReminder(Appointment $appointment, bool $force = false): array
    {
        $appointment->loadMissing('patient', 'physician');

        if (! $force && $appointment->reminder_sent_at) {
            return ['sent' => false, 'reason' => 'already_sent'];
        }

        if ($appointment->status !== Appointment::STATUS_SCHEDULED) {
            return ['sent' => false, 'reason' => 'invalid_status'];
        }

        $patient = $appointment->patient;
        if (! $patient) {
            return ['sent' => false, 'reason' => 'no_patient'];
        }

        $email = $patient->email;
        if (empty($email)) {
            $this->logSmsPlaceholder($appointment, $patient->contact_number);

            return ['sent' => false, 'reason' => 'no_email'];
        }

        if (! config('ahop.appointment_reminders.enabled', true)) {
            return ['sent' => false, 'reason' => 'disabled'];
        }

        Mail::to($email)->send(new AppointmentReminderMail($appointment));

        $appointment->reminder_sent_at = now();
        $appointment->save();

        return ['sent' => true, 'reason' => null];
    }

    public function sendDueReminders(): array
    {
        $stats = ['sent' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($this->dueAppointments() as $appointment) {
            try {
                $result = $this->sendReminder($appointment);
                if ($result['sent']) {
                    $stats['sent']++;
                } else {
                    $stats['skipped']++;
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                Log::error('AHOP appointment reminder failed', [
                    'appointment_id' => $appointment->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    protected function logSmsPlaceholder(Appointment $appointment, ?string $contactNumber): void
    {
        if (empty($contactNumber) || ! config('ahop.appointment_reminders.log_sms_placeholder', true)) {
            return;
        }

        Log::info('AHOP appointment reminder (SMS not configured)', [
            'appointment' => $appointment->appointment_number,
            'patient' => $appointment->patient?->full_name,
            'contact' => $contactNumber,
            'scheduled_at' => $appointment->scheduled_at?->toDateTimeString(),
            'hint' => 'Add patient email for email reminders, or integrate SMS provider.',
        ]);
    }
}
