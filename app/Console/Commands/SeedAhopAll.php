<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedAhopAll extends Command
{
    protected $signature = 'ahop:seed-all
                            {--patients=25 : Demo patients to create (max 25 predefined)}
                            {--skip-roles : Skip AHOP role group setup}
                            {--skip-equipment : Skip medical equipment taxonomy/assets}
                            {--skip-clinical : Skip demo patients, OPD, appointments, lab, billing}
                            {--skip-users : Skip demo staff logins (reception, physician, etc.)}
                            {--no-demo-assets : Seed equipment categories/models only (no AC-EQ- assets)}
                            {--password= : Password for demo staff accounts (min 8 characters)}';

    protected $description = 'Populate AHOP: roles, demo users, medical equipment (Snipe assets), and clinic demo data';

    public function handle(): int
    {
        $this->info('AHOP full demo seed');
        $this->line('Safe to re-run — skips records that already exist.');
        $this->newLine();

        if (! $this->option('skip-roles')) {
            $this->info('Step 1/4 — AHOP role groups');
            $code = $this->call('ahop:setup-priority1', ['--force' => true]);
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $this->newLine();
        }

        if (! $this->option('skip-equipment')) {
            $this->info('Step 2/4 — Medical equipment (Snipe-IT assets module)');
            $args = $this->option('no-demo-assets') ? [] : ['--demo-assets' => true, '--demo-it-assets' => true];
            $code = $this->call('ahop:seed-equipment', $args);
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $this->newLine();
        }

        if (! $this->option('skip-users')) {
            $this->info('Step 3/4 — Demo staff logins (AHOP role groups)');
            $userArgs = ['--force' => true];
            if ($this->option('password')) {
                $userArgs['--password'] = $this->option('password');
            }
            $code = $this->call('ahop:seed-demo-users', $userArgs);
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $code = $this->call('ahop:seed-physicians', $userArgs);
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $this->newLine();
        }

        if (! $this->option('skip-clinical')) {
            $this->info('Step 4/6 — Clinic companies (patient Company field)');
            $code = $this->call('ahop:seed-companies');
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $this->newLine();

            $this->info('Step 5/6 — Clinic demo data (patients, OPD, appointments, lab, billing)');
            $count = max(1, min(25, (int) $this->option('patients')));
            $code = $this->call('ahop:seed-demo', ['--patients' => $count]);
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $this->newLine();
        }

        if (! $this->option('skip-clinical')) {
            $this->info('Step 6/6 — Clinical Analytics demo (risk, lab trends, equipment scores)');
            $code = $this->call('ahop:seed-analytics');
            if ($code !== self::SUCCESS) {
                return $code;
            }
            $this->newLine();
        }

        $this->info('AHOP seed complete.');
        $this->newLine();
        $this->line('Review in the app:');
        $this->line('  Dashboard, Patients, Appointments, OPD, Lab Orders, Billing, Clinical Analytics');
        $this->line('  Medical Equipment → List All (if demo assets were seeded)');
        if (! $this->option('skip-users')) {
            $this->line('  Demo logins: reception, physician, dr.santos, dr.garcia, … (password: demo1234)');
        }
        $this->newLine();
        $this->line('Fresh Snipe-IT sample data (users, licenses, IT assets): php artisan db:seed');
        $this->warn('  Only run db:seed on a new/empty install — not on production with real data.');

        return self::SUCCESS;
    }
}
