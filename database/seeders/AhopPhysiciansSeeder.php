<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Group;
use App\Models\User;
use App\Support\AhopRoleTemplates;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Sample attending physicians for local/testing (Attending Physician dropdowns).
 * Safe to re-run: skips existing usernames unless --force is passed via command.
 */
class AhopPhysiciansSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'demo1234';

    public function run(bool $force = false, ?string $password = null): void
    {
        $password = $password ?: self::DEFAULT_PASSWORD;
        $companyId = Company::query()->value('id');
        $prefix = (string) config('ahop_roles.prefix', 'AHOP ');
        $groupName = $prefix.'Clinic Staff';
        $group = Group::query()->where('name', $groupName)->first();

        if (! $group) {
            AhopRoleTemplates::syncAll($force);
            $group = Group::query()->where('name', $groupName)->first();
        }

        if (! $group) {
            $this->command?->error("Role group not found: {$groupName}. Run: php artisan ahop:setup-priority1");

            return;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->physicianAccounts() as $account) {
            $user = User::query()->where('username', $account['username'])->first();

            if ($user && ! $force) {
                $skipped++;
                $this->command?->line("  [skip] {$account['username']} ({$account['first_name']} {$account['last_name']})");

                continue;
            }

            if (! $user) {
                $user = new User();
                $user->username = $account['username'];
                $user->permissions = '{}';
                $created++;
            } else {
                $updated++;
            }

            $user->activated = 1;
            $user->show_in_list = 1;
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
        $this->command?->info("Attending physicians: {$created} created, {$updated} updated, {$skipped} skipped.");
        $this->command?->line('  These users appear in Attending Physician dropdowns (AHOP Clinic Staff).');
        if ($created > 0 || $updated > 0) {
            $this->command?->line('  Password for new/updated accounts: '.$password);
        }
    }

    /**
     * @return list<array{username: string, first_name: string, last_name: string, email: string, jobtitle: string}>
     */
    public static function accounts(): array
    {
        return (new self())->physicianAccounts();
    }

    /**
     * @return list<array{username: string, first_name: string, last_name: string, email: string, jobtitle: string}>
     */
    protected function physicianAccounts(): array
    {
        return [
            [
                'username' => 'physician',
                'first_name' => 'Juan',
                'last_name' => 'Reyes',
                'email' => 'physician@demo.agilitycare.local',
                'jobtitle' => 'Attending Physician',
            ],
            [
                'username' => 'dr.santos',
                'first_name' => 'Carmen',
                'last_name' => 'Santos',
                'email' => 'dr.santos@demo.agilitycare.local',
                'jobtitle' => 'Attending Physician',
            ],
            [
                'username' => 'dr.garcia',
                'first_name' => 'Roberto',
                'last_name' => 'Garcia',
                'email' => 'dr.garcia@demo.agilitycare.local',
                'jobtitle' => 'Attending Physician',
            ],
            [
                'username' => 'dr.cruz',
                'first_name' => 'Patricia',
                'last_name' => 'Cruz',
                'email' => 'dr.cruz@demo.agilitycare.local',
                'jobtitle' => 'Attending Physician',
            ],
            [
                'username' => 'dr.mendoza',
                'first_name' => 'Miguel',
                'last_name' => 'Mendoza',
                'email' => 'dr.mendoza@demo.agilitycare.local',
                'jobtitle' => 'Attending Physician',
            ],
            [
                'username' => 'dr.lim',
                'first_name' => 'Angela',
                'last_name' => 'Lim',
                'email' => 'dr.lim@demo.agilitycare.local',
                'jobtitle' => 'Attending Physician',
            ],
        ];
    }
}
