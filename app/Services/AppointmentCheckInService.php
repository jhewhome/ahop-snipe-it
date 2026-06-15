<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\OpdVisit;
use App\Services\ClinicSiteService;
use Illuminate\Support\Facades\DB;

class AppointmentCheckInService
{
    public function __construct(
        protected ClinicSiteService $clinicSiteService,
    ) {
    }

    public function checkIn(Appointment $appointment, ?int $userId = null): OpdVisit
    {
        if (! $appointment->canCheckIn()) {
            throw new \InvalidArgumentException(trans('admin/appointments/message.check_in.invalid_status'));
        }

        $appointment->loadMissing('patient');

        return DB::connection($appointment->getConnectionName())->transaction(function () use ($appointment, $userId) {
            $visit = new OpdVisit;
            $visit->visit_number = OpdVisit::generateNextVisitNumber();
            $visit->patient_id = $appointment->patient_id;
            $visit->physician_id = $appointment->physician_id;
            $visit->visit_date = now();
            $visit->visit_type = $appointment->visit_type;
            $visit->status = OpdVisit::STATUS_IN_PROGRESS;
            $visit->chief_complaint = $appointment->reason;
            $visit->company_id = $this->clinicSiteService->resolve(
                $appointment->company_id ? (int) $appointment->company_id : null,
                $appointment->patient
            );
            $visit->created_by = $userId ?? auth()->id();

            if ($visit->company_id && $appointment->patient && ! $appointment->patient->company_id) {
                $appointment->patient->company_id = $visit->company_id;
                $appointment->patient->saveQuietly();
            }

            if (! $visit->save()) {
                throw new \RuntimeException(trans('admin/appointments/message.check_in.opd_failed'));
            }

            $appointment->opd_visit_id = $visit->id;
            $appointment->status = Appointment::STATUS_CHECKED_IN;

            if (! $appointment->save()) {
                throw new \RuntimeException(trans('admin/appointments/message.check_in.appointment_failed'));
            }

            return $visit;
        });
    }
}
