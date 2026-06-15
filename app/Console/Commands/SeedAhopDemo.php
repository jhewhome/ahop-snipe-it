<?php

namespace App\Console\Commands;

use Database\Seeders\AhopClinicalDemoSeeder;
use Illuminate\Console\Command;

class SeedAhopDemo extends Command
{
    protected $signature = 'ahop:seed-demo
                            {--patients=10 : Number of demo patients to create (max 10 predefined)}';

    protected $description = 'Seed demo patients and sample OPD/appointment/lab records for testing';

    public function handle(): int
    {
        $count = max(1, min(10, (int) $this->option('patients')));

        $this->info('AHOP clinical demo data');
        $this->newLine();

        $seeder = new AhopClinicalDemoSeeder;
        $seeder->setCommand($this);
        $seeder->run($count);

        $this->newLine();
        $this->line('Medical equipment (assets): php artisan ahop:seed-equipment --demo-assets');

        return self::SUCCESS;
    }
}
