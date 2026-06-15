<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
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
    public function run(bool $withDemoAssets = false): void
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
        $models = $this->seedAssetModels($adminId, $categories, $manufacturer);

        if ($withDemoAssets) {
            $this->seedDemoAssets($adminId, $models, $locations, $statuses);
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

    /**
     * @param  array<string, Category>  $categories
     * @return array<string, AssetModel>
     */
    protected function seedAssetModels(int $adminId, array $categories, Manufacturer $manufacturer): array
    {
        $definitions = [
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
        ];

        $models = [];
        foreach ($definitions as $name => $attrs) {
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

        return $models;
    }

    /**
     * @param  array<string, AssetModel>  $models
     * @param  array<string, Location>  $locations
     * @param  array<string, Statuslabel>  $statuses
     */
    protected function seedDemoAssets(int $adminId, array $models, array $locations, array $statuses): void
    {
        $assignments = [
            ['tag' => 'AC-EQ-000001', 'model' => 'Patient Monitor (IntelliVue)', 'location' => 'Intensive Care Unit', 'status' => 'In Use', 'serial' => 'PM-2024-001'],
            ['tag' => 'AC-EQ-000002', 'model' => 'Portable Ventilator', 'location' => 'Emergency Department', 'status' => 'Available', 'serial' => 'VENT-2024-002'],
            ['tag' => 'AC-EQ-000003', 'model' => 'Automated External Defibrillator (AED)', 'location' => 'OPD Clinic', 'status' => 'In Use', 'serial' => 'AED-2024-003'],
            ['tag' => 'AC-EQ-000004', 'model' => 'Infusion Pump', 'location' => 'Ward A', 'status' => 'Available', 'serial' => 'IP-2024-004'],
            ['tag' => 'AC-EQ-000005', 'model' => 'Digital X-Ray System', 'location' => 'Radiology', 'status' => 'Out for Calibration', 'serial' => 'XR-2024-005'],
        ];

        foreach ($assignments as $row) {
            if (Asset::where('asset_tag', $row['tag'])->exists()) {
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
                'serial' => $row['serial'],
                'purchase_date' => now()->subMonths(6)->format('Y-m-d'),
                'purchase_cost' => 0,
                'created_by' => $adminId,
                'notes' => 'AHOP demo medical equipment',
            ]);
        }
    }
}
