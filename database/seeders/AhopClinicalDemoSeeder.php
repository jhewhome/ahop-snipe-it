<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BillingInvoice;
use App\Models\LabOrder;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Models\User;
use App\Services\OpdVisitInvoiceService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Demo patients and sample clinical rows for local/testing (AHOP).
 * Safe to re-run: skips patients that already exist by patient_number.
 */
class AhopClinicalDemoSeeder extends Seeder
{
    public function run(int $patientCount = 25): void
    {
        if (! Schema::hasTable('patients')) {
            $this->command?->error('Patients table not found. Run clinical migrations first.');

            return;
        }

        $companySeeder = new AhopCompanySeeder;
        $companySeeder->setCommand($this->command);
        $companies = $companySeeder->run(backfillPatients: true);
        $companyIds = $companies['ids'] ?: array_filter([$companies['default_id']]);
        $defaultCompanyId = $companies['default_id'];

        $created = 0;

        $samples = $this->samplePatients();

        foreach (array_slice($samples, 0, $patientCount) as $index => $row) {
            if (Patient::where('patient_number', $row['patient_number'])->exists()) {
                continue;
            }

            $patient = Patient::create(array_merge($row, [
                'company_id' => $this->companyIdForIndex($companyIds, $defaultCompanyId, $index),
                'created_by' => null,
            ]));

            $created++;

            if ($index < 3) {
                $visit = $this->seedOpdVisit($patient, $index);

                if ($index === 0 && $visit) {
                    $this->seedDemoBilling($visit);
                }
            }

            if ($index === 0) {
                $this->seedAppointment($patient);
            }

            if ($index === 1) {
                $this->seedLabOrder($patient);
            }
        }

        $this->command?->info("Demo clinical data: {$created} new patient(s).");
        $this->command?->line('  Patients → Clinical Services → Patients');
        $this->command?->line('  Billing → demo invoice on first OPD visit (if new)');
        $this->command?->line('  Equipment → php artisan ahop:seed-equipment --demo-assets --demo-it-assets');
    }

    protected function seedOpdVisit(Patient $patient, int $index): ?OpdVisit
    {
        $status = match ($index) {
            0 => OpdVisit::STATUS_COMPLETED,
            1 => OpdVisit::STATUS_IN_PROGRESS,
            default => OpdVisit::STATUS_SCHEDULED,
        };

        return OpdVisit::create([
            'visit_number' => OpdVisit::generateNextVisitNumber(),
            'patient_id' => $patient->id,
            'physician_id' => $this->demoPhysicianId(),
            'visit_date' => now()->subDays(2 - $index),
            'visit_type' => $index === 1 ? OpdVisit::TYPE_FOLLOW_UP : OpdVisit::TYPE_INITIAL,
            'status' => $status,
            'chief_complaint' => 'Demo visit — routine check',
            'blood_pressure' => '120/80',
            'pulse_rate' => 72,
            'temperature' => 36.8,
            'assessment' => 'Demo assessment for testing.',
            'diagnosis' => 'Demo diagnosis (test data).',
            'company_id' => $patient->company_id,
        ]);
    }

    protected function seedDemoBilling(OpdVisit $visit): void
    {
        if (! Schema::hasTable('billing_invoices')) {
            return;
        }

        $exists = BillingInvoice::query()
            ->where('opd_visit_id', $visit->id)
            ->where('status', '!=', BillingInvoice::STATUS_CANCELLED)
            ->exists();

        if ($exists) {
            return;
        }

        try {
            app(OpdVisitInvoiceService::class)->createOrOpenForVisit($visit);
        } catch (\Throwable $e) {
            $this->command?->warn('Could not seed demo billing invoice: '.$e->getMessage());
        }
    }

    protected function seedAppointment(Patient $patient): void
    {
        Appointment::create([
            'appointment_number' => Appointment::generateNextAppointmentNumber(),
            'patient_id' => $patient->id,
            'physician_id' => $this->demoPhysicianId(),
            'scheduled_at' => now()->addDay()->setTime(9, 0),
            'duration_minutes' => 30,
            'visit_type' => OpdVisit::TYPE_INITIAL,
            'status' => Appointment::STATUS_SCHEDULED,
            'reason' => 'Demo follow-up appointment',
            'company_id' => $patient->company_id,
        ]);
    }

    protected function seedLabOrder(Patient $patient): void
    {
        LabOrder::create([
            'order_number' => LabOrder::generateNextOrderNumber(),
            'patient_id' => $patient->id,
            'test_panel' => 'CBC',
            'status' => LabOrder::STATUS_ORDERED,
            'priority' => 'routine',
            'ordered_at' => now(),
            'company_id' => $patient->company_id,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function samplePatients(): array
    {
        $rows = [
            ['patient_number' => 'AC-900001', 'full_name' => 'Maria Santos Dela Cruz', 'sex' => 'F', 'birthdate' => '1985-03-12', 'contact_number' => '09171234501', 'email' => 'demo.maria@example.com'],
            ['patient_number' => 'AC-900002', 'full_name' => 'Juan Reyes Garcia', 'sex' => 'M', 'birthdate' => '1978-07-22', 'contact_number' => '09181234502', 'email' => 'demo.juan@example.com'],
            ['patient_number' => 'AC-900003', 'full_name' => 'Ana Mendoza Torres', 'sex' => 'F', 'birthdate' => '1992-11-05', 'contact_number' => '09191234503', 'email' => null],
            ['patient_number' => 'AC-900004', 'full_name' => 'Jose Bautista Ramos', 'sex' => 'M', 'birthdate' => '1965-01-18', 'contact_number' => '09201234504', 'email' => 'demo.jose@example.com'],
            ['patient_number' => 'AC-900005', 'full_name' => 'Grace Villanueva Cruz', 'sex' => 'F', 'birthdate' => '2001-09-30', 'contact_number' => '09211234505', 'email' => null],
            ['patient_number' => 'AC-900006', 'full_name' => 'Mark Anthony Flores', 'sex' => 'M', 'birthdate' => '1988-04-14', 'contact_number' => '09221234506', 'email' => 'demo.mark@example.com'],
            ['patient_number' => 'AC-900007', 'full_name' => 'Patricia Aquino Navarro', 'sex' => 'F', 'birthdate' => '1995-12-08', 'contact_number' => '09231234507', 'email' => null],
            ['patient_number' => 'AC-900008', 'full_name' => 'Paolo Castillo Fernandez', 'sex' => 'M', 'birthdate' => '1972-06-25', 'contact_number' => '09241234508', 'email' => 'demo.paolo@example.com'],
            ['patient_number' => 'AC-900009', 'full_name' => 'Bianca Rivera Domingo', 'sex' => 'F', 'birthdate' => '1999-02-17', 'contact_number' => '09251234509', 'email' => null],
            ['patient_number' => 'AC-900010', 'full_name' => 'Jerome de Guzman Test', 'sex' => 'M', 'birthdate' => '1990-05-16', 'contact_number' => '09261234510', 'email' => 'demo.jerome@example.com'],
            ['patient_number' => 'AC-900011', 'full_name' => 'Liza Mercado Villanueva', 'sex' => 'F', 'birthdate' => '1983-08-03', 'contact_number' => '09271234511', 'email' => 'demo.liza@example.com'],
            ['patient_number' => 'AC-900012', 'full_name' => 'Ramon Castillo Navarro', 'sex' => 'M', 'birthdate' => '1959-12-19', 'contact_number' => '09281234512', 'email' => null],
            ['patient_number' => 'AC-900013', 'full_name' => 'Cherry Ann Lopez', 'sex' => 'F', 'birthdate' => '1997-04-27', 'contact_number' => '09291234513', 'email' => 'demo.cherry@example.com'],
            ['patient_number' => 'AC-900014', 'full_name' => 'Edgar Tan Sy', 'sex' => 'M', 'birthdate' => '1975-10-11', 'contact_number' => '09301234514', 'email' => null],
            ['patient_number' => 'AC-900015', 'full_name' => 'Helen Cruz Bautista', 'sex' => 'F', 'birthdate' => '1968-02-28', 'contact_number' => '09311234515', 'email' => 'demo.helen@example.com'],
            ['patient_number' => 'AC-900016', 'full_name' => 'Francis Mendoza', 'sex' => 'M', 'birthdate' => '2003-07-09', 'contact_number' => '09321234516', 'email' => null],
            ['patient_number' => 'AC-900017', 'full_name' => 'Rosario Espiritu', 'sex' => 'F', 'birthdate' => '1954-05-22', 'contact_number' => '09331234517', 'email' => 'demo.rosario@example.com'],
            ['patient_number' => 'AC-900018', 'full_name' => 'Kevin Alcantara', 'sex' => 'M', 'birthdate' => '1991-11-14', 'contact_number' => '09341234518', 'email' => null],
            ['patient_number' => 'AC-900019', 'full_name' => 'Diana Soriano', 'sex' => 'F', 'birthdate' => '1986-01-07', 'contact_number' => '09351234519', 'email' => 'demo.diana@example.com'],
            ['patient_number' => 'AC-900020', 'full_name' => 'Alfredo Pangilinan', 'sex' => 'M', 'birthdate' => '1948-09-16', 'contact_number' => '09361234520', 'email' => null],
            ['patient_number' => 'AC-900021', 'full_name' => 'Michelle Yu', 'sex' => 'F', 'birthdate' => '1994-06-30', 'contact_number' => '09371234521', 'email' => 'demo.michelle@example.com'],
            ['patient_number' => 'AC-900022', 'full_name' => 'Nathaniel Ramos', 'sex' => 'M', 'birthdate' => '1980-03-25', 'contact_number' => '09381234522', 'email' => null],
            ['patient_number' => 'AC-900023', 'full_name' => 'Camille Domingo', 'sex' => 'F', 'birthdate' => '2000-12-01', 'contact_number' => '09391234523', 'email' => 'demo.camille@example.com'],
            ['patient_number' => 'AC-900024', 'full_name' => 'Victor Herrera', 'sex' => 'M', 'birthdate' => '1970-08-18', 'contact_number' => '09401234524', 'email' => null],
            ['patient_number' => 'AC-900025', 'full_name' => 'Isabel Marquez', 'sex' => 'F', 'birthdate' => '1989-10-05', 'contact_number' => '09411234525', 'email' => 'demo.isabel@example.com'],
        ];

        return array_map(function (array $row) {
            $row['birthdate'] = Carbon::parse($row['birthdate']);
            $row['notes'] = 'AHOP demo/test patient — safe to delete.';

            return $row;
        }, $rows);
    }

    protected function demoPhysicianId(): ?int
    {
        return User::query()->where('username', 'physician')->value('id');
    }

    /**
     * @param  list<int>  $companyIds
     */
    protected function companyIdForIndex(array $companyIds, ?int $defaultCompanyId, int $index): ?int
    {
        if ($companyIds !== []) {
            return $companyIds[$index % count($companyIds)];
        }

        return $defaultCompanyId;
    }
}
