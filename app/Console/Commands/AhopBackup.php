<?php

namespace App\Console\Commands;

use App\Support\ClinicalDatabase;
use App\Support\MysqlDumpPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class AhopBackup extends Command
{
    protected $signature = 'ahop:backup
                            {--filename= : Optional Snipe-IT backup zip filename}';

    protected $description = 'Full AHOP backup: Snipe-IT (MySQL + uploads) and optional PostgreSQL clinical database';

    public function handle(): int
    {
        ini_set('max_execution_time', (string) env('BACKUP_TIME_LIMIT', 600));

        $this->info('AHOP backup starting…');

        if (! $this->assertMysqlDumpAvailable()) {
            return self::FAILURE;
        }

        $filename = $this->option('filename');
        if ($filename) {
            $this->call('snipeit:backup', ['--filename' => $filename]);
        } else {
            $this->call('snipeit:backup');
        }

        if ($this->backupClinicalPostgres()) {
            $this->info('Clinical PostgreSQL dump completed.');
        }

        $this->call('backup:clean');
        $this->info('AHOP backup finished. Copy files from storage/app/backups to off-site storage.');

        return self::SUCCESS;
    }

    protected function backupClinicalPostgres(): bool
    {
        if (! ClinicalDatabase::isEnabled() || ! ClinicalDatabase::isAvailable()) {
            return false;
        }

        $connection = config('database.connections.'.ClinicalDatabase::connectionName());
        if (($connection['driver'] ?? '') !== 'pgsql') {
            $this->warn('Clinical connection is not PostgreSQL; skipping clinical dump.');

            return false;
        }

        $host = $connection['host'] ?? '127.0.0.1';
        $port = (string) ($connection['port'] ?? '5432');
        $database = $connection['database'] ?? '';
        $username = $connection['username'] ?? '';
        $password = $connection['password'] ?? '';

        if ($database === '' || $username === '') {
            $this->error('Clinical database credentials incomplete in .env (CLINICAL_DB_*).');

            return false;
        }

        $dir = storage_path('app/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $outFile = $dir.DIRECTORY_SEPARATOR.'clinical-'.date('Y-m-d-His').'.sql';

        $pgDump = $this->resolvePgDumpBinary();
        if ($pgDump === null) {
            $this->error('pg_dump not found. Install PostgreSQL client tools or add pg_dump to PATH.');

            return false;
        }

        $env = array_filter([
            'PGPASSWORD' => $password,
        ]);

        $process = new Process([
            $pgDump,
            '--host='.$host,
            '--port='.$port,
            '--username='.$username,
            '--no-owner',
            '--no-acl',
            '-f', $outFile,
            $database,
        ], null, $env);

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Clinical pg_dump failed: '.$process->getErrorOutput());

            if (File::exists($outFile)) {
                File::delete($outFile);
            }

            return false;
        }

        $size = File::size($outFile);
        $this->line('  Clinical dump: '.basename($outFile).' ('.number_format($size / 1024, 1).' KB)');

        return true;
    }

    protected function resolvePgDumpBinary(): ?string
    {
        $custom = env('PG_DUMP_PATH');
        if ($custom && is_executable($custom)) {
            return $custom;
        }

        foreach (['pg_dump', 'pg_dump.exe'] as $binary) {
            $process = new Process([$binary, '--version']);
            $process->run();
            if ($process->isSuccessful()) {
                return $binary;
            }
        }

        $windowsPaths = [
            'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe',
        ];

        foreach ($windowsPaths as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function assertMysqlDumpAvailable(): bool
    {
        if (MysqlDumpPath::resolveExecutable() !== null) {
            return true;
        }

        $configured = MysqlDumpPath::configuredDirectory();
        $this->error('mysqldump was not found. Snipe-IT backup cannot run.');
        $this->line('  Configured DB_DUMP_PATH: '.($configured !== '' ? $configured : '(empty)'));

        if (PHP_OS_FAMILY === 'Windows') {
            $this->newLine();
            $this->line('On XAMPP, add to your .env file:');
            $this->line('  DB_DUMP_PATH=C:/xampp/mysql/bin');
            foreach (MysqlDumpPath::commonWindowsPaths() as $path) {
                if (MysqlDumpPath::executableInDirectory($path) !== null) {
                    $this->line('  (mysqldump exists at '.$path.')');
                }
            }
        }

        return false;
    }
}
