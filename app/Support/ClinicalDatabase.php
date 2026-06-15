<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

class ClinicalDatabase
{
    protected static ?bool $available = null;

    public static function isEnabled(): bool
    {
        return (bool) config('ahop.clinical_database.enabled', false);
    }

    public static function isAvailable(): bool
    {
        if (! self::isEnabled()) {
            return false;
        }

        if (self::$available !== null) {
            return self::$available;
        }

        try {
            DB::connection(self::connectionName())->getPdo();
            self::$available = true;
        } catch (Throwable) {
            self::$available = false;
        }

        return self::$available;
    }

    public static function connectionName(): string
    {
        return (string) config('ahop.clinical_database.connection', 'clinical');
    }

    /**
     * Connection used by clinical models (PostgreSQL when available, else MySQL).
     */
    public static function activeConnectionName(): string
    {
        if (self::isAvailable()) {
            return self::connectionName();
        }

        return (string) config('database.default');
    }

    public static function usesTable(string $table): bool
    {
        return self::isEnabled() && in_array($table, config('ahop.clinical_tables', []), true);
    }

    public static function connectionForTable(string $table): string
    {
        if (self::usesTable($table) && self::isAvailable()) {
            return self::connectionName();
        }

        if (self::usesTable($table)) {
            return (string) config('database.default');
        }

        return (string) config('database.default');
    }
}
