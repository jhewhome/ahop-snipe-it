<?php

namespace App\Console\Commands;

use Database\Seeders\AhopPhysiciansSeeder;
use Illuminate\Console\Command;

class SeedAhopPhysicians extends Command
{
    protected $signature = 'ahop:seed-physicians
                            {--force : Reset passwords and re-assign AHOP Clinic Staff on existing demo physicians}
                            {--password= : Password for demo physician accounts (min 8 characters)}';

    protected $description = 'Seed sample attending physicians for Attending Physician dropdowns (AHOP Clinic Staff)';

    public function handle(): int
    {
        $password = $this->option('password') ?: AhopPhysiciansSeeder::DEFAULT_PASSWORD;

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $this->info('AHOP attending physician roster');
        $this->line('Safe to re-run — skips existing usernames unless --force is set.');
        $this->newLine();

        $seeder = new AhopPhysiciansSeeder;
        $seeder->setCommand($this);
        $seeder->run((bool) $this->option('force'), $password);

        $this->newLine();
        $this->table(
            ['Username', 'Name', 'Role group', 'Password (if created/updated)'],
            collect(AhopPhysiciansSeeder::accounts())
                ->map(fn (array $account) => [
                    $account['username'],
                    trim($account['first_name'].' '.$account['last_name']),
                    'AHOP Clinic Staff',
                    $this->option('force') ? $password : '(unchanged if skipped)',
                ])
                ->all()
        );

        return self::SUCCESS;
    }
}
