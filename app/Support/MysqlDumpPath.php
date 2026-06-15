<?php

namespace App\Support;

class MysqlDumpPath
{
    /**
     * Resolve mysqldump executable from DB_DUMP_PATH (or common Windows install paths).
     */
    public static function resolveExecutable(): ?string
    {
        $configured = (string) config('database.dump_binary_path', '');
        $candidates = array_filter(array_unique([
            $configured,
            ...self::commonWindowsPaths(),
        ]));

        foreach ($candidates as $dir) {
            $executable = self::executableInDirectory($dir);
            if ($executable !== null) {
                return $executable;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function commonWindowsPaths(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        return [
            'C:\\xampp\\mysql\\bin',
            'C:\\PUP\\mysql\\bin',
            'C:\\laragon\\bin\\mysql',
        ];
    }

    public static function executableInDirectory(string $directory): ?string
    {
        $directory = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($directory)), DIRECTORY_SEPARATOR);
        if ($directory === '') {
            return null;
        }

        $name = PHP_OS_FAMILY === 'Windows' ? 'mysqldump.exe' : 'mysqldump';
        $path = $directory.DIRECTORY_SEPARATOR.$name;

        return is_file($path) ? $path : null;
    }

    public static function configuredDirectory(): string
    {
        return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, (string) config('database.dump_binary_path', '')), DIRECTORY_SEPARATOR);
    }
}
