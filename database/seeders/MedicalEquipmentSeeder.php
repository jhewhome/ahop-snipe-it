<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Company;
use App\Models\CompanyableScope;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * AgilityCare AHOP — Phase 4 clinical medical equipment taxonomy.
 * Safe to re-run: uses firstOrCreate / skips existing records.
 */
class MedicalEquipmentSeeder extends Seeder
{
    public function run(bool $withDemoMedicalAssets = false, bool $withDemoItAssets = false): void
    {
        $admin = User::where('permissions->superuser', '1')->first()
            ?? User::where('activated', 1)->first();

        if (! $admin) {
            $this->command?->error('No admin user found. Create a superuser first.');

            return;
        }

        $adminId = $admin->id;

        $categories = $this->seedCategories($adminId);
        $locations = $this->seedLocations($adminId);
        $statuses = $this->seedStatusLabels($adminId);
        $manufacturer = $this->seedManufacturer($adminId);
        $itManufacturer = $this->seedItManufacturer($adminId);
        $models = $this->seedAssetModels($adminId, $categories, $manufacturer, $itManufacturer);

        if ($withDemoMedicalAssets || $withDemoItAssets) {
            $companySeeder = new AhopCompanySeeder;
            $companySeeder->setCommand($this->command);
            $companySeeder->run(backfillPatients: false);
        }

        if ($withDemoMedicalAssets) {
            $this->seedDemoMedicalAssets($adminId, $models, $locations, $statuses);
        }

        if ($withDemoItAssets) {
            $this->seedDemoItAssets($adminId, $models, $locations, $statuses);
        }

        if ($withDemoMedicalAssets || $withDemoItAssets) {
            $this->backfillDemoAssetCompanies();
        }

        $this->command?->info('Medical equipment taxonomy seeded.');
    }

    /**
     * @return array<string, Category>
     */
    protected function seedCategories(int $adminId): array
    {
        $names = [
            'Diagnostic Imaging',
            'Patient Monitoring',
            'Life Support & Emergency',
            'Laboratory Equipment',
            'Surgical & Procedure',
            'Patient Care & Mobility',
            'IT-Medical Systems',
            'Information Technology',
        ];

        $categories = [];
        foreach ($names as $name) {
            $categories[$name] = Category::firstOrCreate(
                ['name' => $name, 'category_type' => 'asset'],
                [
                    'created_by' => $adminId,
                    'require_acceptance' => 0,
                    'use_default_eula' => 1,
                ]
            );
        }

        return $categories;
    }

    /**
     * @return array<string, Location>
     */
    protected function seedLocations(int $adminId): array
    {
        $facility = Location::firstOrCreate(
            ['name' => 'AgilityCare Main Facility'],
            ['created_by' => $adminId, 'city' => 'Manila', 'country' => 'PH']
        );

        $departments = [
            'Emergency Department',
            'OPD Clinic',
            'Intensive Care Unit',
            'Operating Room',
            'Radiology',
            'Clinical Laboratory',
            'Pharmacy',
            'Ward A',
            'Biomedical Engineering',
            'Equipment Storage',
            'IT Office / Server Room',
        ];

        $locations = ['facility' => $facility];
        foreach ($departments as $name) {
            $locations[$name] = Location::firstOrCreate(
                ['name' => $name],
                [
                    'parent_id' => $facility->id,
                    'created_by' => $adminId,
                    'city' => 'Manila',
                    'country' => 'PH',
                ]
            );
        }

        return $locations;
    }

    /**
     * @return array<string, Statuslabel>
     */
    protected function seedStatusLabels(int $adminId): array
    {
        $definitions = [
            'Available' => [
                'deployable' => 1,
                'pending' => 0,
                'archived' => 0,
                'show_in_nav' => 1,
                'color' => '#2e7d32',
                'notes' => 'Equipment ready for clinical use.',
            ],
            'In Use' => [
                'deployable' => 1,
                'pending' => 0,
                'archived' => 0,
                'show_in_nav' => 1,
                'color' => '#1565c0',
                'notes' => 'Assigned to a department or staff member.',
            ],
            'Out for Calibration' => [
                'deployable' => 0,
                'pending' => 1,
                'archived' => 0,
                'show_in_nav' => 1,
                'color' => '#e65100',
                'notes' => 'Pending return from calibration vendor.',
            ],
            'Out for Repair' => [
                'deployable' => 0,
                'pending' => 1,
                'archived' => 0,
                'show_in_nav' => 1,
                'color' => '#f57c00',
                'notes' => 'Biomedical engineering or vendor repair.',
            ],
            'Condemned' => [
                'deployable' => 0,
                'pending' => 0,
                'archived' => 1,
                'show_in_nav' => 0,
                'color' => '#c62828',
                'notes' => 'Not safe for clinical use.',
            ],
            'Retired' => [
                'deployable' => 0,
                'pending' => 0,
                'archived' => 1,
                'show_in_nav' => 0,
                'color' => '#757575',
                'notes' => 'Disposed or removed from inventory.',
            ],
        ];

        $statuses = [];
        foreach ($definitions as $name => $attrs) {
            $nav = $attrs['show_in_nav'];
            $color = $attrs['color'];
            unset($attrs['show_in_nav'], $attrs['color']);

            $status = Statuslabel::firstOrCreate(
                ['name' => $name],
                array_merge($attrs, ['created_by' => $adminId])
            );

            $status->show_in_nav = $nav;
            $status->color = $color;
            $status->save();

            $statuses[$name] = $status;
        }

        return $statuses;
    }

    protected function seedManufacturer(int $adminId): Manufacturer
    {
        return Manufacturer::firstOrCreate(
            ['name' => 'AgilityCare Biomedical'],
            ['created_by' => $adminId, 'url' => 'https://agilitycare.example']
        );
    }

    protected function seedItManufacturer(int $adminId): Manufacturer
    {
        return Manufacturer::firstOrCreate(
            ['name' => 'AgilityCare IT Solutions'],
            ['created_by' => $adminId, 'url' => 'https://agilitycare.example/it']
        );
    }

    /**
     * @param  array<string, Category>  $categories
     * @return array<string, AssetModel>
     */
    protected function seedAssetModels(
        int $adminId,
        array $categories,
        Manufacturer $manufacturer,
        Manufacturer $itManufacturer,
    ): array {
        $medicalDefinitions = [
            'Patient Monitor (IntelliVue)' => [
                'category' => 'Patient Monitoring',
                'model_number' => 'MX450',
                'eol' => 84,
            ],
            'Portable Ventilator' => [
                'category' => 'Life Support & Emergency',
                'model_number' => 'V60',
                'eol' => 96,
            ],
            'Automated External Defibrillator (AED)' => [
                'category' => 'Life Support & Emergency',
                'model_number' => 'HeartStart',
                'eol' => 60,
            ],
            'Infusion Pump' => [
                'category' => 'Patient Monitoring',
                'model_number' => 'Alaris-8100',
                'eol' => 72,
            ],
            'Digital X-Ray System' => [
                'category' => 'Diagnostic Imaging',
                'model_number' => 'DXR-200',
                'eol' => 120,
            ],
            'Portable Ultrasound System' => [
                'category' => 'Diagnostic Imaging',
                'model_number' => 'US-500',
                'eol' => 96,
            ],
            '12-Lead ECG Machine' => [
                'category' => 'Diagnostic Imaging',
                'model_number' => 'ECG-1200',
                'eol' => 84,
            ],
            'Blood Gas Analyzer' => [
                'category' => 'Laboratory Equipment',
                'model_number' => 'BGA-300',
                'eol' => 72,
            ],
            'LED Surgical Light' => [
                'category' => 'Surgical & Procedure',
                'model_number' => 'SL-LED-4',
                'eol' => 120,
            ],
            'Anesthesia Workstation' => [
                'category' => 'Surgical & Procedure',
                'model_number' => 'AW-7000',
                'eol' => 120,
            ],
            'Electric Hospital Bed' => [
                'category' => 'Patient Care & Mobility',
                'model_number' => 'HB-E500',
                'eol' => 96,
            ],
            'Transport Wheelchair' => [
                'category' => 'Patient Care & Mobility',
                'model_number' => 'WC-TR-01',
                'eol' => 60,
            ],
            'Portable Pulse Oximeter' => [
                'category' => 'Patient Monitoring',
                'model_number' => 'POX-100',
                'eol' => 48,
            ],
            'Laboratory Centrifuge' => [
                'category' => 'Laboratory Equipment',
                'model_number' => 'LC-4000',
                'eol' => 96,
            ],
            'Clinical Microscope' => [
                'category' => 'Laboratory Equipment',
                'model_number' => 'CM-200',
                'eol' => 120,
            ],
            'Steam Autoclave' => [
                'category' => 'Laboratory Equipment',
                'model_number' => 'AC-50L',
                'eol' => 120,
            ],
            'Patient Lift (Hydraulic)' => [
                'category' => 'Patient Care & Mobility',
                'model_number' => 'PL-H200',
                'eol' => 84,
            ],
        ];

        $itDefinitions = [
            'Clinic Workstation' => [
                'category' => 'Information Technology',
                'model_number' => 'WS-OPD-01',
                'eol' => 48,
            ],
            'Nurse Station PC' => [
                'category' => 'Information Technology',
                'model_number' => 'PC-NS-01',
                'eol' => 48,
            ],
            'Clinical Tablet' => [
                'category' => 'IT-Medical Systems',
                'model_number' => 'TAB-CL-10',
                'eol' => 36,
            ],
            'Barcode Label Printer' => [
                'category' => 'Information Technology',
                'model_number' => 'BP-ZD420',
                'eol' => 60,
            ],
            'Network Switch (24-port)' => [
                'category' => 'Information Technology',
                'model_number' => 'SW-24G',
                'eol' => 84,
            ],
            'Server Rack Unit' => [
                'category' => 'Information Technology',
                'model_number' => 'SR-2U',
                'eol' => 96,
            ],
            'VoIP Desk Phone' => [
                'category' => 'Information Technology',
                'model_number' => 'IP-PHONE-01',
                'eol' => 60,
            ],
            'UPS Battery Backup' => [
                'category' => 'Information Technology',
                'model_number' => 'UPS-1500',
                'eol' => 48,
            ],
            'Wi-Fi Access Point' => [
                'category' => 'Information Technology',
                'model_number' => 'AP-WIFI-6',
                'eol' => 60,
            ],
            'Document Scanner' => [
                'category' => 'Information Technology',
                'model_number' => 'SCN-FI-7160',
                'eol' => 72,
            ],
            'Laser Printer (A4)' => [
                'category' => 'Information Technology',
                'model_number' => 'LP-M404',
                'eol' => 60,
            ],
            'Network Firewall Appliance' => [
                'category' => 'Information Technology',
                'model_number' => 'FW-UTM-100',
                'eol' => 84,
            ],
        ];

        $models = [];

        foreach ($medicalDefinitions as $name => $attrs) {
            $category = $categories[$attrs['category']] ?? reset($categories);

            $models[$name] = AssetModel::firstOrCreate(
                ['name' => $name],
                [
                    'model_number' => $attrs['model_number'],
                    'category_id' => $category->id,
                    'manufacturer_id' => $manufacturer->id,
                    'eol' => $attrs['eol'],
                    'created_by' => $adminId,
                    'notes' => 'AHOP medical equipment model',
                ]
            );
        }

        foreach ($itDefinitions as $name => $attrs) {
            $category = $categories[$attrs['category']] ?? reset($categories);

            $models[$name] = AssetModel::firstOrCreate(
                ['name' => $name],
                [
                    'model_number' => $attrs['model_number'],
                    'category_id' => $category->id,
                    'manufacturer_id' => $itManufacturer->id,
                    'eol' => $attrs['eol'],
                    'created_by' => $adminId,
                    'notes' => 'AHOP IT asset model',
                ]
            );
        }

        return $models;
    }

    protected function seedDemoMedicalAssets(int $adminId, array $models, array $locations, array $statuses): void
    {
        $assignments = [
            ['tag' => 'AC-EQ-000001', 'model' => 'Patient Monitor (IntelliVue)', 'location' => 'Intensive Care Unit', 'status' => 'In Use', 'serial' => 'PM-2024-001'],
            ['tag' => 'AC-EQ-000002', 'model' => 'Portable Ventilator', 'location' => 'Emergency Department', 'status' => 'Available', 'serial' => 'VENT-2024-002'],
            ['tag' => 'AC-EQ-000003', 'model' => 'Automated External Defibrillator (AED)', 'location' => 'OPD Clinic', 'status' => 'In Use', 'serial' => 'AED-2024-003'],
            ['tag' => 'AC-EQ-000004', 'model' => 'Infusion Pump', 'location' => 'Ward A', 'status' => 'Available', 'serial' => 'IP-2024-004'],
            ['tag' => 'AC-EQ-000005', 'model' => 'Digital X-Ray System', 'location' => 'Radiology', 'status' => 'Out for Calibration', 'serial' => 'XR-2024-005'],
            ['tag' => 'AC-EQ-000006', 'model' => 'Portable Ultrasound System', 'location' => 'Radiology', 'status' => 'In Use', 'serial' => 'US-2024-006'],
            ['tag' => 'AC-EQ-000007', 'model' => '12-Lead ECG Machine', 'location' => 'OPD Clinic', 'status' => 'Available', 'serial' => 'ECG-2024-007'],
            ['tag' => 'AC-EQ-000008', 'model' => 'Blood Gas Analyzer', 'location' => 'Clinical Laboratory', 'status' => 'In Use', 'serial' => 'BGA-2024-008'],
            ['tag' => 'AC-EQ-000009', 'model' => 'LED Surgical Light', 'location' => 'Operating Room', 'status' => 'Available', 'serial' => 'SL-2024-009'],
            ['tag' => 'AC-EQ-000010', 'model' => 'Anesthesia Workstation', 'location' => 'Operating Room', 'status' => 'In Use', 'serial' => 'AW-2024-010'],
            ['tag' => 'AC-EQ-000011', 'model' => 'Electric Hospital Bed', 'location' => 'Ward A', 'status' => 'In Use', 'serial' => 'HB-2024-011'],
            ['tag' => 'AC-EQ-000012', 'model' => 'Transport Wheelchair', 'location' => 'OPD Clinic', 'status' => 'Available', 'serial' => 'WC-2024-012'],
            ['tag' => 'AC-EQ-000013', 'model' => 'Portable Pulse Oximeter', 'location' => 'Emergency Department', 'status' => 'In Use', 'serial' => 'POX-2024-013'],
            ['tag' => 'AC-EQ-000014', 'model' => 'Laboratory Centrifuge', 'location' => 'Clinical Laboratory', 'status' => 'Available', 'serial' => 'LC-2024-014'],
            ['tag' => 'AC-EQ-000015', 'model' => 'Clinical Microscope', 'location' => 'Clinical Laboratory', 'status' => 'In Use', 'serial' => 'CM-2024-015'],
            ['tag' => 'AC-EQ-000016', 'model' => 'Steam Autoclave', 'location' => 'Clinical Laboratory', 'status' => 'Out for Repair', 'serial' => 'AC-2024-016'],
            ['tag' => 'AC-EQ-000017', 'model' => 'Patient Lift (Hydraulic)', 'location' => 'Ward A', 'status' => 'Available', 'serial' => 'PL-2024-017'],
        ];

        $created = $this->seedAssetAssignments($adminId, $assignments, $models, $locations, $statuses, 'AHOP demo medical equipment');
        $this->command?->info("Demo medical equipment: {$created} new asset(s).");
    }

    protected function seedDemoItAssets(int $adminId, array $models, array $locations, array $statuses): void
    {
        $assignments = [
            ['tag' => 'AC-IT-000001', 'model' => 'Clinic Workstation', 'location' => 'OPD Clinic', 'status' => 'In Use', 'serial' => 'WS-2024-001'],
            ['tag' => 'AC-IT-000002', 'model' => 'Nurse Station PC', 'location' => 'Ward A', 'status' => 'In Use', 'serial' => 'PC-2024-002'],
            ['tag' => 'AC-IT-000003', 'model' => 'Clinical Tablet', 'location' => 'Emergency Department', 'status' => 'Available', 'serial' => 'TAB-2024-003'],
            ['tag' => 'AC-IT-000004', 'model' => 'Barcode Label Printer', 'location' => 'Pharmacy', 'status' => 'In Use', 'serial' => 'BP-2024-004'],
            ['tag' => 'AC-IT-000005', 'model' => 'Network Switch (24-port)', 'location' => 'IT Office / Server Room', 'status' => 'In Use', 'serial' => 'SW-2024-005'],
            ['tag' => 'AC-IT-000006', 'model' => 'Server Rack Unit', 'location' => 'IT Office / Server Room', 'status' => 'In Use', 'serial' => 'SR-2024-006'],
            ['tag' => 'AC-IT-000007', 'model' => 'VoIP Desk Phone', 'location' => 'OPD Clinic', 'status' => 'Available', 'serial' => 'VOIP-2024-007'],
            ['tag' => 'AC-IT-000008', 'model' => 'UPS Battery Backup', 'location' => 'IT Office / Server Room', 'status' => 'In Use', 'serial' => 'UPS-2024-008'],
            ['tag' => 'AC-IT-000009', 'model' => 'Wi-Fi Access Point', 'location' => 'Biomedical Engineering', 'status' => 'In Use', 'serial' => 'AP-2024-009'],
            ['tag' => 'AC-IT-000010', 'model' => 'Document Scanner', 'location' => 'OPD Clinic', 'status' => 'Available', 'serial' => 'SCN-2024-010'],
            ['tag' => 'AC-IT-000011', 'model' => 'Laser Printer (A4)', 'location' => 'Ward A', 'status' => 'In Use', 'serial' => 'LP-2024-011'],
            ['tag' => 'AC-IT-000012', 'model' => 'Network Firewall Appliance', 'location' => 'IT Office / Server Room', 'status' => 'In Use', 'serial' => 'FW-2024-012'],
        ];

        $created = $this->seedAssetAssignments($adminId, $assignments, $models, $locations, $statuses, 'AHOP demo IT asset');
        $this->command?->info("Demo IT assets: {$created} new asset(s).");
    }

    /**
     * @param  list<array{tag: string, model: string, location: string, status: string, serial: string}>  $assignments
     * @param  array<string, AssetModel>  $models
     * @param  array<string, Location>  $locations
     * @param  array<string, Statuslabel>  $statuses
     */
    protected function seedAssetAssignments(
        int $adminId,
        array $assignments,
        array $models,
        array $locations,
        array $statuses,
        string $notes,
    ): int {
        $created = 0;
        $companyId = $this->resolveDefaultCompanyId();

        foreach ($assignments as $row) {
            if (Asset::withoutGlobalScope(CompanyableScope::class)
                ->where('asset_tag', $row['tag'])
                ->exists()) {
                continue;
            }

            $model = $models[$row['model']] ?? null;
            $location = $locations[$row['location']] ?? $locations['facility'];
            $status = $statuses[$row['status']] ?? $statuses['Available'];

            if (! $model || ! $status) {
                continue;
            }

            Asset::create([
                'asset_tag' => $row['tag'],
                'name' => $row['model'],
                'model_id' => $model->id,
                'status_id' => $status->id,
                'rtd_location_id' => $location->id,
                'location_id' => $location->id,
                'company_id' => $companyId,
                'serial' => $row['serial'],
                'purchase_date' => now()->subMonths(6)->format('Y-m-d'),
                'purchase_cost' => 0,
                'created_by' => $adminId,
                'notes' => $notes,
            ]);

            $created++;
        }

        return $created;
    }

    protected function resolveDefaultCompanyId(): ?int
    {
        $name = config('ahop.default_clinic_company_name', config('ahop.default_site_name', 'AgilityCare Main Clinic'));
        $id = Company::query()->where('name', $name)->value('id');

        if ($id) {
            return (int) $id;
        }

        $fallback = Company::query()->value('id');

        return $fallback ? (int) $fallback : null;
    }

    /**
     * When Full Multiple Company Support is enabled, assets without company_id are hidden from clinic staff.
     */
    public function backfillDemoAssetCompanies(): void
    {
        $companyId = $this->resolveDefaultCompanyId();

        if (! $companyId) {
            $this->command?->warn('No company found — demo assets may not appear under Full Multiple Company Support.');

            return;
        }

        $updated = Asset::withoutGlobalScope(CompanyableScope::class)
            ->where(function ($query) {
                $query->where('asset_tag', 'like', 'AC-EQ-%')
                    ->orWhere('asset_tag', 'like', 'AC-IT-%');
            })
            ->where(function ($query) use ($companyId) {
                $query->whereNull('company_id')
                    ->orWhere('company_id', '!=', $companyId);
            })
            ->update(['company_id' => $companyId]);

        if ($updated > 0) {
            $this->command?->info("Demo assets: {$updated} record(s) linked to clinic company for list visibility.");
        }
    }
}
