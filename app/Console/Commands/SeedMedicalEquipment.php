<?php

namespace App\Console\Commands;

use Database\Seeders\MedicalEquipmentSeeder;
use Illuminate\Console\Command;

class SeedMedicalEquipment extends Command
{
    protected $signature = 'ahop:seed-equipment
                            {--demo-assets : Create sample equipment records (AC-EQ-000001…)}';

    protected $description = 'Seed AHOP medical equipment categories, locations, status labels, and models (safe to re-run)';

    public function handle(): int
    {
        if (! config('ahop.clinical_sidebar_mode')) {
            $this->warn('AHOP_CLINICAL_SIDEBAR is not enabled in .env. Seeding clinical equipment taxonomy anyway.');
        }

        $seeder = new MedicalEquipmentSeeder();
        $seeder->setCommand($this);
        $seeder->run((bool) $this->option('demo-assets'));

        $this->info('Done. Review equipment under Medical Equipment → List All.');

        return self::SUCCESS;
    }
}
