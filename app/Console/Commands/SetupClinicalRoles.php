<?php

namespace App\Console\Commands;

use App\Support\AhopRoleTemplates;
use Illuminate\Console\Command;

class SetupClinicalRoles extends Command
{
    protected $signature = 'ahop:setup-clinical-roles
                            {--force : Update permissions on existing AHOP role groups}';

    protected $description = 'Create or update AHOP clinical permission groups (Reception, Clinic, Lab, Biomedical, Admin)';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->info('AHOP clinical roles (Priority 1 — security)');
        $this->newLine();

        foreach (AhopRoleTemplates::roles() as $role) {
            $existing = \App\Models\Group::query()->where('name', $role['name'])->exists();
            $action = ($existing && ! $force) ? 'exists (skip)' : ($existing ? 'updated' : 'created');

            if ($existing && ! $force) {
                $this->line("  [skip] {$role['name']}");
                continue;
            }

            AhopRoleTemplates::syncRole($role, $force);
            $this->line("  [{$action}] {$role['name']}");
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Admin → Users → assign each staff member to one AHOP group');
        $this->line('  2. Keep Superuser for IT only (1–2 accounts)');
        $this->line('  3. Run: php artisan ahop:setup-priority1');

        return self::SUCCESS;
    }
}
