<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Patient;
use Illuminate\Support\Facades\Session;

/**
 * Resolves which clinic/site applies to intake, visits, and printed documents.
 */
class ClinicSiteService
{
    public const SESSION_KEY = 'ahop_clinic_site_id';

    public function availableSites()
    {
        return Company::query()->orderBy('name')->get(['id', 'name']);
    }

    public function defaultSiteId(): ?int
    {
        $name = config('ahop.default_clinic_company_name');

        if ($name) {
            $id = Company::query()->where('name', $name)->value('id');
            if ($id) {
                return (int) $id;
            }
        }

        $firstId = Company::query()->orderBy('id')->value('id');

        return $firstId ? (int) $firstId : null;
    }

    public function sessionSiteId(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return $id ? (int) $id : null;
    }

    public function setSessionSite(?int $companyId): void
    {
        if ($companyId && Company::query()->where('id', $companyId)->exists()) {
            Session::put(self::SESSION_KEY, $companyId);
        } else {
            Session::forget(self::SESSION_KEY);
        }
    }

    public function ensureSessionSite(): ?int
    {
        $current = $this->sessionSiteId();

        if ($current && Company::query()->where('id', $current)->exists()) {
            return $current;
        }

        $resolved = $this->resolve();

        if ($resolved) {
            $this->setSessionSite($resolved);
        }

        return $resolved;
    }

    public function resolve(?int $explicitId = null, ?Patient $patient = null): ?int
    {
        if ($explicitId !== null && $explicitId > 0) {
            $fromInput = Company::getIdForCurrentUser($explicitId);
            if ($fromInput) {
                return (int) $fromInput;
            }
        }

        if ($explicitId === null && auth()->check()) {
            $userCompany = Company::getIdForCurrentUser(null);
            if ($userCompany) {
                return (int) $userCompany;
            }
        }

        if ($sessionId = $this->sessionSiteId()) {
            if (Company::query()->where('id', $sessionId)->exists()) {
                return $sessionId;
            }
        }

        if ($patient?->company_id) {
            return (int) $patient->company_id;
        }

        return $this->defaultSiteId();
    }

    public function resolveFromRequest($unescapedInput, ?Patient $patient = null): ?int
    {
        $explicit = null;

        if ($unescapedInput !== null && $unescapedInput !== '') {
            $fromInput = Company::getIdFromInput($unescapedInput);
            $explicit = $fromInput ? (int) $fromInput : null;
        }

        return $this->resolve($explicit, $patient);
    }

    public function siteName(?int $companyId): ?string
    {
        if (! $companyId) {
            return null;
        }

        return Company::query()->where('id', $companyId)->value('name');
    }
}
