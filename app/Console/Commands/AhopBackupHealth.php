<?php

namespace App\Console\Commands;

use App\Support\MysqlDumpPath;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AhopBackupHealth extends Command
{
    protected $signature = 'ahop:backup-health';

    protected $description = 'Check that recent AHOP/Snipe-IT backups exist (Priority 1 monitoring)';

    public function handle(): int
    {
        $maxAgeHours = (int) config('ahop.priority1.backup_health_max_age_hours', 26);
        $path = 'app/backups';
        $files = Storage::files($path);

        $candidates = [];
        foreach ($files as $file) {
            $base = basename($file);
            if (str_starts_with($base, '.')) {
                continue;
            }
            if (! str_ends_with($base, '.zip') && ! str_ends_with($base, '.sql')) {
                continue;
            }

            $candidates[] = [
                'name' => $base,
                'modified' => Storage::lastModified($file),
                'size' => Storage::size($file),
            ];
        }

        if ($candidates === []) {
            $this->error('No backup files found in storage/app/backups.');
            $this->line('Run: php artisan ahop:backup');

            if (MysqlDumpPath::resolveExecutable() === null) {
                $this->newLine();
                $this->warn('mysqldump is missing or DB_DUMP_PATH is wrong — fix .env before backups will succeed.');
                if (PHP_OS_FAMILY === 'Windows') {
                    $this->line('Example: DB_DUMP_PATH=C:/xampp/mysql/bin');
                }
            }

            return self::FAILURE;
        }

        usort($candidates, fn ($a, $b) => $b['modified'] <=> $a['modified']);

        $this->info('Backup health check');
        $this->line('  Max age allowed: '.$maxAgeHours.' hours');
        $this->newLine();

        $newest = $candidates[0];
        $newestAt = Carbon::createFromTimestamp($newest['modified']);
        $ageHours = $newestAt->diffInHours(now());
        $ok = $ageHours <= $maxAgeHours;

        $this->table(
            ['File', 'Size', 'Modified', 'Age (hours)'],
            collect($candidates)->take(5)->map(function (array $f) {
                return [
                    $f['name'],
                    number_format($f['size'] / 1024 / 1024, 2).' MB',
                    Carbon::createFromTimestamp($f['modified'])->toDateTimeString(),
                    Carbon::createFromTimestamp($f['modified'])->diffInHours(now()),
                ];
            })->all()
        );

        if ($ok) {
            $this->info('OK — newest backup is within the allowed age.');

            return self::SUCCESS;
        }

        $this->error('UNHEALTHY — newest backup is older than '.$maxAgeHours.' hours.');
        $this->line('Schedule daily backups: php artisan ahop:backup (see AHOP_DAILY_BACKUP in .env)');

        return self::FAILURE;
    }
}
