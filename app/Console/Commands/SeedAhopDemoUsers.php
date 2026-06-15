<?php

namespace App\Console\Commands;

use Database\Seeders\AhopDemoUsersSeeder;
use Illuminate\Console\Command;

class SeedAhopDemoUsers extends Command
{
    protected $signature = 'ahop:seed-demo-users
                            {--force : Reset passwords and re-assign AHOP groups on existing demo accounts}
                            {--password= : Password for all demo accounts (min 8 characters)}';

    protected $description = 'Seed demo staff logins with one AHOP role group each (reception, physician, lab, biomedical, clinic admin)';

    public function handle(): int
    {
        $password = $this->option('password') ?: AhopDemoUsersSeeder::DEFAULT_PASSWORD;

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        $this->info('AHOP demo staff accounts');
        $this->line('Safe to re-run — skips existing usernames unless --force is set.');
        $this->newLine();

        $seeder = new AhopDemoUsersSeeder;
        $seeder->setCommand($this);
        $seeder->run((bool) $this->option('force'), $password);

        $this->newLine();
        $this->table(
            ['Username', 'Role group', 'Password'],
            [
                ['reception', 'AHOP Reception', $password],
                ['physician', 'AHOP Clinic Staff', $password],
                ['labtech', 'AHOP Laboratory', $password],
                ['biomedical', 'AHOP Biomedical', $password],
                ['clinicadmin', 'AHOP Clinic Administrator', $password],
            ]
        );

        return self::SUCCESS;
    }
}
