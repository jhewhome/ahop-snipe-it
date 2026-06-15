<?php

namespace App\Console\Commands;

use Database\Seeders\AhopAnalyticsDemoSeeder;
use Illuminate\Console\Command;

class SeedAhopAnalytics extends Command
{
    protected $signature = 'ahop:seed-analytics';

    protected $description = 'Seed demo patients, lab trends, and equipment data for Clinical Analytics charts';

    public function handle(): int
    {
        $this->info('AHOP Clinical Analytics demo data');
        $this->newLine();

        $seeder = new AhopAnalyticsDemoSeeder;
        $seeder->setCommand($this);
        $seeder->run();

        $this->newLine();
        $this->line('If equipment tab is empty, also run: php artisan ahop:seed-equipment --demo-assets');

        return self::SUCCESS;
    }
}
