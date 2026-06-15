<?php

namespace App\Policies;

class OpdVisitPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'opd_visits';
    }
}
