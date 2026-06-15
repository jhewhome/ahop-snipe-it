<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Support\AhopRoleTemplates;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupPriority1 extends Command
{
    protected $signature = 'ahop:setup-priority1
                            {--force : Update permissions on existing AHOP role groups}';

    protected $description = 'Run Priority 1 setup: clinical roles, backup health check, and print adoption checklist';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->info('AHOP Priority 1 — Security, backups & staff adoption');
        $this->newLine();

        $this->info('Step 1/2 — Clinical role groups');
        foreach (AhopRoleTemplates::roles() as $role) {
            $existing = Group::query()->where('name', $role['name'])->exists();
            if ($existing && ! $force) {
                $this->line("  [skip] {$role['name']}");
                continue;
            }

            AhopRoleTemplates::syncRole($role, $force);
            $action = $existing ? 'updated' : 'created';
            $this->line("  [{$action}] {$role['name']}");
        }

        $this->newLine();
        $this->info('Step 2/2 — Backup health');
        Artisan::call('ahop:backup-health');
        $this->line(trim(Artisan::output()));

        $this->newLine();
        $this->printChecklist();

        return self::SUCCESS;
    }

    private function printChecklist(): void
    {
        $this->info('Operational checklist (complete outside this command):');
        $this->line('  [ ] Admin → Users: assign each staff member to one AHOP group');
        $this->line('  [ ] Limit Superuser accounts to IT only (1–2 users)');
        $this->line('  [ ] Windows Task Scheduler: php artisan schedule:run every minute');
        $this->line('  [ ] Verify .env: AHOP_DAILY_BACKUP=true');
        $this->line('  [ ] Copy backups off-site (USB / cloud / second server)');
        $this->line('  [ ] Staff training: open Staff Guide and follow patient → OPD → lab flow');
        $this->line('  [ ] Stop new patient/OPD/lab rows in Excel or paper logs');
        $this->newLine();
        $this->line('Staff Guide URL path: /staff-guide');
        $this->line('Daily backup: php artisan ahop:backup');
    }
}
