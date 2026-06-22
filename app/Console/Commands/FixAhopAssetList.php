<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\CompanyableScope;
use App\Models\Setting;
use App\Models\User;
use App\Support\AhopRoleTemplates;
use Database\Seeders\AhopCompanySeeder;
use Database\Seeders\AhopDemoUsersSeeder;
use Database\Seeders\MedicalEquipmentSeeder;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

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

        AhopRoleTemplates::syncAll(true);

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
        $this->line('  APP_URL: '.config('app.url'));
        $this->line('  Assets API route: '.route('api.assets.index'));
        $this->line('  Passport private key: '.(is_readable(storage_path('oauth-private.key')) ? 'OK' : 'MISSING (run: php artisan passport:install)'));

        foreach (['clinicadmin', 'biomedical'] as $username) {
            $user = User::query()->where('username', $username)->first();
            if (! $user) {
                $this->warn("  [{$username}] account not found — run ahop:seed-demo-users");

                continue;
            }

            auth()->guard('web')->login($user);
            $visible = Asset::query()->count();
            $this->line("  [{$username}] company_id=".($user->company_id ?? 'null').", visible assets={$visible}, assets.view=".($user->hasAccess('assets.view') ? 'yes' : 'no').', assets.checkout='.($user->hasAccess('assets.checkout') ? 'yes' : 'no').', assets.checkin='.($user->hasAccess('assets.checkin') ? 'yes' : 'no'));
        }

        auth()->guard('web')->logout();

        $this->diagnoseAssetsApi('clinicadmin');

        $this->newLine();
        $this->info('Repair complete.');
        $this->line('In the browser: log out and log back in, then open Medical Equipment → List All.');
        $this->line('If the table still says "No matching records":');
        $this->line('  1. Clear the search box on the assets table (X button).');
        $this->line('  2. F12 → Application → Local Storage → delete keys starting with assetsListingTable');
        $this->line('  3. Hard refresh (Ctrl+F5).');
        $this->line('If the table stays on "Loading... please wait...":');
        $this->line('  1. Set APP_URL in .env to your live site (e.g. https://ahop.jhewhome.xyz), then: php artisan config:clear && php artisan config:cache');
        $this->line('  2. Ensure Passport keys exist: php artisan passport:install');
        $this->line('  3. In browser F12 → Network, check /api/v1/hardware — should be 200 JSON, not 401/500 or wrong host.');
        $this->line('  4. tail storage/logs/laravel.log for PHP errors.');
        $this->line('If Checkin/Checkout buttons are missing on the assets table:');
        $this->line('  1. Log in as clinicadmin or biomedical (not reception/physician/labtech).');
        $this->line('  2. Run: php artisan ahop:setup-clinical-roles --force');
        $this->line('  3. Log out and back in, then hard refresh (Ctrl+F5).');
        $this->line('  4. Scroll right or use Columns menu — enable "Checkin/Checkout" if hidden.');
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

    protected function diagnoseAssetsApi(string $username): void
    {
        $user = User::query()->where('username', $username)->first();

        if (! $user) {
            $this->warn("  API test skipped — {$username} not found.");

            return;
        }

        try {
            Passport::actingAs($user, ['*']);

            $request = Request::create('/api/v1/hardware', 'GET', [
                'limit' => 5,
                'offset' => 0,
                'sort' => 'created_at',
                'order' => 'desc',
            ], [], [], [
                'HTTP_ACCEPT' => 'application/json',
            ]);

            $response = app()->handle($request);
            $status = $response->getStatusCode();
            $body = $response->getContent();
            $json = json_decode($body, true);
            $total = is_array($json) ? ($json['total'] ?? null) : null;
            $rowCount = is_array($json) && isset($json['rows']) && is_array($json['rows']) ? count($json['rows']) : null;

            $this->line("  API test [{$username}] HTTP {$status}, total=".($total ?? 'n/a').', rows returned='.($rowCount ?? 'n/a'));

            if ($status !== 200) {
                $snippet = substr(preg_replace('/\s+/', ' ', strip_tags($body)), 0, 180);
                $this->warn('  API response snippet: '.$snippet);
            } elseif (is_array($json) && isset($json['rows'][0])) {
                $first = $json['rows'][0];
                $actions = $first['available_actions'] ?? [];
                $this->line('  API sample row: checkout='.($actions['checkout'] ?? 'n/a').', checkin='.($actions['checkin'] ?? 'n/a').', user_can_checkout='.($first['user_can_checkout'] ?? 'n/a'));
            }
        } catch (\Throwable $e) {
            $this->error('  API test failed: '.$e->getMessage());
        }
    }
}
