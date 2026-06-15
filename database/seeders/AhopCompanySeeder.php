<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BillingInvoice;
use App\Models\Company;
use App\Models\LabOrder;
use App\Models\OpdVisit;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Clinic/site companies for AHOP patient and clinical records.
 * Safe to re-run: creates missing companies by name; optionally backfills null company_id.
 */
class AhopCompanySeeder extends Seeder
{
    /**
     * @return array{default_id: int|null, ids: list<int>, created: int, backfilled: int}
     */
    public function run(bool $backfillPatients = true): array
    {
        if (! Schema::hasTable('companies')) {
            $this->command?->error('Companies table not found.');

            return ['default_id' => null, 'ids' => [], 'created' => 0, 'backfilled' => 0];
        }

        $created = 0;
        $ids = [];

        foreach ($this->clinicCompanies() as $row) {
            $company = Company::query()->where('name', $row['name'])->first();

            if (! $company) {
                $company = Company::create($row);
                $created++;
            }

            $ids[] = (int) $company->id;
        }

        $defaultId = $ids[0] ?? Company::query()->value('id');
        $backfilled = 0;

        if ($backfillPatients && $defaultId && Schema::hasTable('patients')) {
            $backfilled = Patient::query()->whereNull('company_id')->update(['company_id' => $defaultId]);
            $this->syncClinicalCompanyFromPatients($defaultId);
        }

        $this->command?->info("AHOP companies: {$created} created, ".count($ids).' available, '.$backfilled.' patient(s) backfilled.');

        return [
            'default_id' => $defaultId ? (int) $defaultId : null,
            'ids' => $ids,
            'created' => $created,
            'backfilled' => (int) $backfilled,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function clinicCompanies(): array
    {
        $primary = config('ahop.default_clinic_company_name', config('ahop.default_site_name', 'AgilityCare Main Clinic'));

        return [
            [
                'name' => $primary,
                'phone' => '+63 2 8123 4567',
                'email' => 'main@agilitycare.demo',
                'notes' => 'AHOP demo — primary clinic / main site.',
                'tag_color' => '#0d6e7a',
                'created_by' => 1,
            ],
            [
                'name' => 'AgilityCare Quezon City Branch',
                'phone' => '+63 2 8987 6543',
                'email' => 'qc@agilitycare.demo',
                'notes' => 'AHOP demo — outpatient branch.',
                'tag_color' => '#1496a6',
                'created_by' => 1,
            ],
            [
                'name' => 'AgilityCare Makati Outpatient Center',
                'phone' => '+63 2 8555 1212',
                'email' => 'makati@agilitycare.demo',
                'notes' => 'AHOP demo — satellite clinic.',
                'tag_color' => '#2eb8a6',
                'created_by' => 1,
            ],
        ];
    }

    protected function syncClinicalCompanyFromPatients(int $defaultId): void
    {
        $tables = [
            OpdVisit::class => 'opd_visits',
            Appointment::class => 'appointments',
            LabOrder::class => 'lab_orders',
            BillingInvoice::class => 'billing_invoices',
        ];

        foreach ($tables as $model => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $model::query()
                ->whereNull('company_id')
                ->whereNotNull('patient_id')
                ->with('patient:id,company_id')
                ->chunkById(100, function ($rows) use ($defaultId) {
                    foreach ($rows as $row) {
                        $companyId = $row->patient?->company_id ?? $defaultId;
                        if ($companyId && $row->company_id !== $companyId) {
                            $row->company_id = $companyId;
                            $row->saveQuietly();
                        }
                    }
                });
        }
    }
}
