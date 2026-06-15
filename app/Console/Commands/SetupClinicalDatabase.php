<?php

namespace App\Console\Commands;

use App\Support\ClinicalDatabase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SetupClinicalDatabase extends Command
{
    protected $signature = 'ahop:clinical-db-setup
                            {--migrate-only : Only run PostgreSQL clinical migrations}
                            {--import-from-mysql : Copy clinical rows from MySQL if tables exist there}
                            {--drop-mysql-tables : After import, remove clinical tables from MySQL}';

    protected $description = 'Set up AHOP clinical data on PostgreSQL (dual-database: clinical on PG, assets on MySQL)';

    public function handle(): int
    {
        if (! ClinicalDatabase::isEnabled()) {
            $this->error('Clinical database is disabled. Set AHOP_CLINICAL_DATABASE_ENABLED=true in .env');

            return self::FAILURE;
        }

        $clinical = ClinicalDatabase::connectionName();
        $mysql = (string) config('database.default');

        try {
            DB::connection($clinical)->getPdo();
        } catch (Throwable $e) {
            $this->error('Cannot connect to clinical PostgreSQL: '.$e->getMessage());
            $this->line('Check CLINICAL_DB_* variables in .env and create the database first.');

            return self::FAILURE;
        }

        $this->info('Clinical connection ['.$clinical.'] OK. Asset connection ['.$mysql.'] unchanged.');

        $this->call('migrate', [
            '--database' => $clinical,
            '--path' => 'database/migrations/clinical',
            '--force' => true,
        ]);

        if ($this->option('migrate-only')) {
            $this->info('Clinical PostgreSQL migrations complete.');

            return self::SUCCESS;
        }

        if ($this->option('import-from-mysql') || $this->shouldAutoImport($mysql, $clinical)) {
            $this->importFromMysql($mysql, $clinical);
        }

        if ($this->option('drop-mysql-tables')) {
            $this->dropMysqlClinicalTables($mysql);
        }

        $this->info('AHOP clinical database setup finished.');
        $this->line('Patients, OPD, and lab data use PostgreSQL. Users and assets remain on MySQL.');

        return self::SUCCESS;
    }

    protected function shouldAutoImport(string $mysql, string $clinical): bool
    {
        if (! Schema::connection($mysql)->hasTable('patients')) {
            return false;
        }

        if (! Schema::connection($clinical)->hasTable('patients')) {
            return false;
        }

        $mysqlCount = (int) DB::connection($mysql)->table('patients')->count();
        $clinicalCount = (int) DB::connection($clinical)->table('patients')->count();

        return $mysqlCount > 0 && $clinicalCount === 0;
    }

    protected function importFromMysql(string $mysql, string $clinical): void
    {
        $tables = ['patients', 'opd_visits', 'lab_orders', 'lab_results'];

        if (! Schema::connection($mysql)->hasTable('patients')) {
            $this->line('No clinical tables on MySQL to import.');

            return;
        }

        DB::connection($clinical)->statement(
            'TRUNCATE TABLE lab_results, lab_orders, opd_visits, patients RESTART IDENTITY CASCADE'
        );

        foreach ($tables as $table) {
            if (! Schema::connection($mysql)->hasTable($table)) {
                continue;
            }

            $count = (int) DB::connection($mysql)->table($table)->count();
            if ($count === 0) {
                continue;
            }

            $this->info("Importing {$count} row(s) from MySQL.{$table} → PostgreSQL.{$table}...");

            DB::connection($mysql)->table($table)->orderBy('id')->chunk(200, function ($rows) use ($clinical, $table) {
                $payload = array_map(fn ($row) => (array) $row, $rows->all());
                DB::connection($clinical)->table($table)->insert($payload);
            });
        }

        $this->resetClinicalSequences($clinical);
        $this->info('Import from MySQL complete.');
    }

    protected function resetClinicalSequences(string $clinical): void
    {
        $sequences = [
            'patients' => 'patients_id_seq',
            'opd_visits' => 'opd_visits_id_seq',
            'lab_orders' => 'lab_orders_id_seq',
            'lab_results' => 'lab_results_id_seq',
        ];

        foreach ($sequences as $table => $sequence) {
            if (! Schema::connection($clinical)->hasTable($table)) {
                continue;
            }

            $max = (int) DB::connection($clinical)->table($table)->max('id');
            if ($max < 1) {
                continue;
            }

            try {
                DB::connection($clinical)->statement(
                    "SELECT setval('{$sequence}', {$max}, true)"
                );
            } catch (Throwable) {
                // Sequence names may differ; non-fatal.
            }
        }
    }

    protected function dropMysqlClinicalTables(string $mysql): void
    {
        if (! Schema::connection($mysql)->hasTable('patients')) {
            $this->line('No clinical tables on MySQL to drop.');

            return;
        }

        if (! $this->confirm('Remove patients, opd_visits, lab_orders, and lab_results from MySQL?', false)) {
            return;
        }

        Schema::connection($mysql)->disableForeignKeyConstraints();
        Schema::connection($mysql)->dropIfExists('lab_results');
        Schema::connection($mysql)->dropIfExists('lab_orders');
        Schema::connection($mysql)->dropIfExists('opd_visits');
        Schema::connection($mysql)->dropIfExists('patients');
        Schema::connection($mysql)->enableForeignKeyConstraints();

        $this->info('Clinical tables removed from MySQL.');
    }
}
