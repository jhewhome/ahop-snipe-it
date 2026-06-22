<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\CompanyableScope;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AhopCompanySeeder;
use Database\Seeders\AhopDemoUsersSeeder;
use Database\Seeders\MedicalEquipmentSeeder;
use Illuminate\Console\Command;

class FixAhopAssetList extends Command
{
    protected $signature = 'ahop:fix-asset-list
                            {--seed : Also create missing demo assets (AC-EQ / AC-IT tags)}';

    protected $description = 'Fix empty Medical Equipment list: link demo users/assets to clinic company and verify API visibility';

    public function handle(): int
    {
        $this->info('AHOP asset list repair');
        $this->newLine();

        $companySeeder = new AhopCompanySeeder;
        $companySeeder->setCommand($this);
        $companies = $companySeeder->run(backfillPatients: false);
        $companyId = $companies['default_id'];

        $userSeeder = new AhopDemoUsersSeeder;
        $userSeeder->setCommand($this);
        $userSeeder->run();

        $equipmentSeeder = new MedicalEquipmentSeeder;
        $equipmentSeeder->setCommand($this);
        if ($this->option('seed')) {
            $equipmentSeeder->run(withDemoMedicalAssets: true, withDemoItAssets: true);
        } else {
            $equipmentSeeder->backfillDemoAssetCompanies();
            $equipmentSeeder->backfillDemoAssetPurchaseCosts();
        }

        $settings = Setting::getSettings();
        $fmcs = (string) ($settings->full_multiple_companies_support ?? '0') === '1';

        $this->newLine();
        $this->line('Diagnostics:');
        $this->line('  Full Multiple Company Support: '.($fmcs ? 'ON' : 'off'));
        $this->line('  Default clinic company id: '.($companyId ?: 'none'));
        $this->line('  Demo assets (AC-EQ / AC-IT): '.$this->demoAssetCount());

        foreach (['clinicadmin', 'biomedical'] as $username) {
            $user = User::query()->where('username', $username)->first();
            if (! $user) {
                $this->warn("  [{$username}] account not found — run ahop:seed-demo-users");

                continue;
            }

            auth()->login($user);
            $visible = Asset::query()->count();
            $this->line("  [{$username}] company_id=".($user->company_id ?? 'null').", visible assets={$visible}, assets.view=".($user->hasAccess('assets.view') ? 'yes' : 'no'));
        }

        auth()->logout();

        $this->newLine();
        $this->info('Repair complete.');
        $this->line('In the browser: log out/in, open Medical Equipment → List All.');
        $this->line('If the table is still empty, clear site Local Storage (F12 → Application) for keys like assetsListingTable.*');
        $this->line('Optional: add demo tags with --seed');

        return self::SUCCESS;
    }

    protected function demoAssetCount(): int
    {
        return Asset::withoutGlobalScope(CompanyableScope::class)
            ->where(function ($query) {
                $query->where('asset_tag', 'like', 'AC-EQ-%')
                    ->orWhere('asset_tag', 'like', 'AC-IT-%');
            })
            ->count();
    }
}
