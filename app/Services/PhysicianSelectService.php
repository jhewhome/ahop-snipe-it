<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class PhysicianSelectService
{
    /**
     * AHOP permission group names eligible for Attending Physician fields.
     *
     * @return list<string>
     */
    public static function roleGroupNames(): array
    {
        $prefix = (string) config('ahop_roles.prefix', 'AHOP ');

        return collect(config('ahop.physician_role_groups', ['Clinic Staff', 'Clinic Administrator']))
            ->map(fn (string $label) => $prefix.$label)
            ->values()
            ->all();
    }

    /**
     * @param  Builder<\App\Models\User>  $query
     * @return Builder<\App\Models\User>
     */
    public static function applySelectlistFilter(Builder $query): Builder
    {
        $groups = self::roleGroupNames();

        if ($groups === []) {
            return $query;
        }

        return $query->whereHas('groups', function (Builder $groupQuery) use ($groups) {
            $groupQuery->whereIn('permission_groups.name', $groups);
        });
    }
}
