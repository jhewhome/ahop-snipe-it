<?php

namespace App\Policies;

class AppointmentPolicy extends SnipePermissionsPolicy
{
    protected function columnName()
    {
        return 'appointments';
    }
}
