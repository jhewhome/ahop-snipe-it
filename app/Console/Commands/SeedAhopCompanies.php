<?php

namespace App\Console\Commands;

use Database\Seeders\AhopCompanySeeder;
use Illuminate\Console\Command;

class SeedAhopCompanies extends Command
{
    protected $signature = 'ahop:seed-companies
                            {--no-backfill : Do not assign company_id to existing patients with null company}';

    protected $description = 'Seed clinic company records for the patient Company field and backfill existing patients';

    public function handle(): int
    {
        $this->info('AHOP clinic companies');
        $this->line('Safe to re-run — skips companies that already exist by name.');
        $this->newLine();

        $seeder = new AhopCompanySeeder;
        $seeder->setCommand($this);
        $result = $seeder->run(backfillPatients: ! $this->option('no-backfill'));

        if (empty($result['ids'])) {
            $this->error('No companies available. Check that the companies table exists.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line('Companies are available in Admin → Companies and on the patient create/edit form.');
        $this->line('Run: php artisan ahop:seed-demo — to create demo patients with company assigned.');

        return self::SUCCESS;
    }
}
