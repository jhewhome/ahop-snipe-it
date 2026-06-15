<?php

namespace App\Providers;

use App\Support\ClinicalDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AhopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole() && ClinicalDatabase::isEnabled()) {
            try {
                DB::connection(ClinicalDatabase::connectionName())->getPdo();
            } catch (Throwable $e) {
                Log::warning('AHOP clinical PostgreSQL connection failed: '.$e->getMessage());
            }
        }
    }
}
