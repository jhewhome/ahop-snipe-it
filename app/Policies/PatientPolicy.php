<?php

namespace App\Policies;

class PatientPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'patients';
    }
}
