<?php

namespace App\Models\Traits;

use App\Support\ClinicalDatabase;

trait UsesClinicalDatabase
{
    public function getConnectionName(): ?string
    {
        if (ClinicalDatabase::isEnabled()) {
            return ClinicalDatabase::activeConnectionName();
        }

        return parent::getConnectionName();
    }
}
