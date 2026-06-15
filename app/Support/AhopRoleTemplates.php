<?php

namespace App\Support;

use App\Models\Group;
use Illuminate\Support\Collection;

class AhopRoleTemplates
{
    /**
     * @return Collection<int, array{name: string, notes: string, permissions: array<string, string>}>
     */
    public static function roles(): Collection
    {
        $prefix = (string) config('ahop_roles.prefix', 'AHOP ');
        $roles = config('ahop_roles.roles', []);

        return collect($roles)->map(function (array $role, string $label) use ($prefix) {
            return [
                'name' => $prefix.$label,
                'notes' => $role['notes'] ?? '',
                'permissions' => $role['permissions'] ?? [],
            ];
        })->values();
    }

    public static function syncRole(array $role, bool $force = false): Group
    {
        $group = Group::query()->where('name', $role['name'])->first();

        if ($group && ! $force) {
            return $group;
        }

        if (! $group) {
            $group = new Group();
            $group->name = $role['name'];
        }

        $group->notes = $role['notes'];
        $group->permissions = json_encode($role['permissions']);
        $group->save();

        return $group;
    }

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public static function syncAll(bool $force = false): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0];

        foreach (self::roles() as $role) {
            $existing = Group::query()->where('name', $role['name'])->first();

            if ($existing && ! $force) {
                $stats['skipped']++;

                continue;
            }

            $wasNew = $existing === null;
            self::syncRole($role, $force);

            if ($wasNew) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }
        }

        return $stats;
    }
}
