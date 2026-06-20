<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    /**
     * Active users eligible for Attending Physician dropdowns.
     *
     * @return Collection<int, User>
     */
    public static function roster(?int $includeUserId = null): Collection
    {
        $query = User::query()
            ->select([
                'users.id',
                'users.username',
                'users.first_name',
                'users.last_name',
                'users.display_name',
                'users.employee_num',
            ])
            ->where('activated', 1);

        $physicians = self::applySelectlistFilter($query)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($includeUserId && ! $physicians->contains('id', $includeUserId)) {
            $current = User::query()->find($includeUserId);
            if ($current) {
                $physicians->push($current);
            }
        }

        return $physicians;
    }

    public static function isEligiblePhysician(?User $user): bool
    {
        if (! $user || ! $user->activated) {
            return false;
        }

        return self::applySelectlistFilter(User::query()->where('users.id', $user->id))->exists();
    }

    public static function defaultPhysicianId(?User $user = null): ?int
    {
        $user = $user ?? auth()->user();

        if (! self::isEligiblePhysician($user)) {
            return null;
        }

        return (int) $user->id;
    }

    public static function applyDefaultPhysician(object $item): void
    {
        if (! empty($item->physician_id)) {
            return;
        }

        $defaultId = self::defaultPhysicianId();

        if ($defaultId) {
            $item->physician_id = $defaultId;
        }
    }

    /**
     * Use an explicit selection when present; otherwise default to the logged-in
     * physician when they are eligible (typical OPD documentation workflow).
     */
    public static function resolvePhysicianId(?int $selectedId = null): ?int
    {
        if (! empty($selectedId)) {
            return (int) $selectedId;
        }

        return self::defaultPhysicianId();
    }
}
