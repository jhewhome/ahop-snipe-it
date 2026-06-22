<?php

namespace App\Console\Commands;

use Database\Seeders\MedicalEquipmentSeeder;
use Illuminate\Console\Command;

class SeedMedicalEquipment extends Command
{
    protected $signature = 'ahop:seed-equipment
                            {--demo-assets : Create sample medical equipment records (AC-EQ-…)}
                            {--demo-it-assets : Create sample IT asset records (AC-IT-…)}';

    protected $description = 'Seed AHOP medical equipment categories, locations, status labels, and models (safe to re-run)';

    public function handle(): int
    {
        if (! config('ahop.clinical_sidebar_mode')) {
            $this->warn('AHOP_CLINICAL_SIDEBAR is not enabled in .env. Seeding clinical equipment taxonomy anyway.');
        }

        $seeder = new MedicalEquipmentSeeder();
        $seeder->setCommand($this);
        $seeder->run(
            withDemoMedicalAssets: (bool) $this->option('demo-assets'),
            withDemoItAssets: (bool) $this->option('demo-it-assets'),
        );

        $this->info('Done. Review equipment under Medical Equipment → List All.');
        if ($this->option('demo-assets')) {
            $this->line('  Medical demo tags: AC-EQ-000001 … AC-EQ-000017');
        }
        if ($this->option('demo-it-assets')) {
            $this->line('  IT demo tags: AC-IT-000001 … AC-IT-000012');
        }

        return self::SUCCESS;
    }
}
