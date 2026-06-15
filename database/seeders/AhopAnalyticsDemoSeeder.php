<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Maintenance;
use App\Models\OpdVisit;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Demo data tailored for Clinical Analytics (risk scores, lab trends, equipment priority).
 * Safe to re-run: skips when analytics marker results already exist.
 */
class AhopAnalyticsDemoSeeder extends Seeder
{
    protected const MARKER = 'ANALYTICS-DEMO';

    public function run(): void
    {
        if (! Schema::hasTable('patients') || ! Schema::hasTable('lab_results')) {
            $this->command?->error('Clinical tables not found. Run clinical migrations first.');

            return;
        }

        $this->command?->info('Seeding Clinical Analytics demo data...');

        if (LabResult::where('test_code', self::MARKER)->exists()) {
            $this->command?->warn('Analytics lab demo already present — skipping patient/lab inserts.');
        } else {
            $this->ensureBasePatients();
            $this->seedHighRiskPatient();
            $this->seedLabTrends();
        }

        $this->seedEquipmentMaintenance();

        $this->command?->info('Clinical Analytics demo data ready.');
        $this->command?->line('  Open → Clinical Services → Clinical Analytics');
        $this->command?->line('  Tabs: Patient Risk | Lab Trends | Equipment Maintenance');
    }

    protected function ensureBasePatients(): void
    {
        $seeder = new AhopClinicalDemoSeeder;
        $seeder->setCommand($this->command);
        $seeder->run(10);
    }

    protected function seedHighRiskPatient(): void
    {
        $patient = Patient::where('patient_number', 'AC-900004')->first()
            ?? Patient::query()->orderBy('id')->first();

        if (! $patient) {
            return;
        }

        $physicianId = $this->demoPhysicianId();

        foreach ([85, 60, 30, 10] as $daysAgo) {
            OpdVisit::create([
                'visit_number' => OpdVisit::generateNextVisitNumber(),
                'patient_id' => $patient->id,
                'physician_id' => $physicianId,
                'visit_date' => now()->subDays($daysAgo),
                'visit_type' => OpdVisit::TYPE_FOLLOW_UP,
                'status' => OpdVisit::STATUS_COMPLETED,
                'chief_complaint' => 'Chest pain and shortness of breath on exertion',
                'blood_pressure' => '158/96',
                'pulse_rate' => 108,
                'temperature' => 38.4,
                'assessment' => 'Hypertension with acute symptoms — analytics demo.',
                'diagnosis' => 'Hypertension, diabetes mellitus type 2 — cardiac risk',
                'company_id' => $patient->company_id,
            ]);
        }
    }

    protected function seedLabTrends(): void
    {
        $scenarios = [
            [
                'patient_number' => 'AC-900002',
                'test_name' => 'Creatinine',
                'test_code' => self::MARKER,
                'unit' => 'mg/dL',
                'series' => [
                    ['months' => 6, 'value' => '0.9', 'flag' => 'normal'],
                    ['months' => 3, 'value' => '1.2', 'flag' => 'high'],
                    ['months' => 0, 'value' => '1.8', 'flag' => 'critical'],
                ],
            ],
            [
                'patient_number' => 'AC-900001',
                'test_name' => 'Glucose Fasting',
                'test_code' => self::MARKER.'-GLU',
                'unit' => 'mg/dL',
                'series' => [
                    ['months' => 5, 'value' => '180', 'flag' => 'high'],
                    ['months' => 2, 'value' => '145', 'flag' => 'high'],
                    ['months' => 0, 'value' => '118', 'flag' => 'normal'],
                ],
            ],
            [
                'patient_number' => 'AC-900008',
                'test_name' => 'Hemoglobin',
                'test_code' => self::MARKER.'-HGB',
                'unit' => 'g/dL',
                'series' => [
                    ['months' => 4, 'value' => '13.5', 'flag' => 'normal'],
                    ['months' => 2, 'value' => '13.4', 'flag' => 'normal'],
                    ['months' => 0, 'value' => '13.6', 'flag' => 'normal'],
                ],
            ],
        ];

        foreach ($scenarios as $scenario) {
            $patient = Patient::where('patient_number', $scenario['patient_number'])->first();
            if (! $patient) {
                continue;
            }

            $order = LabOrder::create([
                'order_number' => LabOrder::generateNextOrderNumber(),
                'patient_id' => $patient->id,
                'test_panel' => 'Analytics Demo Panel',
                'status' => LabOrder::STATUS_COMPLETED,
                'priority' => 'routine',
                'ordered_at' => now()->subMonths(6),
                'company_id' => $patient->company_id,
            ]);

            foreach ($scenario['series'] as $point) {
                LabResult::create([
                    'lab_order_id' => $order->id,
                    'test_code' => $scenario['test_code'],
                    'test_name' => $scenario['test_name'],
                    'result_value' => $point['value'],
                    'unit' => $scenario['unit'],
                    'reference_range' => 'Demo',
                    'flag' => $point['flag'],
                    'result_at' => now()->subMonths($point['months']),
                    'notes' => 'AHOP Clinical Analytics demo result',
                ]);
            }
        }
    }

    protected function seedEquipmentMaintenance(): void
    {
        if (! Schema::hasTable('maintenances')) {
            return;
        }

        $adminId = User::query()->where('permissions->superuser', '1')->value('id')
            ?? User::query()->value('id');

        $assets = Asset::query()
            ->whereIn('asset_tag', ['AC-EQ-000001', 'AC-EQ-000005'])
            ->get()
            ->keyBy('asset_tag');

        $monitor = $assets->get('AC-EQ-000001');
        if ($monitor && ! Maintenance::where('asset_id', $monitor->id)->where('name', 'Analytics demo PM')->exists()) {
            Maintenance::create([
                'asset_id' => $monitor->id,
                'asset_maintenance_type' => 'Preventive Maintenance',
                'name' => 'Analytics demo PM',
                'start_date' => now()->subMonths(14)->format('Y-m-d'),
                'completion_date' => now()->subMonths(14)->format('Y-m-d'),
                'notes' => 'AHOP analytics demo — overdue preventive maintenance',
                'created_by' => $adminId,
            ]);
        }

        $xray = $assets->get('AC-EQ-000005');
        if ($xray && ! Maintenance::where('asset_id', $xray->id)->where('name', 'Analytics demo calibration')->exists()) {
            Maintenance::create([
                'asset_id' => $xray->id,
                'asset_maintenance_type' => 'Calibration',
                'name' => 'Analytics demo calibration',
                'start_date' => now()->subMonths(13)->format('Y-m-d'),
                'completion_date' => now()->subMonths(13)->format('Y-m-d'),
                'notes' => 'AHOP analytics demo — calibration overdue',
                'created_by' => $adminId,
            ]);
        }
    }

    protected function demoPhysicianId(): ?int
    {
        return User::query()->where('username', 'physician')->value('id');
    }
}
