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

        if ($fmcs) {
            $orphanAssets = $this->backfillOrphanAssetCompanies($companyId);
            if ($orphanAssets > 0) {
                $this->info("FMCS: {$orphanAssets} asset(s) with no company linked to clinic company id {$companyId}.");
            }
        }

        $this->newLine();
        $this->line('Diagnostics:');
        $this->line('  Full Multiple Company Support: '.($fmcs ? 'ON' : 'off'));
        $this->line('  Default clinic company id: '.($companyId ?: 'none'));
        $this->line('  Total assets in database: '.$this->totalAssetCount());
        $this->line('  Assets without company_id: '.$this->assetsMissingCompanyCount());
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
        $this->line('In the browser: log out and log back in, then open Medical Equipment → List All.');
        $this->line('If the table still says "No matching records":');
        $this->line('  1. Clear the search box on the assets table (X button).');
        $this->line('  2. F12 → Application → Local Storage → delete keys starting with assetsListingTable');
        $this->line('  3. Hard refresh (Ctrl+F5).');
        if ($this->demoAssetCount() === 0) {
            $this->warn('No demo assets found — run: php artisan ahop:fix-asset-list --seed');
        } else {
            $this->line('Optional: recreate demo tags with php artisan ahop:fix-asset-list --seed');
        }

        return self::SUCCESS;
    }

    protected function backfillOrphanAssetCompanies(?int $companyId): int
    {
        if (! $companyId) {
            return 0;
        }

        return Asset::withoutGlobalScope(CompanyableScope::class)
            ->whereNull('company_id')
            ->update(['company_id' => $companyId]);
    }

    protected function totalAssetCount(): int
    {
        return Asset::withoutGlobalScope(CompanyableScope::class)->count();
    }

    protected function assetsMissingCompanyCount(): int
    {
        return Asset::withoutGlobalScope(CompanyableScope::class)
            ->whereNull('company_id')
            ->count();
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
