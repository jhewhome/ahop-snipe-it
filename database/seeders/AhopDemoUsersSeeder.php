<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Group;
use App\Models\User;
use App\Support\AhopRoleTemplates;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo staff accounts with one AHOP role group each (local/testing).
 * Safe to re-run: skips existing usernames unless --force is passed via command.
 */
class AhopDemoUsersSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'demo1234';

    public function run(bool $force = false, ?string $password = null): void
    {
        $password = $password ?: self::DEFAULT_PASSWORD;
        $companyId = Company::query()->value('id');
        $prefix = (string) config('ahop_roles.prefix', 'AHOP ');

        AhopRoleTemplates::syncAll($force);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->demoAccounts() as $account) {
            $groupName = $prefix.$account['role'];
            $group = Group::query()->where('name', $groupName)->first();

            if (! $group) {
                $this->command?->warn("Role group not found: {$groupName}. Run ahop:setup-priority1 first.");

                continue;
            }

            $user = User::query()->where('username', $account['username'])->first();

            if ($user && ! $force) {
                $skipped++;
                $this->command?->line("  [skip] {$account['username']} ({$groupName})");

                continue;
            }

            if (! $user) {
                $user = new User();
                $user->username = $account['username'];
                $user->permissions = '{}';
                $user->activated = 1;
                $created++;
            } else {
                $updated++;
            }

            $user->first_name = $account['first_name'];
            $user->last_name = $account['last_name'];
            $user->email = $account['email'];
            $user->jobtitle = $account['jobtitle'];
            $user->company_id = $companyId;
            $user->password = Hash::make($password);
            $user->save();

            $user->groups()->sync([$group->id]);

            $action = $user->wasRecentlyCreated ? 'created' : 'updated';
            $this->command?->line("  [{$action}] {$account['username']} → {$groupName}");
        }

        $this->command?->newLine();
        $this->command?->info("Demo users: {$created} created, {$updated} updated, {$skipped} skipped.");
        $this->command?->line('  Password for all demo accounts: '.$password);
        $this->command?->line('  Log in at /login — use username (not email).');
    }

    /**
     * @return list<array{username: string, role: string, first_name: string, last_name: string, email: string, jobtitle: string}>
     */
    protected function demoAccounts(): array
    {
        return [
            [
                'username' => 'reception',
                'role' => 'Reception',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'email' => 'reception@demo.agilitycare.local',
                'jobtitle' => 'Receptionist',
            ],
            [
                'username' => 'physician',
                'role' => 'Clinic Staff',
                'first_name' => 'Juan',
                'last_name' => 'Reyes',
                'email' => 'physician@demo.agilitycare.local',
                'jobtitle' => 'Physician',
            ],
            [
                'username' => 'labtech',
                'role' => 'Laboratory',
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'email' => 'labtech@demo.agilitycare.local',
                'jobtitle' => 'Lab Technician',
            ],
            [
                'username' => 'biomedical',
                'role' => 'Biomedical',
                'first_name' => 'Rico',
                'last_name' => 'Mendoza',
                'email' => 'biomedical@demo.agilitycare.local',
                'jobtitle' => 'Biomedical Engineer',
            ],
            [
                'username' => 'clinicadmin',
                'role' => 'Clinic Administrator',
                'first_name' => 'Elena',
                'last_name' => 'Torres',
                'email' => 'clinicadmin@demo.agilitycare.local',
                'jobtitle' => 'Clinic Administrator',
            ],
        ];
    }
}
