<?php

namespace App\Console\Commands;

use App\Services\AppointmentReminderService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'ahop:send-appointment-reminders';

    protected $description = 'Email patients about upcoming appointments (AHOP reminders)';

    public function handle(AppointmentReminderService $service): int
    {
        if (! config('ahop.appointment_reminders.enabled', true)) {
            $this->warn('Appointment reminders are disabled (AHOP_APPOINTMENT_REMINDERS=false).');

            return self::SUCCESS;
        }

        $due = $service->dueAppointments();
        $this->info('Due for reminder: '.$due->count());

        $stats = $service->sendDueReminders();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Sent', $stats['sent']],
                ['Skipped (no email / already sent)', $stats['skipped']],
                ['Errors', $stats['errors']],
            ]
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
