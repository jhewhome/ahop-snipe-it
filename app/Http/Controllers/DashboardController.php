<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Component;
use App\Models\Consumable;
use App\Models\License;
use App\Services\Ahop\ClinicalDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;

/**
 * This controller handles all actions related to the Admin Dashboard
 * for the Snipe-IT Asset Management application.
 *
 * @author A. Gianotto <snipe@snipe.net>
 *
 * @version v1.0
 */
class DashboardController extends Controller
{
    /**
     * Check authorization and display admin dashboard, otherwise display
     * the user's checked-out assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @since [v1.0]
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if (ClinicalDashboardService::canViewDashboard($user)) {
            return $this->clinicalDashboard();
        }

        // Snipe-IT asset dashboard for IT superusers when clinical dashboard does not apply.
        if ($user->hasAccess('admin')) {
            $asset_stats = null;

            $counts['asset'] = Asset::count();
            $counts['accessory'] = Accessory::count();
            $counts['license'] = License::assetcount();
            $counts['consumable'] = Consumable::count();
            $counts['component'] = Component::count();
            $counts['user'] = Company::scopeCompanyables(auth()->user())->count();
            $counts['grand_total'] = $counts['asset'] + $counts['accessory'] + $counts['license'] + $counts['consumable'];

            if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('passport:install', ['--no-interaction' => true]);
            }

            return view('dashboard')->with('asset_stats', $asset_stats)->with('counts', $counts);
        } else {
            Session::reflash();

            // Redirect to the profile page
            return redirect()->intended('account/view-assets');
        }
    }

    /**
     * AHOP clinical operations dashboard (UI Phase B).
     */
    protected function clinicalDashboard(): View
    {
        return view('dashboard-ahop', app(ClinicalDashboardService::class)->build());
    }

    /**
     * JSON payload for clinical dashboard auto-refresh (Phase B).
     */
    public function clinicalData(): JsonResponse
    {
        abort_unless(ClinicalDashboardService::canViewDashboard(), 403);

        abort_unless(config('ahop.dashboard_auto_refresh.enabled', true), 404);

        return response()->json(app(ClinicalDashboardService::class)->refreshPayload());
    }
}
